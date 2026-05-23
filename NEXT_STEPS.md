# 📋 PRÓXIMOS PASSOS - ROADMAP EXECUTIVO

---

## 🎯 FASE 1: INTEGRAÇÃO BÁSICA (Esta Semana - 3 dias)

### **PASSO 1: Integrar Phone Validation no WhatsAppService** ⏱️ 2h

**Por que:** Evitar enviar mensagens para números inválidos desde o início

**Código:**
```php
// No sendText, sendImage, etc do WhatsAppService
public function sendText(string $to, string $text): ?array
{
    // ADICIONAR ISTO:
    $normalizedPhone = PhoneValidationService::normalize($to);
    if (!$normalizedPhone) {
        Log::error('[WhatsApp] Invalid phone number', ['phone' => $to]);
        return null;
    }
    
    // Usar $normalizedPhone em vez de $to
    return $this->postToRecipients($normalizedPhone, [...]);
}
```

**Arquivo:** `app/Services/WhatsAppService.php`

**Teste:**
```bash
# Testar com número inválido
curl -X POST /conversations/1/send \
  -d '{"message":"Teste","phone":"999999999"}'
# Deve retornar erro de validação
```

---

### **PASSO 2: Integrar Webhook Monitoring no WebhookController** ⏱️ 2h

**Por que:** Registrar TODOS os webhooks para auditoria e debugging

**Código:**
```php
// No WebhookController::handle()
public function handle(Request $request)
{
    $startTime = microtime(true);
    $payload = $request->all();
    $ipAddress = $request->ip();
    
    // REGISTRAR WEBHOOK
    $webhookLog = WebhookMonitoringService::logWebhook(
        type: 'unknown',
        payload: $payload,
        ipAddress: $ipAddress
    );
    
    try {
        // Seu código existente...
        WhatsAppService::processWebhook($payload);
        
        $processingTime = (microtime(true) - $startTime) * 1000;
        WebhookMonitoringService::markSuccess($webhookLog, (int)$processingTime);
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        $processingTime = (microtime(true) - $startTime) * 1000;
        WebhookMonitoringService::markFailed($webhookLog, $e->getMessage(), (int)$processingTime);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

**Arquivo:** `app/Http/Controllers/WebhookController.php`

---

### **PASSO 3: Criar Comando Artisan para Processar Retries** ⏱️ 1h

**Por que:** Executar retries de forma automática e agendada

**Comando:**
```bash
php artisan make:command ProcessMessageRetries
```

**Código:**
```php
// app/Console/Commands/ProcessMessageRetries.php
<?php

namespace App\Console\Commands;

use App\Services\MessageRetryService;
use Illuminate\Console\Command;

class ProcessMessageRetries extends Command
{
    protected $signature = 'messages:retry';
    protected $description = 'Process failed messages that need retry';

    public function handle()
    {
        $this->info('Processing message retries...');
        
        $processed = MessageRetryService::processRetries();
        $this->info("✅ Processed {$processed} messages");
        
        // Limpeza
        $cleaned = MessageRetryService::cleanup();
        $this->info("🗑️ Cleaned up {$cleaned} old records");
    }
}
```

**Agendar (Kernel.php):**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Executar a cada minuto
    $schedule->command('messages:retry')->everyMinute();
}
```

**Teste:**
```bash
php artisan messages:retry
# Deve processar retries e mostrar quantas foram processadas
```

---

## 🎯 FASE 2: TESTES & VALIDAÇÃO (Dias 4-5)

### **PASSO 4: Criar Health Dashboard View** ⏱️ 3h

**Por que:** Ter visualização clara do status do sistema

**Arquivo:** `resources/views/health/dashboard.blade.php`

**Componentes:**
```
┌─────────────────────────────────────────┐
│ 🟢 SYSTEM HEALTH                        │
├─────────────────────────────────────────┤
│                                         │
│ WEBHOOKS (últimas 24h)                  │
│ Status: 🟢 OK / 🟡 WARNING / 🔴 CRITICAL│
│ • Last: 2 min ago                       │
│ • Success rate: 99.5%                   │
│ • Total: 1,234 webhooks                 │
│ • Failed: 6                             │
│                                         │
│ RATE LIMITS (Current Usage)             │
│ • WhatsApp Send: 156/1000 (15%)        │
│ • Webhooks: 45/500 (9%)                │
│ • API: 23/200 (11%)                    │
│                                         │
│ FAILED MESSAGES (Pending Retry)         │
│ • Total: 3 messages                    │
│ • Next retry: in 2 minutes              │
│                                         │
│ RECENT WEBHOOKS (últimos 20)            │
│ [Tabela com logs]                       │
│                                         │
└─────────────────────────────────────────┘
```

---

### **PASSO 5: Testar Retry em Produção** ⏱️ 2h

**Cenário 1: Mensagem falha por API indisponível**
```
1. Parar o servidor WhatsApp (simular erro)
2. Enviar mensagem (vai falhar)
3. Verificar FailedMessage table
   SELECT * FROM failed_messages WHERE status='pending'
4. Iniciar cron job: php artisan messages:retry
5. Verificar se foi retentado
6. Retomar servidor WhatsApp
7. Próxima execução do cron deve ter sucesso
```

