<?php

namespace App\Observers;

use App\Models\Conversation;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ConversationObserver
{
    public function created(Conversation $conversation): void
    {
        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'created',
            'description' => 'Novo atendimento criado',
            'user_id' => Auth::id(),
            'new_values' => [
                'id' => $conversation->id,
                'contact_id' => $conversation->contact_id,
                'status' => $conversation->status,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Conversation $conversation): void
    {
        $changes = $conversation->getChanges();
        if (empty($changes) || count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        $original = $conversation->getOriginal();
        $old = [];
        $new = [];

        foreach ($changes as $key => $value) {
            if ($key !== 'updated_at' && isset($original[$key])) {
                $old[$key] = $original[$key];
                $new[$key] = $value;
            }
        }

        if (empty($old)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'updated',
            'description' => 'Atendimento atualizado: ' . implode(', ', array_keys($old)),
            'user_id' => Auth::id(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
