<?php

namespace App\Observers;

use App\Models\Message;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class MessageObserver
{
    public function created(Message $message): void
    {
        AuditLog::create([
            'auditable_type' => 'Message',
            'auditable_id' => $message->id,
            'action' => 'created',
            'description' => $message->direction === 'outbound' ? 'Mensagem enviada' : 'Mensagem recebida',
            'user_id' => $message->direction === 'outbound' ? Auth::id() : null,
            'new_values' => [
                'conversation_id' => $message->conversation_id,
                'type' => $message->type,
                'direction' => $message->direction,
                'content_length' => strlen($message->content ?? ''),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Message $message): void
    {
        AuditLog::create([
            'auditable_type' => 'Message',
            'auditable_id' => $message->id,
            'action' => 'deleted',
            'description' => 'Mensagem deletada',
            'user_id' => Auth::id(),
            'old_values' => [
                'type' => $message->type,
                'direction' => $message->direction,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
