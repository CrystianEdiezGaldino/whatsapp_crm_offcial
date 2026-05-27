<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SSEController;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

// SSE Endpoints (require authentication via session)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/sse/conversation/{conversationId}', [SSEController::class, 'subscribeToConversation']);
    Route::get('/sse/messages', [SSEController::class, 'subscribeToMessages']);
    Route::get('/sse/conversations', [SSEController::class, 'subscribeToConversations']);
});

// Polling endpoints (fallback for SSE)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/conversations/status', [ConversationController::class, 'pollAllStatus']);
    Route::get('/messages/{message}/status', [ConversationController::class, 'pollMessageStatus']);
});

// WhatsApp Webhook
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);
