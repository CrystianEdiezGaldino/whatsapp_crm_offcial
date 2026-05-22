<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

// Lista de agentes para transferência
Route::middleware('auth')->get('/agents', function () {
    $agents = User::select('id', 'name', 'email', 'role')
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'agents' => $agents,
    ]);
});

// WhatsApp Webhook
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);
