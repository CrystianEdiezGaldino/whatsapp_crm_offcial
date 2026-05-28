<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ConversationController;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

// Polling endpoints (primary real-time mechanism)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/conversations/status', [ConversationController::class, 'pollAllStatus']);
    Route::get('/messages/{message}/status', [ConversationController::class, 'pollMessageStatus']);
});

// WhatsApp Webhook
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);
