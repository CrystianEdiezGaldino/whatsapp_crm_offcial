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
        'claimed_at' => 'datetime',
        'claimed_by' => 'integer',
        'assigned_to' => 'integer',
        'contact_id' => 'integer',
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

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'conversation_tags');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function transfers()
    {
        return $this->hasMany(ConversationTransfer::class);
    }

    public function resolution()
    {
        return $this->hasOne(ConversationResolution::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getActiveClaim(): ?ConversationClaim
    {
        // If activeClaim was eager loaded, use it directly to avoid N+1 queries
        if ($this->relationLoaded('activeClaim')) {
            return $this->activeClaim;
        }
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
        return (int) $claim->user_id === (int) $userId;
    }

    public function isPendingInQueue(): bool
    {
        if ($this->status !== 'new' || $this->claimed_by) {
            return false;
        }

        return !$this->getActiveClaim();
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
