<?php

namespace App\Services;

use App\Models\WhatsAppToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppTokenManager
{
    private const META_GRAPH_URL = 'https://graph.instagram.com/v25.0';
    private const REFRESH_ENDPOINT = '/refresh_access_token';

    public static function getValidAccessToken(): ?string
    {
        $token = WhatsAppToken::where('token_type', 'access')->first();

        if (!$token) {
            return config('services.whatsapp.access_token');
        }

        if ($token->isExpired()) {
            Log::warning('WhatsApp token expirado', [
                'expired_at' => $token->expires_at,
                'attempts' => $token->refresh_attempts,
            ]);
            return null;
        }

        if ($token->isExpiringSoon()) {
            self::attemptRefresh();
        }

        return $token->token_value;
    }

    public static function attemptRefresh(): bool
    {
        $token = WhatsAppToken::where('token_type', 'access')->first();

        if (!$token) {
            Log::warning('Nenhum token WhatsApp encontrado para renovar');
            return false;
        }

        if ($token->refresh_attempts >= 3) {
            Log::error('Limite de tentativas de renovação excedido para token WhatsApp');
            return false;
        }

        try {
            $response = Http::get(self::META_GRAPH_URL . self::REFRESH_ENDPOINT, [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $token->token_value,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $token->update([
                    'token_value' => $data['access_token'] ?? $token->token_value,
                    'expires_in' => $data['expires_in'] ?? null,
                    'expires_at' => $data['expires_in'] ? now()->addSeconds($data['expires_in']) : null,
                    'last_refreshed_at' => now(),
                    'refresh_attempts' => 0,
                ]);

                Log::info('Token WhatsApp renovado com sucesso', [
                    'expires_in' => $data['expires_in'] ?? null,
                ]);

                return true;
            }

            Log::warning('Falha ao renovar token WhatsApp', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            $token->increment('refresh_attempts');
            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao renovar token WhatsApp', [
                'error' => $e->getMessage(),
            ]);

            $token->increment('refresh_attempts');
            return false;
        }
    }

    public static function storeTokenFromEnv(): void
    {
        $tokenValue = config('services.whatsapp.access_token');

        if ($tokenValue) {
            WhatsAppToken::storeToken($tokenValue, scope: 'whatsapp_business_messaging');
        }
    }

    public static function getTokenStatus(): array
    {
        $token = WhatsAppToken::where('token_type', 'access')->first();

        if (!$token) {
            return [
                'status' => 'not_stored',
                'message' => 'Token não está armazenado no banco de dados',
                'action' => 'execute: WhatsAppTokenManager::storeTokenFromEnv()',
                'expires_at' => null,
                'time_until_expiration' => 'Não disponível',
                'last_refreshed_at' => null,
                'refresh_attempts' => 0,
            ];
        }

        return [
            'status' => $token->isExpired() ? 'expired' : ($token->isExpiringSoon() ? 'expiring_soon' : 'valid'),
            'expires_at' => $token->expires_at,
            'time_until_expiration' => $token->getTimeUntilExpiration(),
            'last_refreshed_at' => $token->last_refreshed_at,
            'refresh_attempts' => $token->refresh_attempts,
        ];
    }
}
