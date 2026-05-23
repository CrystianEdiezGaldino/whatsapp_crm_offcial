<?php

namespace App\Services;

use App\Models\WebhookLog;
use Illuminate\Support\Facades\Log;

class WebhookMonitoringService
{
    /**
     * Registra um webhook recebido
     */
    public static function logWebhook(
        string $type,
        array $payload,
        ?string $phoneNumber = null,
        string $ipAddress = ''
    ): WebhookLog {
        return WebhookLog::create([
            'type' => $type,
            'status' => 'received',
            'phone_number' => $phoneNumber,
            'payload' => json_encode($payload),
            'ip_address' => $ipAddress,
            'processing_time_ms' => 0,
        ]);
    }

    /**
     * Marca webhook como processado com sucesso
     */
    public static function markSuccess(WebhookLog $log, int $processingTimeMs): void
    {
        $log->update([
            'status' => 'success',
            'processing_time_ms' => $processingTimeMs,
        ]);

        Log::info('[Webhook] Successfully processed', [
            'type' => $log->type,
            'phone' => $log->phone_number,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Marca webhook como falhado
     */
    public static function markFailed(WebhookLog $log, string $error, int $processingTimeMs): void
    {
        $log->update([
            'status' => 'failed',
            'error_message' => $error,
            'processing_time_ms' => $processingTimeMs,
        ]);

        Log::error('[Webhook] Processing failed', [
            'type' => $log->type,
            'phone' => $log->phone_number,
            'error' => $error,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Retorna status de saúde dos webhooks
     */
    public static function getHealth(): array
    {
        // Últimas 24 horas
        $last24h = WebhookLog::where('created_at', '>=', now()->subHours(24))->get();

        $totalReceived = $last24h->count();
        $successful = $last24h->where('status', 'success')->count();
        $failed = $last24h->where('status', 'failed')->count();
        $processing = $last24h->where('status', 'processing')->count();

        $successRate = $totalReceived > 0 ? ($successful / $totalReceived) * 100 : 0;
        $avgProcessingTime = $last24h->avg('processing_time_ms');

        // Última mensagem recebida
        $lastWebhook = WebhookLog::orderBy('created_at', 'desc')->first();
        $lastReceivedAt = $lastWebhook?->created_at;
        $minutesSinceLastWebhook = $lastReceivedAt ? now()->diffInMinutes($lastReceivedAt) : null;

        // Status: OK, WARNING, CRITICAL
        $status = 'OK';
        if ($minutesSinceLastWebhook && $minutesSinceLastWebhook > 5) {
            $status = 'CRITICAL';
        } elseif ($successRate < 95) {
            $status = 'WARNING';
        }

        return [
            'status' => $status,
            'last_webhook_at' => $lastReceivedAt,
            'minutes_since_last' => $minutesSinceLastWebhook,
            'total_received_24h' => $totalReceived,
            'successful_24h' => $successful,
            'failed_24h' => $failed,
            'processing_24h' => $processing,
            'success_rate' => round($successRate, 2),
            'avg_processing_time_ms' => round($avgProcessingTime ?? 0, 2),
            'alert' => self::getAlert($status, $minutesSinceLastWebhook, $successRate),
        ];
    }

    /**
     * Retorna mensagem de alerta se necessário
     */
    private static function getAlert(string $status, ?int $minutesSinceLastWebhook, float $successRate): ?string
    {
        if ($status === 'CRITICAL') {
            return "⚠️ CRÍTICO: Nenhum webhook recebido há {$minutesSinceLastWebhook} minutos!";
        }

        if ($status === 'WARNING') {
            return "⚠️ AVISO: Taxa de sucesso baixa ({$successRate}%)";
        }

        return null;
    }

    /**
     * Retorna logs dos últimos webhooks (com filtro opcional)
     */
    public static function getRecentLogs(
        ?string $type = null,
        ?string $status = null,
        int $limit = 50
    ): array {
        $query = WebhookLog::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'type' => $log->type,
                'status' => $log->status,
                'phone' => $log->phone_number,
                'processing_time_ms' => $log->processing_time_ms,
                'error' => $log->error_message,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $log->created_at->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Limpa logs antigos (mais de 30 dias)
     */
    public static function cleanup(): int
    {
        return WebhookLog::where('created_at', '<', now()->subDays(30))->delete();
    }
}
