<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    protected $fillable = [
        'name',
        'description',
        'keyboard_option',
        'greeting_message',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all agents in this sector
     */
    public function agents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active agents in this sector
     */
    public function activeAgents(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('is_active', true)
            ->whereIn('role', ['agent', 'supervisor']);
    }

    /**
     * Get agent count in this sector
     */
    public function getAgentCountAttribute(): int
    {
        return $this->agents()->count();
    }

    /**
     * Get active agent count
     */
    public function getActiveAgentCountAttribute(): int
    {
        return $this->activeAgents()->count();
    }

    /**
     * Scope: Only active sectors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by keyboard option
     */
    public function scopeByOption($query)
    {
        return $query->orderBy('keyboard_option');
    }

    /**
     * Get greeting message or default
     */
    public function getGreetingOrDefault(): string
    {
        return $this->greeting_message ?? "Você foi redirecionado para {$this->name}. Um atendente irá responder em breve.";
    }

    /**
     * Format as keyboard option
     */
    public function getOptionLabel(): string
    {
        return "{$this->keyboard_option}. {$this->name}";
    }
}
