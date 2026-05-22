<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
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
        $payload = $request->all();

        Log::info('Webhook received', ['payload' => $payload]);

        $object = $payload['object'] ?? null;
        if ($object !== 'whatsapp_business_account') {
            Log::warning('Webhook object not whatsapp_business_account', ['object' => $object]);
            return response('Ignored', 200);
        }

        WhatsAppService::processWebhook($payload);

        return response('OK', 200);
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
}
