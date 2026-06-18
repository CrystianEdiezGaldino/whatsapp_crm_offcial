<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationClaim;
use App\Models\AuditLog;
use App\Events\ConversationClaimed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationClaimController extends Controller
{
    public function claim(Conversation $conversation)
    {
        $activeClaim = $conversation->getActiveClaim();

        // Se há um claim ativo e não é do usuário atual e ele não é admin, rejeita
        if ($activeClaim && (int) $activeClaim->user_id !== (int) Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Este atendimento já foi clamado por ' . $activeClaim->user->name,
            ], 422);
        }

        $oldStatus = $conversation->status;
        $claim = $conversation->claim(Auth::id(), 'Agente clamou o atendimento');

        // Update conversation status to in_attendance
        $conversation->update([
            'status' => 'in_attendance',
            'claimed_by' => Auth::id(),
            'claimed_at' => now(),
        ]);

        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'claimed',
            'description' => Auth::user()->name . ' clamou o atendimento',
            'user_id' => Auth::id(),
            'new_values' => [
                'status' => 'in_attendance',
                'claimed_by' => Auth::id(),
                'claimed_at' => $claim->claimed_at,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Broadcast status change
        event(new ConversationClaimed($conversation, Auth::user()));
        event(new \App\Events\ConversationStatusChanged($conversation, $oldStatus));

        return response()->json([
            'success' => true,
            'message' => 'Atendimento clamado com sucesso',
            'claim' => [
                'id' => $claim->id,
                'user_id' => $claim->user_id,
                'user_name' => $claim->user->name,
                'claimed_at' => $claim->claimed_at,
            ],
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'claimed_by' => $conversation->claimed_by,
            ],
        ]);
    }

    public function release(Conversation $conversation)
    {
        $activeClaim = $conversation->getActiveClaim();

        if (!$activeClaim) {
            return response()->json([
                'success' => false,
                'message' => 'Este atendimento não possui claim ativo',
            ], 422);
        }

        if (!Auth::user()->isAdmin() && (int) $activeClaim->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode liberar o claim de outro agente',
            ], 403);
        }

        $oldStatus = $conversation->status;
        $releasedUser = $activeClaim->user;
        $conversation->releaseClaim('Agente liberou o atendimento');

        // Update conversation status back to new
        $conversation->update([
            'status' => 'new',
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'released',
            'description' => Auth::user()->name . ' liberou o atendimento de ' . $releasedUser->name,
            'user_id' => Auth::id(),
            'old_values' => [
                'status' => $oldStatus,
                'claimed_by' => $releasedUser->id,
            ],
            'new_values' => [
                'status' => 'new',
                'claimed_by' => null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Broadcast status change
        event(new \App\Events\ConversationStatusChanged($conversation, $oldStatus));

        return response()->json([
            'success' => true,
            'message' => 'Atendimento liberado com sucesso',
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
            ],
        ]);
    }

    public function reassign(Request $request, Conversation $conversation)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem reatribuir',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        $oldClaim = $conversation->getActiveClaim();
        $oldUserId = $oldClaim?->user_id;
        $oldUserName = $oldClaim?->user->name ?? 'Desatribuído';

        if ($oldClaim) {
            $conversation->releaseClaim('Admin reatribuiu o atendimento');
        }

        $newClaim = $conversation->claim($validated['user_id'], $validated['reason'] ?? 'Admin reatribuiu');
        $newUser = $newClaim->user;

        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'assigned',
            'description' => Auth::user()->name . ' reatribuiu de ' . $oldUserName . ' para ' . $newUser->name,
            'user_id' => Auth::id(),
            'old_values' => [
                'claimed_by' => $oldUserId,
            ],
            'new_values' => [
                'claimed_by' => $newUser->id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        event(new ConversationClaimed($conversation, $newUser));

        return response()->json([
            'success' => true,
            'message' => 'Atendimento reatribuído com sucesso',
            'claim' => [
                'id' => $newClaim->id,
                'user_id' => $newClaim->user_id,
                'user_name' => $newUser->name,
                'claimed_at' => $newClaim->claimed_at,
            ],
        ]);
    }

    public function history(Conversation $conversation)
    {
        $claims = ConversationClaim::where('conversation_id', $conversation->id)
            ->with('user')
            ->orderBy('claimed_at', 'desc')
            ->get()
            ->map(fn($claim) => [
                'id' => $claim->id,
                'user_id' => $claim->user_id,
                'user_name' => $claim->user->name,
                'claimed_at' => $claim->claimed_at,
                'released_at' => $claim->released_at,
                'duration_minutes' => $claim->getDurationInMinutes(),
                'is_active' => $claim->isActive(),
            ]);

        return response()->json([
            'success' => true,
            'claims' => $claims,
        ]);
    }
}
