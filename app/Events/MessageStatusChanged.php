<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageStatusChanged implements ShouldBroadcast
{
    use SerializesModels;

    public Message $message;
    public $conversationId;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->conversationId = $message->conversation_id;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('conversation.' . $this->conversationId),
            new Channel('messages.status'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'wa_message_id' => $this->message->wa_message_id,
            'conversation_id' => $this->conversationId,
            'status' => $this->message->status,
            'updated_at' => $this->message->updated_at->toIso8601String(),
        ];
    }
}
