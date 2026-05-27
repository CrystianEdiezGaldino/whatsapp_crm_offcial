<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WhatsAppToken extends Model
{
    protected $table = 'whatsapp_tokens';

    protected $fillable = ['token_type', 'token_value', 'expires_in', 'expires_at', 'scope', 'notes', 'last_refreshed_at', 'refresh_attempts'];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInHours(now()) <= 24;
    }

    public function getTimeUntilExpiration(): ?string
    {
        if (!$this->expires_at) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expirado há ' . $this->expires_at->diffForHumans();
        }

        return 'Expira em ' . $this->expires_at->diffForHumans();
    }

    public static function getAccessToken(): ?string
    {
        $token = self::where('token_type', 'access')->first();

        if ($token && $token->isExpired()) {
            return null;
        }

        return $token?->token_value;
    }

    public static function storeToken(string $tokenValue, ?int $expiresIn = null, string $scope = ''): self
    {
        $token = self::where('token_type', 'access')->first();

        $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;

        if ($token) {
            $token->update([
                'token_value' => $tokenValue,
                'expires_in' => $expiresIn,
                'expires_at' => $expiresAt,
                'scope' => $scope,
                'last_refreshed_at' => now(),
                'refresh_attempts' => 0,
            ]);
            return $token;
        }

        return self::create([
            'token_type' => 'access',
            'token_value' => $tokenValue,
            'expires_in' => $expiresIn,
            'expires_at' => $expiresAt,
            'scope' => $scope,
            'last_refreshed_at' => now(),
        ]);
    }
}
