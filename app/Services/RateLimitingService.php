<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RateLimitingService
{
    /**
     * Limites para sistema de uso único (você + empresa)
     * Bem menos restrictivo que multi-tenant
     */
    private const LIMITS = [
        'whatsapp_send' => ['limit' => 1000, 'window' => 60], // 1000 msgs/min
        'webhook_process' => ['limit' => 500, 'window' => 60], // 500 webhooks/min
        'api_request' => ['limit' => 200, 'window' => 60], // 200 requests/min
        'message_retry' => ['limit' => 100, 'window' => 60], // 100 retries/min
    ];

    /**
     * Verifica se ação está dentro do limite
     *
     * Retorna: [allowed: bool, remaining: int, retry_after: int|null]
     */
    public static function checkLimit(string $action, string $identifier = 'default'): array
    {
        if (!isset(self::LIMITS[$action])) {
            Log::warning('[RateLimit] Unknown action', ['action' => $action]);
            return ['allowed' => true, 'remaining' => 0, 'retry_after' => null];
        }

        $config = self::LIMITS[$action];
        $key = "ratelimit:{$action}:{$identifier}";

        $current = Redis::incr($key);

        // Primeira requisição, set a expiração
        if ($current === 1) {
            Redis::expire($key, $config['window']);
        }

        $allowed = $current <= $config['limit'];
        $remaining = max(0, $config['limit'] - $current);
        $retryAfter = !$allowed ? $config['window'] : null;

        if (!$allowed) {
            Log::warning('[RateLimit] Limit exceeded', [
                'action' => $action,
                'identifier' => $identifier,
                'limit' => $config['limit'],
                'current' => $current,
            ]);
        }

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'retry_after' => $retryAfter,
        ];
    }

    /**
     * Registra ação consumida
     */
    public static function recordAction(string $action, string $identifier = 'default'): void
    {
        $result = self::checkLimit($action, $identifier);

        Log::debug('[RateLimit] Action recorded', [
            'action' => $action,
            'allowed' => $result['allowed'],
            'remaining' => $result['remaining'],
        ]);
    }

    /**
     * Reseta limite para uma ação/identificador
     */
    public static function reset(string $action, string $identifier = 'default'): void
    {
        $key = "ratelimit:{$action}:{$identifier}";
        Redis::del($key);

        Log::info('[RateLimit] Limit reset', [
            'action' => $action,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Retorna status atual de todos os limites
     */
    public static function getStatus(): array
    {
        $status = [];

        foreach (self::LIMITS as $action => $config) {
            $key = "ratelimit:{$action}:default";
            $current = (int) Redis::get($key) ?: 0;

            $status[$action] = [
                'limit' => $config['limit'],
                'window' => $config['window'],
                'current' => $current,
                'remaining' => max(0, $config['limit'] - $current),
                'percentage' => round(($current / $config['limit']) * 100, 2),
                'status' => $current >= $config['limit'] ? '🔴 REACHED' : '🟢 OK',
            ];
        }

        return $status;
    }

    /**
     * Limpa todos os limites
     */
    public static function resetAll(): void
    {
        foreach (self::LIMITS as $action => $config) {
            self::reset($action);
        }

        Log::info('[RateLimit] All limits reset');
    }
}
