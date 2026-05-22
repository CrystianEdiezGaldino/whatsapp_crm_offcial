<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Console\Command;

class DiagnoseWebhook extends Command
{
    protected $signature = 'webhook:diagnose {phone?}';
    protected $description = 'Diagnose webhook configuration and test message creation';

    public function handle()
    {
        $this->info('=== WhatsApp Webhook Diagnóstico ===\n');

        // 1. Verificar configuração
        $this->checkConfiguration();

        // 2. Verificar logs recentes
        $this->checkRecentLogs();

        // 3. Se fornecido número de teste, procurar conversas
        if ($phone = $this->argument('phone')) {
            $this->checkPhoneData($phone);
        }

        // 4. Instruções para debug
        $this->showDebugInstructions();
    }

    private function checkConfiguration(): void
    {
        $this->line('<fg=cyan>1. Verificando Configuração...</>');

        $config = [
            'WA_PHONE_NUMBER_ID' => config('services.whatsapp.phone_number_id'),
            'WA_WABA_ID' => config('services.whatsapp.waba_id'),
            'WA_ACCESS_TOKEN' => config('services.whatsapp.access_token') ? '✓ Configurado' : '✗ FALTANDO',
            'WA_VERIFY_TOKEN' => config('services.whatsapp.verify_token') ? '✓ Configurado' : '✗ FALTANDO',
        ];

        foreach ($config as $key => $value) {
            $status = is_string($value) && str_starts_with($value, '✓') ? 'fg=green' : 'fg=yellow';
            $this->line("  <{$status}>{$key}</> = {$value}");
        }

        $this->line('');
    }

    private function checkRecentLogs(): void
    {
        $this->line('<fg=cyan>2. Verificando Logs Recentes...</>');

        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            $this->line('  <fg=yellow>Nenhum log encontrado<//>');
            return;
        }

        $lines = array_slice(file($logFile), -50);
        $webhookLines = array_filter($lines, fn($line) => str_contains($line, 'Webhook'));

        if (empty($webhookLines)) {
            $this->line('  <fg=yellow>Nenhuma mensagem webhook encontrada nos últimos logs</>');
        } else {
            $this->line("  <fg=green>Encontrados " . count($webhookLines) . " eventos webhook:</>");
            foreach (array_slice($webhookLines, -5) as $line) {
                $this->line('    ' . trim($line));
            }
        }

        $this->line('');
    }

    private function checkPhoneData(string $phone): void
    {
        $this->line("<fg=cyan>3. Procurando contatos para: {$phone}</>");

        $contacts = Contact::where('phone', 'like', "%{$phone}%")
            ->orWhere('phone', $phone)
            ->get();

        if ($contacts->isEmpty()) {
            $this->line('  <fg=yellow>Nenhum contato encontrado com este número</>');
            return;
        }

        foreach ($contacts as $contact) {
            $this->line("\n  <fg=green>Contato:</> {$contact->name}");
            $this->line("    ID: {$contact->id}");
            $this->line("    Telefone: {$contact->phone}");
            $this->line("    Mensagens: {$contact->messages()->count()}");

            $conversations = $contact->conversations;
            if ($conversations->isEmpty()) {
                $this->line('    <fg=yellow>Nenhuma conversa</>');
            } else {
                foreach ($conversations as $conv) {
                    $this->line("    Conversa ID {$conv->id}: {$conv->messages()->count()} mensagens (status: {$conv->status})");
                }
            }
        }

        $this->line('');
    }

    private function showDebugInstructions(): void
    {
        $this->line('<fg=cyan>4. Instruções para Debug:</>\n');

        $this->line('Para testar o webhook:');
        $this->line('  1. Acesse: /webhook/debug');
        $this->line('  2. Envie um JSON de teste com formato WhatsApp');
        $this->line('  3. Verifique se a mensagem aparece em Conversas\n');

        $this->line('Problemas Comuns:');
        $this->line('  • Webhook URL não está registrada na Meta');
        $this->line('  • Verify Token não coincide com a configuração');
        $this->line('  • Access Token expirado ou sem permissões');
        $this->line('  • Número em formato diferente do esperado\n');

        $this->line('URL do Webhook (registrar na Meta):');
        $this->line("  <fg=green>" . config('app.url') . "/webhook</>\n");
    }
}
