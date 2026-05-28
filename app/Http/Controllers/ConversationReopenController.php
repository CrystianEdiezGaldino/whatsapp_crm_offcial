<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationReopenRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationReopenController extends Controller
{
    public function request(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'reason' => 'required|string|min:10',
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);

        if ($conversation->status !== 'resolved' && $conversation->status !== 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas conversas encerradas podem ser reabertas.',
            ], 422);
        }

        // Check if there's already a pending request
        $existing = ConversationReopenRequest::where('conversation_id', $conversation->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um pedido de reabertura pendente para esta conversa.',
            ], 422);
        }

        ConversationReopenRequest::create([
            'conversation_id' => $conversation->id,
            'requested_by' => Auth::id(),
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de reabertura enviado para aprovação do administrador.',
        ]);
    }

    public function approve(Request $request, ConversationReopenRequest $reopenRequest)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem aprovar pedidos de reabertura.',
            ], 403);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $reopenRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        // Reopen conversation
        $reopenRequest->conversation->update(['status' => 'in_attendance']);

        return response()->json([
            'success' => true,
            'message' => 'Conversa reaberта com sucesso!',
        ]);
    }

    public function reject(Request $request, ConversationReopenRequest $reopenRequest)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem rejeitar pedidos de reabertura.',
            ], 403);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $reopenRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de reabertura rejeitado.',
        ]);
    }

    public function pending()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.',
            ], 403);
        }

        $requests = ConversationReopenRequest::with(['conversation.contact', 'requestedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests,
        ]);
    }
}
