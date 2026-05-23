<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionSetting extends Model
{
    protected $fillable = ['mode', 'overflow_action'];

    protected $casts = [
        'mode' => 'string',
        'overflow_action' => 'string',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['mode' => 'manual', 'overflow_action' => 'next_agent']
        );
    }

    public function isAutomatic(): bool
    {
        return $this->mode === 'automatic';
    }

    public function isManual(): bool
    {
        return $this->mode === 'manual';
    }

    public function isQueueOverflow(): bool
    {
        return $this->overflow_action === 'queue';
    }

    public function isNextAgentOverflow(): bool
    {
        return $this->overflow_action === 'next_agent';
    }
}
