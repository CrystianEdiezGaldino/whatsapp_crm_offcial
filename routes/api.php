<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SSEController;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

// SSE Endpoints (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sse/conversation/{conversationId}', [SSEController::class, 'subscribeToConversation']);
    Route::get('/sse/messages', [SSEController::class, 'subscribeToMessages']);
    Route::get('/sse/conversations', [SSEController::class, 'subscribeToConversations']);
});

// WhatsApp Webhook
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);
