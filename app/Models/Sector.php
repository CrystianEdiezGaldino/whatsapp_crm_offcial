<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use HasFactory;
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

    public function getDisplayName(): string
    {
        $name = trim((string) $this->name);
        if ($name === '' || preg_match('/^Sector [a-f0-9]{6,}$/i', $name)) {
            return 'Geral';
        }

        return $name;
    }

    public function getDisplayColor(): string
    {
        $colors = ['#4353E8', '#1DA85A', '#D97706', '#D1383E', '#8B5CF6', '#06B6D4', '#EC4899', '#F97316'];

        return $colors[$this->id % count($colors)];
    }

    public function toUiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getDisplayName(),
            'color' => $this->getDisplayColor(),
            'keyboard_option' => $this->keyboard_option,
        ];
    }

    public static function defaultUi(): array
    {
        return [
            'id' => null,
            'name' => 'Geral',
            'color' => '#9CA3AF',
            'keyboard_option' => null,
        ];
    }
}
