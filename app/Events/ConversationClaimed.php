<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationClaimed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $user,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('conversation.' . $this->conversation->id . '.claim'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.claimed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'claimed_at' => now(),
        ];
    }
}
