<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;

class MessageReceived
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
}
