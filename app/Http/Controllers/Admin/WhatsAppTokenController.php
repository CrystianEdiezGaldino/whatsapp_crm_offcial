<?php

namespace App\Http\Controllers\Admin;

use App\Models\WhatsAppToken;
use App\Services\WhatsAppTokenManager;
use Illuminate\Http\Request;

class WhatsAppTokenController
{
    public function index()
    {
        $status = WhatsAppTokenManager::getTokenStatus();
        $tokens = WhatsAppToken::orderByDesc('created_at')->get();

        return view('admin.whatsapp.tokens', compact('status', 'tokens'));
    }

    public function refresh()
    {
        $success = WhatsAppTokenManager::attemptRefresh();

        if ($success) {
            return redirect()->back()->with('success', 'Token renovado com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao renovar token. Verifique os logs.');
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:50',
            'expires_in' => 'nullable|integer|min:0',
        ]);

        WhatsAppToken::storeToken(
            $validated['token'],
            $validated['expires_in'] ?? null
        );

        return redirect()->back()->with('success', 'Token armazenado com sucesso!');
    }

    public function syncFromEnv()
    {
        WhatsAppTokenManager::storeTokenFromEnv();

        return redirect()->back()->with('success', 'Token sincronizado do arquivo .env!');
    }
}
