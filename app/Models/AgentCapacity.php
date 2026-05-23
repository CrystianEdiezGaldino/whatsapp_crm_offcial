<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCapacity extends Model
{
    protected $fillable = ['user_id', 'max_conversations', 'is_active', 'round_robin_position'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeConversationsCount(): int
    {
        return Conversation::where('claimed_by', $this->user_id)
            ->where('status', 'in_attendance')
            ->count();
    }

    public function hasCapacity(): bool
    {
        return $this->activeConversationsCount() < $this->max_conversations;
    }

    public function isFull(): bool
    {
        return !$this->hasCapacity();
    }

    public function getAvailableSlots(): int
    {
        return max(0, $this->max_conversations - $this->activeConversationsCount());
    }
}
