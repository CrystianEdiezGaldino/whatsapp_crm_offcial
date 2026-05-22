<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use SerializesModels;

    public Message $message;
    public $conversationId;
    public $agentId;

    public function __construct(Message $message, $conversationId, $agentId = null)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;
        $this->agentId = $agentId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'content' => $this->message->content,
            'sender_name' => $this->message->conversation->contact->name,
            'conversation_id' => $this->conversationId,
            'type' => $this->message->type,
            'timestamp' => now()->format('H:i'),
        ];
    }
}
