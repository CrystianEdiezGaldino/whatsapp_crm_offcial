<?php

namespace App\Http\Controllers;

use App\Helpers\ChatInboxHelper;
use App\Models\Conversation;
use App\Models\Contact;
use App\Models\Macro;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Support\AudioMediaPreparer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $list = $this->buildInboxConversationList($request);
        $conversations = $list['conversations'];
        $totalCount = $list['totalCount'];
        $pendingCount = $list['pendingCount'];

        $activeConversation = null;
        $previousConversations = collect();

        $adminIds = User::where('role', 'admin')->pluck('id');

        $macros = Macro::query()
            ->where(function ($q) use ($adminIds) {
                $q->where('user_id', Auth::id());
                if ($adminIds->isNotEmpty()) {
                    $q->orWhereIn('user_id', $adminIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'content', 'shortcut', 'category']);

        if ($request->filled('conversation')) {
            $requested = Conversation::find($request->conversation);

            if ($requested) {
                $resolved = $this->resolveConversationForContact($requested, (int) Auth::id());

                if ((int) $resolved->id !== (int) $requested->id) {
                    $params = $request->query();
                    $params['conversation'] = $resolved->id;

                    return redirect()->route('conversations.index', $params);
                }

                $activeConversation = $this->loadActiveConversationWithRelations($resolved->id);
            }

            if ($activeConversation?->contact) {
                $previousConversations = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'claims.user'])
                    ->where('contact_id', $activeConversation->contact_id)
                    ->where('id', '!=', $activeConversation->id)
                    ->whereIn('status', ['closed', 'resolved'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
        } elseif ($conversations->count() > 0) {
            $activeConversation = $this->loadActiveConversationWithRelations($conversations->first()->id);
        }

        return view('conversations.index', compact('conversations', 'activeConversation', 'previousConversations', 'macros', 'totalCount', 'pendingCount'));
    }

    public function pollList(Request $request)
    {
        $list = $this->buildInboxConversationList($request);
        $activeId = $request->filled('conversation') ? (int) $request->conversation : null;

        if ($activeId) {
            $requested = Conversation::find($activeId);
            if ($requested) {
                $activeId = (int) $this->resolveConversationForContact($requested, (int) Auth::id())->id;
            }
        }

        $items = $list['conversations']
            ->filter(fn($c) => $c->contact)
            ->map(fn($c) => $this->conversationToListItem($c, $request, $activeId))
            ->values();

        return response()->json([
            'conversations' => $items,
            'total_count' => $list['totalCount'],
            'pending_count' => $list['pendingCount'],
        ]);
    }

    public function macrosJson()
    {
        $adminIds = User::where('role', 'admin')->pluck('id');

        $macros = Macro::query()
            ->where(function ($q) use ($adminIds) {
                $q->where('user_id', Auth::id());
                if ($adminIds->isNotEmpty()) {
                    $q->orWhereIn('user_id', $adminIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'content', 'shortcut', 'category']);

        return response()->json($macros);
    }

    /** @return array{conversations: \Illuminate\Support\Collection, totalCount: int, pendingCount: int} */
    private function buildInboxConversationList(Request $request): array
    {
        $isAdmin = Auth::user()->isAdmin();
        $showAllConversations = $isAdmin && !$request->filled('assigned') && !$request->filled('status');
        $isMineTab = $request->filled('assigned') && $request->assigned === 'mine';

        $query = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'activeClaim.user', 'tags', 'sector']);

        if (!$showAllConversations && !$isMineTab) {
            $query->whereIn('status', ['new', 'in_attendance']);
        }

        if ($isMineTab) {
            $query->whereHas('activeClaim', fn($q) => $q->where('user_id', Auth::id()));
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->get();
        $conversations = $this->dedupeConversationsByContact($conversations, (int) Auth::id());

        $pendingCount = $this->countPendingInQueue();

        if ($request->filled('status') && $request->status === 'pending') {
            $conversations = $conversations->filter(fn($c) => $c->isPendingInQueue());
        }

        return [
            'conversations' => $conversations,
            'totalCount' => $conversations->count(),
            'pendingCount' => $pendingCount,
        ];
    }

    private function countPendingInQueue(): int
    {
        $conversations = Conversation::with(['activeClaim.user'])
            ->whereIn('status', ['new', 'in_attendance'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return $this->dedupeConversationsByContact($conversations, (int) Auth::id())
            ->filter(fn($c) => $c->isPendingInQueue())
            ->count();
    }

    private function conversationToListItem(Conversation $conv, Request $request, ?int $activeId = null): array
    {
        $pending = $conv->isPendingInQueue();
        $resolved = in_array($conv->status, ['resolved', 'closed'], true);
        $sectorName = $conv->sector?->name;
        if ($sectorName && preg_match('/^Sector [a-f0-9]{6,}$/i', $sectorName)) {
            $sectorName = 'Geral';
        }
        $sectorName = $sectorName ?: 'Geral';

        $query = $request->query();
        $query['conversation'] = $conv->id;

        return [
            'id' => $conv->id,
            'contact_name' => $conv->contact->name,
            'contact_phone' => $conv->contact->phone,
            'contact_initials' => $conv->contact->initials,
            'last_preview' => \Illuminate\Support\Str::limit($conv->lastMessage?->content ?? 'Sem mensagens', 80),
            'last_time' => $conv->last_message_at?->locale('pt_BR')->diffForHumans(short: true) ?? '???',
            'pending' => $pending,
            'resolved' => $resolved,
            'sector' => $sectorName,
            'active' => $activeId === (int) $conv->id,
            'url' => route('conversations.index', $query),
        ];
    }

    private function loadActiveConversationWithRelations(int $conversationId): ?Conversation
    {
        return Conversation::with([
            'contact',
            'assignedUser',
            'activeClaim.user',
            'tags',
            'messages' => fn ($q) => $q->orderBy('created_at', 'asc')->limit(100),
        ])->find($conversationId);
    }

    private function resolveConversationForContact(Conversation $conversation, ?int $userId = null): Conversation
    {
        $userId = $userId ?? (int) Auth::id();

        if ($conversation->hasActiveClaim($userId)) {
            return $conversation;
        }

        $siblings = Conversation::with(['activeClaim.user'])
            ->where('contact_id', $conversation->contact_id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderByDesc('last_message_at')
            ->get();

        $withMyClaim = $siblings->first(fn ($c) => $c->hasActiveClaim($userId));
        if ($withMyClaim) {
            return $withMyClaim;
        }

        $withAnyClaim = $siblings->first(fn ($c) => $c->getActiveClaim());
        if ($withAnyClaim) {
            return $withAnyClaim;
        }

        return $conversation;
    }

    /** @param \Illuminate\Support\Collection<int, Conversation> $conversations */
    private function dedupeConversationsByContact($conversations, ?int $userId = null): \Illuminate\Support\Collection
    {
        $userId = $userId ?? (int) Auth::id();

        return $conversations
            ->groupBy('contact_id')
            ->map(function ($group) use ($userId) {
                $sorted = $group->sortByDesc(fn ($c) => $c->last_message_at ?? $c->created_at);

                $mine = $sorted->first(fn ($c) => $c->hasActiveClaim($userId));
                if ($mine) {
                    return $mine;
                }

                $claimed = $sorted->first(fn ($c) => $c->getActiveClaim());
                if ($claimed) {
                    return $claimed;
                }

                $open = $sorted->first(fn ($c) => ! in_array($c->status, ['resolved', 'closed'], true));
                if ($open) {
                    return $open;
                }

                return $sorted->first();
            })
            ->sortByDesc(fn ($c) => $c->last_message_at ?? $c->created_at)
            ->values();
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:16384',
        ]);

        $conversation = Conversation::with(['contact', 'sector', 'activeClaim'])->findOrFail($validated['conversation_id']);
        $conversation = $this->resolveConversationForContact($conversation, (int) Auth::id());

        if ($conversation->status === 'resolved' || $conversation->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Esta conversa foi encerrada e n??o pode mais receber mensagens.',
            ], 422);
        }

        if (!Auth::user()->isAdmin() && !$conversation->hasActiveClaim(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Voc?? n??o tem este atendimento. Clame-o primeiro!',
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
        if ($content) {
            $content = app(\App\Services\VariableResolver::class)->replaceInText($content, $conversation);
        }

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
        $lastId = (int) $request->input('last_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->select(['id', 'conversation_id', 'direction', 'content', 'status', 'type', 'wa_message_id', 'media_url', 'created_at'])
            ->limit(50)
            ->get();

        return response()->json([
            'messages' => ChatInboxHelper::mapMessagesForClient($messages),
            'conversation_status' => $conversation->status,
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'claimed_by_name' => $conversation->getActiveClaim()?->user->name,
            ],
        ]);
    }

    public function startConversation(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);

        $conversation = Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'status' => 'new'],
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
        $oldStatus = $conversation->status;
        $conversation->update(['status' => 'resolved']);

        // Dispatch event for real-time update
        event(new \App\Events\ConversationStatusChanged($conversation, $oldStatus));

        return redirect()->route('conversations.index');
    }

    public function resolveWithReason(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'resolution_reason' => 'required|in:problem_solved,customer_satisfied,follow_up_needed,transferred,duplicate,spam,no_response,other',
            'resolution_notes' => 'required|string|min:5',
            'internal_comments' => 'nullable|string',
        ]);

        try {
            $conversation = Conversation::findOrFail($validated['conversation_id']);
            $oldStatus = $conversation->status;

            // Create resolution record
            \App\Models\ConversationResolution::create([
                'conversation_id' => $validated['conversation_id'],
                'resolved_by' => Auth::id(),
                'resolution_reason' => $validated['resolution_reason'],
                'resolution_notes' => $validated['resolution_notes'],
                'internal_comments' => $validated['internal_comments'],
            ]);

            // Update conversation status
            $conversation->update(['status' => 'resolved']);

            // Dispatch event for real-time update
            event(new \App\Events\ConversationStatusChanged($conversation, $oldStatus));

            // Log action
            \Illuminate\Support\Facades\Log::info('[Conversation] Resolved with reason', [
                'conversation_id' => $conversation->id,
                'resolved_by' => Auth::id(),
                'reason' => $validated['resolution_reason'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversa encerrada com sucesso!',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Conversation] Resolution failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar conversa: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history(Conversation $conversation)
    {
        $previousConversations = Conversation::with(['contact', 'assignedUser', 'lastMessage', 'claims.user'])
            ->where('contact_id', $conversation->contact_id)
            ->where('id', '!=', $conversation->id)
            ->whereIn('status', ['closed', 'resolved'])
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
        $messages = $conversation->messages()
            ->with(['conversation.contact'])
            ->orderBy('created_at', 'asc')
            ->get();

        $claims = $conversation->claims()->orderBy('claimed_at', 'asc')->get();
        $firstClaim = $claims->first();
        $lastClaim = $claims->last();

        // Construir timeline de eventos
        $events = collect();

        // Evento: Conversa criada
        $events->push([
            'type' => 'created',
            'title' => 'Conversa iniciada',
            'description' => 'Contato enviou a primeira mensagem',
            'timestamp' => $conversation->created_at,
            'icon' => 'chat',
            'color' => 'primary',
        ]);

        // Eventos: Clamadas
        foreach ($claims as $claim) {
            $events->push([
                'type' => 'claimed',
                'title' => 'Atendimento clamado',
                'description' => 'Clamado por ' . ($claim->user->name ?? 'Agente desconhecido'),
                'timestamp' => $claim->claimed_at,
                'icon' => 'assignment',
                'color' => 'secondary',
            ]);

            if ($claim->released_at) {
                $events->push([
                    'type' => 'released',
                    'title' => 'Atendimento liberado',
                    'description' => 'Liberado por ' . ($claim->user->name ?? 'Agente'),
                    'timestamp' => $claim->released_at,
                    'icon' => 'lock_open',
                    'color' => 'warning',
                ]);
            }
        }

        // Evento: Conversa encerrada
        if ($conversation->status === 'resolved' || $conversation->status === 'closed') {
            $events->push([
                'type' => 'resolved',
                'title' => 'Atendimento encerrado',
                'description' => 'Motivo: ' . ($conversation->resolution_category ?? 'N??o especificado'),
                'timestamp' => $conversation->updated_at,
                'icon' => 'done_all',
                'color' => 'tertiary',
            ]);
        }

        // Ordenar eventos por timestamp
        $events = $events->sortBy('timestamp')->values();

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'contact_name' => $conversation->contact->name,
                'contact_initials' => $conversation->contact->initials,
                'contact_phone' => $conversation->contact->phone,
                'created_at' => $conversation->created_at->format('d/m/Y H:i'),
                'closed_at' => $conversation->updated_at->format('d/m/Y H:i'),
                'claimed_by' => $firstClaim?->user->name ?? 'Desconhecido',
                'message_count' => $messages->count(),
                'status' => $conversation->status,
                'duration' => $this->formatDuration($conversation->created_at, $conversation->updated_at),
            ],
            'events' => $events,
            'messages' => $messages->map(fn($msg) => [
                'direction' => $msg->direction,
                'content' => $msg->content,
                'created_at' => $msg->created_at->format('H:i'),
                'status' => $msg->status,
                'has_media' => !is_null($msg->media_url),
            ]),
        ]);
    }

    private function formatDuration($start, $end)
    {
        $diff = $end->diffInMinutes($start);
        if ($diff < 1) return 'menos de 1 minuto';
        if ($diff < 60) return $diff . ' min';
        $hours = intdiv($diff, 60);
        $mins = $diff % 60;
        return $hours . 'h ' . $mins . 'm';
    }

    public function pollAllStatus(Request $request)
    {
        $conversations = Conversation::with(['contact', 'assignedUser', 'activeClaim.user'])
            ->whereIn('status', ['new', 'in_attendance'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'conversations' => $conversations->map(fn($conv) => [
                'id' => $conv->id,
                'status' => $conv->status,
                'claimed_by_name' => $conv->getActiveClaim()?->user->name,
            ]),
        ]);
    }

    public function pollMessageStatus(Request $request, $messageId)
    {
        $message = \App\Models\Message::find($messageId);

        if (!$message) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $message->id,
            'status' => $message->status,
        ]);
    }

    public function improveText(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:1|max:5000',
            'type' => 'required|in:grammar,professional,both',
        ]);

        try {
            $original = $validated['content'];
            $type = $validated['type'];

            $improved = match ($type) {
                'grammar' => \App\Services\OllamaService::improveGrammar($original),
                'professional' => \App\Services\OllamaService::improveProfessionalTone($original),
                'both' => \App\Services\OllamaService::improveBoth($original),
            };

            \Log::info('[TextImprovement] Text improved', [
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'type' => $type,
            ]);

            return response()->json([
                'success' => true,
                'original' => $original,
                'improved' => $improved,
                'type' => $type,
            ]);

        } catch (\Exception $e) {
            \Log::error('[TextImprovement] Error', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

