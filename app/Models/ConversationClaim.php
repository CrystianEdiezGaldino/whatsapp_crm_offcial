<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationClaim extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'claimed_at', 'released_at', 'reason'];
    protected $casts = ['claimed_at' => 'datetime', 'released_at' => 'datetime'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->released_at === null;
    }

    public function getDurationInMinutes(): int
    {
        $end = $this->released_at ?? now();
        return (int) $this->claimed_at->diffInMinutes($end);
    }
}