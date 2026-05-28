<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\WebhookMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        $verifyToken = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        $startTime = microtime(true);
        $ipAddress = $request->ip();

        // Validar assinatura HMAC para segurança em produção
        if (!$this->validateWebhookSignature($request)) {
            Log::warning('Invalid webhook signature', ['ip' => $ipAddress]);
            return response('Unauthorized', 401);
        }

        $payload = $request->all();
        Log::info('Webhook received', ['payload' => $payload]);

        $object = $payload['object'] ?? null;
        if ($object !== 'whatsapp_business_account') {
            Log::warning('Webhook object not whatsapp_business_account', ['object' => $object]);
            return response('Ignored', 200);
        }

        // Validar que a mensagem é do número ativo
        $activeNumber = \App\Models\WhatsAppNumber::active();
        if ($activeNumber) {
            // Validar phone_number_id se disponível
            $changes = $payload['entry'][0]['changes'][0] ?? [];
            $value = $changes['value'] ?? [];
            $incomingPhoneNumberId = $value['metadata']['phone_number_id'] ?? null;

            if ($incomingPhoneNumberId && $activeNumber->phone_number_id &&
                $incomingPhoneNumberId !== $activeNumber->phone_number_id) {
                Log::warning('Webhook from different WhatsApp number', [
                    'expected' => $activeNumber->phone_number_id,
                    'received' => $incomingPhoneNumberId,
                ]);
                return response('Ignored', 200);
            }
        }

        // Registrar webhook
        $webhookLog = WebhookMonitoringService::logWebhook(
            type: 'message',
            payload: $payload,
            phoneNumber: null,
            ipAddress: $ipAddress
        );

        try {
            WhatsAppService::processWebhook($payload);

            $processingTime = (int) round((microtime(true) - $startTime) * 1000);
            WebhookMonitoringService::markSuccess($webhookLog, $processingTime);

            return response('OK', 200);
        } catch (\Exception $e) {
            $processingTime = (int) round((microtime(true) - $startTime) * 1000);
            WebhookMonitoringService::markFailed($webhookLog, $e->getMessage(), $processingTime);

            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('OK', 200);
        }
    }

    public function debug(Request $request)
    {
        // Apenas para teste/debug
        if (!in_array(config('app.env'), ['local', 'development', 'testing'])) {
            return response('Not available in production', 403);
        }

        if ($request->isMethod('get')) {
            return view('webhook-debug');
        }

        $payload = $request->all();

        Log::info('DEBUG Webhook test', ['payload' => $payload]);

        try {
            \App\Services\WhatsAppService::processWebhook($payload);
            return response()->json(['success' => true, 'message' => 'Webhook processado com sucesso']);
        } catch (\Exception $e) {
            Log::error('DEBUG Webhook error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validar assinatura HMAC do webhook da Meta
     * Garante que o webhook vem realmente da Meta usando a chave secreta
     */
    private function validateWebhookSignature(Request $request): bool
    {
        // Em desenvolvimento, permitir sem validação (para facilitar testes)
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            Log::warning('Missing X-Hub-Signature-256 header');
            return false;
        }

        $appSecret = config('services.whatsapp.app_secret');
        if (!$appSecret) {
            Log::error('META_APP_SECRET not configured');
            return false;
        }

        // Obter o payload raw do request
        $payload = $request->getContent();

        // Calcular a assinatura esperada
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        // Comparação segura (timing-safe)
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid webhook signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            return false;
        }

        return true;
    }
}