**Cenário 2: Número inválido rejeitado**
```
1. Tentar enviar para número: "999999999" (inválido)
2. PhoneValidationService deve rejeitar
3. Log deve mostrar erro de validação
4. Mensagem NÃO deve ir para retry (invalid = skip)
```

**Cenário 3: Rate limit atingido**
```
1. Enviar 1000+ mensagens em 1 minuto
2. Rate limiter deve bloquear após 1000
3. Resposta deve ter retry_after: 60
4. Aguardar 1 minuto e tentar novamente
```

---

## 🎯 FASE 3: MONITORAMENTO CONTÍNUO (Semana 2)

### **PASSO 6: Criar Alertas (Opcional mas Recomendado)** ⏱️ 2h

**Alertas sugeridos:**
```php
// app/Services/AlertService.php
class AlertService
{
    public static function checkHealth()
    {
        $health = WebhookMonitoringService::getHealth();
        
        // Alert 1: Webhooks parados
        if ($health['status'] === 'CRITICAL') {
            self::sendAlert(
                'CRÍTICO: Webhooks parados!',
                'Nenhum webhook há ' . $health['minutes_since_last'] . ' minutos'
            );
        }
        
        // Alert 2: Taxa de erro alta
        if ($health['success_rate'] < 95) {
            self::sendAlert(
                'AVISO: Taxa de erro em ' . $health['success_rate'] . '%',
                'Verificar logs de webhook'
            );
        }
        
        // Alert 3: Muitas mensagens falhadas
        $failed = FailedMessage::where('status', 'failed')->count();
        if ($failed > 10) {
            self::sendAlert(
                'AVISO: ' . $failed . ' mensagens com falha permanente',
                'Revisar no dashboard de health'
            );
        }
    }
    
    private static function sendAlert(string $title, string $message)
    {
        // Opções:
        // 1. Email admin
        // 2. Log com nivel ERROR
        // 3. Slack webhook
        // 4. Telegram bot
        
        Log::critical("[ALERT] $title - $message");
    }
}
```

**Agendar verificação:**
```php
// app/Console/Kernel.php
$schedule->call(fn() => AlertService::checkHealth())
    ->everyFiveMinutes();
```

---

## 📊 ROADMAP COMPLETO

```
ESTA SEMANA (FASE 1):
┌──────────────────────────────────────┐
│ Dia 1-2:                             │
│ ✓ Integrar Phone Validation          │
│ ✓ Integrar Webhook Monitoring        │
│                                      │
│ Dia 3:                               │
│ ✓ Criar Command de Retry             │
│ ✓ Agendar Command em Kernel          │
└──────────────────────────────────────┘
         ↓
PRÓXIMA SEMANA (FASE 2):
┌──────────────────────────────────────┐
│ Dia 4-5:                             │
│ ✓ Criar Health Dashboard             │
│ ✓ Testar Retries em Produção         │
│ ✓ Testar Validação de Telefone       │
│ ✓ Testar Rate Limiting               │
└──────────────────────────────────────┘
         ↓
2 SEMANAS (FASE 3):
┌──────────────────────────────────────┐
│ ✓ Criar Sistema de Alertas           │
│ ✓ Monitorar em tempo real            │
│ ✓ Documentação final                 │
└──────────────────────────────────────┘
```

---

## 🚀 RECOMENDAÇÃO

### **Começar HOJE com PASSO 1 + PASSO 2** (4 horas)
- Integra Phone Validation
- Integra Webhook Monitoring
- Sistema fica muito mais robusto imediatamente

### **Amanhã: PASSO 3** (1 hora)
- Command de retry
- Agendar no Kernel
- Sistema começa a fazer retry automático

### **Semana que vem: PASSO 4 + 5** (5 horas)
- Dashboard bonito
- Testes completos
- Validação em produção

---

## ✅ CHECKLIST FINAL

```
Quando terminar tudo:
□ Phone validation funcionando
□ Webhooks sendo rastreados
□ Retries acontecendo automaticamente
□ Health dashboard mostrando status
□ Alertas funcionando (optional)
□ Testes aprovados em produção
□ Documentação atualizada

Sistema PRONTO PARA PRODUÇÃO ✅
```

---

## 📞 DÚVIDAS COMUNS

**P: Preciso fazer TODOS os passos?**
R: Mínimo: Passos 1-3. Passos 4-5 são validação. Passo 6 é nice-to-have.

**P: Quanto tempo leva?**
R: Passos 1-3: ~5h total. Passos 4-5: ~5h. Total: ~10h de trabalho.

**P: Preciso parar o sistema?**
R: Não. Tudo é non-blocking. Pode integrar enquanto está rodando.

**P: E se der erro durante a integração?**
R: Rollback fácil. Cada passo é isolado. Pior caso: volta para HEAD anterior.

---

