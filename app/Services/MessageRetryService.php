<?php

namespace App\Services;

use App\Models\Message;
use App\Models\FailedMessage;
use Illuminate\Support\Facades\Log;

class MessageRetryService
{
    /**
     * Marca mensagem como falhada e agendada para retry
     */
    public static function markForRetry(Message $message, string $errorMessage): void
    {
        $failedMessage = FailedMessage::firstOrCreate(
            ['message_id' => $message->id],
            [
                'attempt_count' => 1,
                'max_attempts' => 3,
                'last_error' => $errorMessage,
                'status' => 'pending',
                'next_retry_seconds' => 5,
            ]
        );

        // Atualizar se já existe
        if ($failedMessage->exists && $failedMessage->status === 'pending') {
            $failedMessage->increment('attempt_count');
            $failedMessage->update([
                'last_error' => $errorMessage,
                'last_attempt_at' => now(),
                'next_retry_seconds' => self::getBackoffSeconds($failedMessage->attempt_count),
                'next_attempt_at' => now()->addSeconds(
                    self::getBackoffSeconds($failedMessage->attempt_count)
                ),
            ]);
        }

        Log::warning('[MessageRetry] Message marked for retry', [
            'message_id' => $message->id,
            'attempt' => $failedMessage->attempt_count . '/' . $failedMessage->max_attempts,
            'next_retry_in_seconds' => $failedMessage->next_retry_seconds,
            'error' => $errorMessage,
        ]);
    }

    /**
     * Retorna segundos de espera com exponential backoff
     * 1ª tentativa: 5s
     * 2ª tentativa: 30s
     * 3ª tentativa: 300s (5 min)
     */
    public static function getBackoffSeconds(int $attemptNumber): int
    {
        return match ($attemptNumber) {
            1 => 5,
            2 => 30,
            3 => 300,
            default => 600, // 10 min para tentativas além disso
        };
    }

    /**
     * Processa mensagens que precisam de retry
     * Deve ser chamado por um comando agendado ou job
     */
    public static function processRetries(): int
    {
        $failedMessages = FailedMessage::where('status', 'pending')
            ->where('next_attempt_at', '<=', now())
            ->where('attempt_count', '<', 3)
            ->with('message')
            ->limit(10)
            ->get();

        $processed = 0;

        foreach ($failedMessages as $failedMessage) {
            $message = $failedMessage->message;
            if (!$message) {
                $failedMessage->update(['status' => 'failed']);
                continue;
            }

            try {
                // Tenta reenviar mensagem baseado no tipo
                $success = match ($message->type) {
                    'text' => WhatsAppService::sendText($message->conversation->contact->phone, $message->content),
                    'image' => WhatsAppService::sendImage(
                        $message->conversation->contact->phone,
                        $message->media_url
                    ),
                    'audio' => WhatsAppService::sendAudio(
                        $message->conversation->contact->phone,
                        $message->media_url
                    ),
                    'document' => WhatsAppService::sendDocument(
                        $message->conversation->contact->phone,
                        $message->media_url,
                        $message->content
                    ),
                    default => false,
                };

                if ($success) {
                    $failedMessage->update(['status' => 'success']);
                    $message->update(['status' => 'sent']);
                    Log::info('[MessageRetry] Message retry succeeded', ['message_id' => $message->id]);
                    $processed++;
                } else {
                    // Falhou novamente, marcar para novo retry
                    self::markForRetry($message, 'Retry attempt failed again');
                }
            } catch (\Exception $e) {
                // Erro ao tentar reenviar
                self::markForRetry($message, $e->getMessage());

                // Se atingiu limite de tentativas, marcar como falhada
                if ($failedMessage->attempt_count >= $failedMessage->max_attempts) {
                    $failedMessage->update(['status' => 'failed']);
                    $message->update(['status' => 'failed']);
                    Log::error('[MessageRetry] Max retry attempts exceeded', [
                        'message_id' => $message->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $processed;
    }

    /**
     * Limpa registros antigos de failed messages (mais de 7 dias)
     */
    public static function cleanup(): int
    {
        return FailedMessage::where('status', 'failed')
            ->where('created_at', '<', now()->subDays(7))
            ->delete();
    }
}
