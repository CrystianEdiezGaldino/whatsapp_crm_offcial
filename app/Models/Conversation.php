<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id', 'assigned_to', 'status', 'priority', 'last_message_at', 'claimed_by', 'claimed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function claims()
    {
        return $this->hasMany(ConversationClaim::class);
    }

    public function activeClaim()
    {
        return $this->hasOne(ConversationClaim::class)->where('released_at', null)->latestOfMany('claimed_at');
    }

    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getActiveClaim(): ?ConversationClaim
    {
        return $this->activeClaim()->first();
    }

    public function hasActiveClaim(?int $userId = null): bool
    {
        $claim = $this->getActiveClaim();
        if (!$claim) {
            return false;
        }
        if ($userId === null) {
            return true;
        }
        return $claim->user_id === $userId;
    }

    public function claim(int $userId, ?string $reason = null): ConversationClaim
    {
        // Release previous claim if exists
        $this->getActiveClaim()?->update(['released_at' => now()]);

        return $this->claims()->create([
            'user_id' => $userId,
            'claimed_at' => now(),
            'reason' => $reason,
        ]);
    }

    public function releaseClaim(?string $reason = null): bool
    {
        $claim = $this->getActiveClaim();
        if (!$claim) {
            return false;
        }

        return (bool) $claim->update([
            'released_at' => now(),
            'reason' => $reason,
        ]);
    }
}
