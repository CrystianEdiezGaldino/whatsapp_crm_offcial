<?php
namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onConnection('sync');
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        try {
            Log::info('[Webhook Job] Processing payload', [
                'object' => $this->payload['object'] ?? null,
            ]);

            WhatsAppService::processWebhook($this->payload);

            Log::info('[Webhook Job] Completed successfully');
        } catch (\Exception $e) {
            Log::error('[Webhook Job] Failed', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);
            throw $e;
        }
    }

    public function failed(\Exception $exception): void
    {
        Log::critical('[Webhook Job] Failed after retries', [
            'error' => $exception->getMessage(),
        ]);
    }
}
