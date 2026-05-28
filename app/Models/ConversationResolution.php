<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationResolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'resolved_by',
        'resolution_reason',
        'resolution_notes',
        'internal_comments',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public static function getReasonLabels()
    {
        return [
            'problem_solved' => '✓ Problema Resolvido',
            'customer_satisfied' => '😊 Cliente Satisfeito',
            'follow_up_needed' => '→ Acompanhamento Necessário',
            'transferred' => '↗️ Transferido',
            'duplicate' => '📋 Conversa Duplicada',
            'spam' => '⚠️ Spam/Abuso',
            'no_response' => '⏱️ Sem Resposta do Cliente',
            'other' => '❓ Outro',
        ];
    }
}
