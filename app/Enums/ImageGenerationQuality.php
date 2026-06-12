<?php

namespace App\Enums;

enum ImageGenerationQuality: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function credits(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 5,
            self::High => 15,
        };
    }

    public function approximateCostUsd(): float
    {
        return match ($this) {
            self::Low => 0.01,
            self::Medium => 0.05,
            self::High => 0.13,
        };
    }

    public static function fromCostUsd(float $costUsd): ?self
    {
        return match (true) {
            $costUsd <= 0.02 => self::Low,
            $costUsd <= 0.08 => self::Medium,
            default => self::High,
        };
    }
}
