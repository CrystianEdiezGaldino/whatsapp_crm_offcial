<?php

namespace App\Support;

class WhatsAppApiError
{
    public static function userMessage(?array $error): string
    {
        if (!$error) {
            return 'Falha ao enviar mensagem pelo WhatsApp.';
        }

        $code = (int) ($error['code'] ?? 0);

        return match ($code) {
            131030 => 'Número não está na lista de teste da Meta. Em developers.facebook.com → seu app → WhatsApp → API Setup, adicione o telefone em "To" (tente 554197796908 e 5541997796908).',
            190 => 'Token inválido ou expirado. Atualize WA_ACCESS_TOKEN no .env.',
            131047 => 'Janela de 24h fechada. Use um template aprovado para iniciar conversa.',
            131053 => 'Falha no upload de mídia. Verifique formato (áudio: MP3, OGG/OPUS, AAC, M4A, AMR — máx. 16 MB).',
            default => (string) ($error['message'] ?? 'Erro ao enviar pelo WhatsApp.'),
        };
    }
}
