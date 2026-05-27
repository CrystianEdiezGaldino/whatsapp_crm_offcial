<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationClaimed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $user,
    ) {}
}
