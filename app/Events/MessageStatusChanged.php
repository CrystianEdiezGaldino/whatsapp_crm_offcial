<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;

class MessageStatusChanged
{
    use SerializesModels;

    public Message $message;
    public $conversationId;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->conversationId = $message->conversation_id;
    }
}
