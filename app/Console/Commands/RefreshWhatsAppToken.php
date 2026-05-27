<?php

namespace App\Console\Commands;

use App\Services\WhatsAppTokenManager;
use Illuminate\Console\Command;

class RefreshWhatsAppToken extends Command
{
    protected $signature = 'whatsapp:refresh-token {--force : Força renovação mesmo que não esteja expirando}';
    protected $description = 'Verifica e renova o token de acesso WhatsApp se necessário';

    public function handle(): int
    {
        $this->info('Verificando status do token WhatsApp...');

        $status = WhatsAppTokenManager::getTokenStatus();

        $this->line('Status: ' . $status['status']);
        if ($status['time_until_expiration']) {
            $this->line('Expiração: ' . $status['time_until_expiration']);
        }
        if ($status['last_refreshed_at']) {
            $this->line('Última renovação: ' . $status['last_refreshed_at']);
        }

        if ($this->option('force') || $status['status'] === 'expiring_soon' || $status['status'] === 'expired') {
            $this->info('Tentando renovar token...');

            if (WhatsAppTokenManager::attemptRefresh()) {
                $this->info('✅ Token renovado com sucesso!');
                return 0;
            } else {
                $this->error('❌ Falha ao renovar token. Verifique os logs.');
                return 1;
            }
        }

        $this->info('✅ Token válido e não precisa de renovação.');
        return 0;
    }
}
