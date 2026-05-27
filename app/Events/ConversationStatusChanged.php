<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Queue\SerializesModels;

class ConversationStatusChanged
{
    use SerializesModels;

    public Conversation $conversation;
    public $oldStatus;

    public function __construct(Conversation $conversation, ?string $oldStatus = null)
    {
        $this->conversation = $conversation;
        $this->oldStatus = $oldStatus;
    }
}
