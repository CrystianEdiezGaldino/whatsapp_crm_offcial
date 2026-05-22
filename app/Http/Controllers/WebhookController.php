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
            return response('Ignored', 200);
        }

        WhatsAppService::processWebhook($payload);

        return response('OK', 200);
    }
}
