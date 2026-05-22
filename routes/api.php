<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

// WhatsApp Webhook
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);
