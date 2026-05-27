<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'conversation_id',
        'responsible_user_id',
        'reviewed_by',
        'rating',
        'customer_note',
        'severity',
        'status',
        'review_notes',
        'action_taken',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isHighSeverity(): bool
    {
        return $this->severity === 'high' || $this->rating <= 2;
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'reviewing']);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
}
