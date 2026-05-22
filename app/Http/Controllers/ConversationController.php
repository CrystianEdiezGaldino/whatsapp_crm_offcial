<?php

namespace App\Http\Controllers;

use App\Helpers\ChatInboxHelper;
use App\Models\Conversation;
use App\Models\Contact;
use App\Models\Macro;
use App\Services\WhatsAppService;
use App\Support\AudioMediaPreparer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'activeClaim.user'])
            ->where('status', 'open');

        if ($request->filled('assigned') && $request->assigned === 'mine') {
            $query->where('assigned_to', Auth::id());
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->get();
        $activeConversation = null;
        $previousConversations = [];
        $macros = Macro::where('user_id', Auth::id())->orWhereNull('user_id')->get();

        if ($request->filled('conversation')) {
            $activeConversation = Conversation::with([
                'contact',
                'assignedUser',
                'activeClaim.user',
                'messages' => fn($q) => $q->orderBy('created_at', 'asc'),
            ])->find($request->conversation);

            // Load previous conversations with same contact
            if ($activeConversation) {
                $previousConversations = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'claims.user'])
                    ->where('contact_id', $activeConversation->contact_id)
                    ->where('id', '!=', $activeConversation->id)
                    ->where('status', 'closed')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }
        } elseif ($conversations->count() > 0) {
            $activeConversation = Conversation::with([
                'contact',
                'assignedUser',
                'activeClaim.user',
                'messages' => fn($q) => $q->orderBy('created_at', 'asc'),
            ])->find($conversations->first()->id);
        }

        return view('conversations.index', compact('conversations', 'activeConversation', 'previousConversations', 'macros'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:16384',
        ]);

        $conversation = Conversation::with('contact')->findOrFail($validated['conversation_id']);

        if (!Auth::user()->isAdmin() && !$conversation->hasActiveClaim(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem este atendimento. Clame-o primeiro!',
            ], 403);
        }

        if (!$conversation->contact) {
            return response()->json([
                'success' => false,
                'message' => 'Conversa sem contato vinculado.',
            ], 422);
        }

        if (empty(trim($validated['content'] ?? '')) && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Digite uma mensagem ou anexe um arquivo.',
            ], 422);
        }

        $phone = $conversation->contact->phone;
        $whatsapp = new WhatsAppService();

        $type = 'text';
        $mediaId = null;
        $mediaUrl = null;
        $mediaFilename = null;
        $mimeType = null;
        $content = $validated['content'] ?? null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $mediaFilename = $file->getClientOriginalName();
            $path = $file->store('media', 'public');
            $fullPath = Storage::disk('public')->path($path);
            $mediaUrl = $path;
            $uploadPath = $fullPath;
            $uploadMime = $mimeType;
            $uploadFilename = $mediaFilename;
            $tempCleanup = [];

            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mimeType, 'audio/') || str_ends_with(strtolower($mediaFilename), '.webm')) {
                $type = 'document';
                $isRecorded = str_contains($mimeType, 'webm') || str_ends_with(strtolower($mediaFilename), '.webm');

                try {
                    $prepared = AudioMediaPreparer::prepare(
                        $fullPath,
                        $mimeType,
                        $mediaFilename,
                        $isRecorded,
                        asAttachment: true
                    );
                    $uploadPath = $prepared['path'];
                    $uploadMime = $prepared['mime'];
                    $uploadFilename = $prepared['filename'];
                    $tempCleanup = $prepared['cleanup'];
                    $mimeType = $uploadMime;
                    $mediaFilename = $uploadFilename;
                } catch (\RuntimeException $e) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
            } elseif (str_starts_with($mimeType, 'video/')) {
                $type = 'video';
            } else {
                $type = 'document';
            }

            $uploadedMediaId = $whatsapp->uploadMedia($uploadPath, $uploadMime, $uploadFilename);
            AudioMediaPreparer::deleteCleanup($tempCleanup);

            if (!$uploadedMediaId) {
                return response()->json([
                    'success' => false,
                    'message' => $whatsapp->getUserFacingError(),
                    'error_code' => $whatsapp->getLastError()['code'] ?? null,
                ], 422);
            }

            $mediaId = $uploadedMediaId;

            $documentFilename = $type === 'document' && str_starts_with($mimeType, 'audio/')
                ? $mediaFilename
                : null;

            $result = $whatsapp->sendMedia(
                $phone,
                $type,
                $uploadedMediaId,
                $documentFilename ?? $content
            );

            $content = str_starts_with($mimeType, 'audio/') ? ($content ?: null) : ($content ?: $mediaFilename);
        } else {
            $result = $whatsapp->sendText($phone, $content);
        }

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => $whatsapp->getUserFacingError(),
                'error_code' => $whatsapp->getLastError()['code'] ?? null,
            ], 422);
        }

        $waMessageId = $result['messages'][0]['id'] ?? null;

        $message = $conversation->messages()->create([
            'wa_message_id' => $waMessageId,
            'direction' => 'outbound',
            'type' => $type,
            'content' => $content,
            'media_url' => $mediaUrl,
            'media_id' => $mediaId,
            'media_filename' => $mediaFilename,
            'mime_type' => $mimeType,
            'status' => 'sent',
            'sender_id' => Auth::id(),
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->contact->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => ChatInboxHelper::toClientArray($message->load('sender')),
            'whatsapp_response' => $result,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function poll(Request $request, Conversation $conversation)
    {
        $lastId = $request->input('last_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'messages' => ChatInboxHelper::mapMessagesForClient($messages),
            'conversation_status' => $conversation->status,
        ]);
    }

    public function startConversation(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);

        $conversation = Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'status' => 'open'],
            ['assigned_to' => Auth::id(), 'last_message_at' => now()]
        );

        if (!$conversation->assigned_to) {
            $conversation->update(['assigned_to' => Auth::id()]);
        }

        return redirect()->route('conversations.index', ['conversation' => $conversation->id]);
    }

    public function assign(Request $request, Conversation $conversation)
    {
        $conversation->update(['assigned_to' => Auth::id()]);
        return redirect()->route('conversations.index', ['conversation' => $conversation->id]);
    }

    public function resolve(Conversation $conversation)
    {
        $conversation->update(['status' => 'closed']);
        return redirect()->route('conversations.index');
    }

    public function history(Conversation $conversation)
    {
        $previousConversations = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'claims.user'])
            ->where('contact_id', $conversation->contact_id)
            ->where('id', '!=', $conversation->id)
            ->where('status', 'closed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'history' => $previousConversations->map(fn($conv) => [
                'id' => $conv->id,
                'created_at' => $conv->created_at->format('d/m/Y H:i'),
                'resolved_at' => $conv->updated_at->format('d/m/Y H:i'),
                'claimed_by' => $conv->claims()->latest('claimed_at')->first()?->user->name ?? 'Desconhecido',
                'last_message' => $conv->lastMessage?->content ?? '(Sem mensagens)',
                'message_count' => $conv->messages()->count(),
            ]),
        ]);
    }

    public function showHistoryConversation(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with(['conversation.contact'])
            ->orderBy('created_at', 'asc')
            ->get();

        $claim = $conversation->claims()->latest('claimed_at')->first();

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'contact_name' => $conversation->contact->name,
                'contact_initials' => $conversation->contact->initials,
                'created_at' => $conversation->created_at->format('d/m/Y H:i'),
                'closed_at' => $conversation->updated_at->format('d/m/Y H:i'),
                'claimed_by' => $claim?->user->name ?? 'Desconhecido',
                'message_count' => $messages->count(),
            ],
            'messages' => $messages->map(fn($msg) => [
                'direction' => $msg->direction,
                'content' => $msg->content,
                'created_at' => $msg->created_at->format('H:i'),
                'has_media' => !is_null($msg->media_url),
            ]),
        ]);
    }
}
