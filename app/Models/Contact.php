<?php

namespace App\Models;

use App\Support\PhoneNormalizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'tags', 'notes', 'assigned_to', 'last_message_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr($words[1], 0, 1) : ''));
    }

    public static function findOrCreateByPhone(string $phone, array $defaults = []): self
    {
        $variants = PhoneNormalizer::variants($phone);
        $apiPhone = PhoneNormalizer::forApi($phone);

        $existing = static::whereIn('phone', $variants)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            if ($existing->phone !== $apiPhone) {
                $existing->update(['phone' => $apiPhone]);
            }
            if (!empty($defaults['name']) && ($existing->name === $existing->phone || $existing->name === '')) {
                $existing->update(['name' => $defaults['name']]);
            }
            return $existing->fresh();
        }

        return static::create(array_merge([
            'name' => $defaults['name'] ?? $apiPhone,
            'phone' => $apiPhone,
            'last_message_at' => now(),
        ], $defaults));
    }
}
