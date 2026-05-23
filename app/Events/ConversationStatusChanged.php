<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ConversationStatusChanged implements ShouldBroadcast
{
    use SerializesModels;

    public Conversation $conversation;
    public $oldStatus;

    public function __construct(Conversation $conversation, ?string $oldStatus = null)
    {
        $this->conversation = $conversation;
        $this->oldStatus = $oldStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('conversation.' . $this->conversation->id),
            new Channel('conversations.status'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'status' => $this->conversation->status,
            'old_status' => $this->oldStatus,
            'claimed_by' => $this->conversation->claimed_by,
            'claimed_by_name' => $this->conversation->claimed_by ? $this->conversation->claimer?->name : null,
            'claimed_at' => $this->conversation->claimed_at?->toIso8601String(),
            'updated_at' => $this->conversation->updated_at->toIso8601String(),
        ];
    }
}
