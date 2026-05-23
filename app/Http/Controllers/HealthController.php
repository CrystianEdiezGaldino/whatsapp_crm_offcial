<?php

namespace App\Http\Controllers;

use App\Services\WebhookMonitoringService;
use App\Services\RateLimitingService;
use App\Models\FailedMessage;

class HealthController extends Controller
{
    /**
     * Health check simples (para monitoramento)
     */
    public function status()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Dashboard de saúde completo (protegido por autenticação)
     */
    public function dashboard()
    {
        $this->middleware('auth');

        $webhookHealth = WebhookMonitoringService::getHealth();
        $rateLimitStatus = RateLimitingService::getStatus();
        $failedMessages = FailedMessage::where('status', 'pending')->count();
        $recentWebhooks = WebhookMonitoringService::getRecentLogs(limit: 20);

        return view('health.dashboard', compact(
            'webhookHealth',
            'rateLimitStatus',
            'failedMessages',
            'recentWebhooks'
        ));
    }

    /**
     * API JSON de saúde (para monitoramento externo)
     */
    public function api()
    {
        $webhookHealth = WebhookMonitoringService::getHealth();
        $rateLimitStatus = RateLimitingService::getStatus();
        $failedMessages = FailedMessage::where('status', 'pending')->count();

        $overallStatus = match ($webhookHealth['status']) {
            'CRITICAL' => 'unhealthy',
            'WARNING' => 'degraded',
            default => 'healthy',
        };

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'webhook' => [
                'status' => $webhookHealth['status'],
                'last_received_at' => $webhookHealth['last_webhook_at'],
                'minutes_since_last' => $webhookHealth['minutes_since_last'],
                'total_24h' => $webhookHealth['total_received_24h'],
                'success_rate' => $webhookHealth['success_rate'],
                'alert' => $webhookHealth['alert'],
            ],
            'rate_limits' => $rateLimitStatus,
            'failed_messages' => [
                'pending' => $failedMessages,
            ],
        ]);
    }

    /**
     * Detalhes de webhooks (protegido)
     */
    public function webhookLogs(?string $type = null, ?string $status = null)
    {
        $this->middleware('auth');

        $logs = WebhookMonitoringService::getRecentLogs($type, $status, 100);

        return response()->json($logs);
    }
}
