# Resolução de Problemas - Webhook WhatsApp

## Problema
Mensagens recebidas via WhatsApp não aparecem em `/conversations`. O webhook não está escutando ou processando as mensagens corretamente.

---

## 1. Diagnosticar o Problema

### Usar o Comando de Diagnóstico
```bash
php artisan webhook:diagnose [numero_telefone]
```

Exemplo com seu número:
```bash
php artisan webhook:diagnose 15556466644
```

O comando verifica:
- ✓ Configuração do WhatsApp (.env)
- ✓ Logs recentes de webhook
- ✓ Contatos existentes com esse número
- ✓ Conversas associadas

### Verificar Logs
```bash
tail -f storage/logs/laravel.log | grep -i webhook
```

Procure por:
- `"Webhook received"` - webhook foi recebido
- `"MessageReceived Event Dispatched"` - mensagem foi processada
- `"Webhook object not whatsapp_business_account"` - objeto incorreto

---

## 2. Testar o Webhook (sem enviar mensagem real)

### Acessar o Debug
1. Abra: `http://seu-app.com/webhook/debug`
2. Preencha os dados:
   - **Número do Remetente**: `15556466644` (seu número)
   - **Tipo**: Texto
   - **Mensagem**: "Olá, tudo bem?"
   - **Nome**: Seu nome
3. Clique em "Enviar Webhook de Teste"

Se funcionar:
- ✓ A conversa aparecerá em `/conversations`
- ✓ O endpoint está correto
- ✓ O processamento funciona

Se não funcionar:
- ✗ Há um problema no código ou configuração
- ✗ Verifique os logs

---

## 3. Causas Comuns do Problema

### A. Webhook URL não registrada na Meta/Facebook
**Sintoma**: Webhook nunca é recebido  
**Solução**:
1. Acesse [developers.facebook.com](https://developers.facebook.com)
2. Selecione seu App > Webhooks
3. Configure:
   - **URL**: `{{ config('app.url') }}/webhook`
   - **Verify Token**: `{{ config('services.whatsapp.verify_token') }}`
   - **Eventos**: `messages`, `message_template_status_update`

### B. Verify Token incorreto
**Sintoma**: Erro ao verificar webhook  
**Solução**:
1. Verifique `.env`:
   ```
   WA_VERIFY_TOKEN=seu_token_secreto
   ```
2. Confirme que é o mesmo no Meta Dashboard

### C. Access Token expirado
**Sintoma**: Webhook recebido, mas sem processar  
**Solução**:
1. Gere novo access token no Meta
2. Atualize `.env`:
   ```
   WA_ACCESS_TOKEN=seu_novo_token
   ```
3. Teste novamente

### D. Número em formato diferente
**Sintoma**: Webhook recebido, mas conversa não aparece  
**Causa**: Seu número pode estar salvo em formato diferente

**Solução**:
Verificar como o número está salvo:
```bash
php artisan webhook:diagnose 15556466644
```

Se encontrar variantes do número (com/sem 9 extra), isso é normal.

### E. Conversa foi encerrada
**Sintoma**: Nova mensagem não cria conversa, apenas aparece em uma antiga  
**Solução**: Verificar status da conversa:
```bash
SELECT * FROM conversations WHERE contact_id = X AND status = 'closed';
```

Se estiver `closed`, o webhook criará nova conversa automaticamente.

---

## 4. Verificação da Configuração

### Checklist de Verificação
```bash
# 1. Verificar credenciais
php artisan tinker
> config('services.whatsapp.phone_number_id')
> config('services.whatsapp.access_token') ? '✓' : '✗'
> config('services.whatsapp.verify_token') ? '✓' : '✗'

# 2. Verificar contatos
> \App\Models\Contact::where('phone', 'like', '%1555%')->first()

# 3. Verificar logs
> \Illuminate\Support\Facades\Log::channel('single')->getMonolog()
```

### Verificar Normalizando Números
```bash
php artisan tinker
> \App\Support\PhoneNormalizer::digits('15556466644')
> \App\Support\PhoneNormalizer::variants('15556466644')
> \App\Support\PhoneNormalizer::forApi('15556466644')
```

---

## 5. Fluxo de Processamento

Quando uma mensagem é recebida:

```
1. Meta envia POST /webhook
   ↓
2. WebhookController::verify() valida token
   ↓
3. WebhookController::handle() valida objeto
   ↓
4. WhatsAppService::processWebhook() processa
   ↓
5. handleInboundMessage() cria/atualiza contato
   ↓
6. Conversa é criada (se não existir)
   ↓
7. Mensagem é salva
   ↓
8. Evento MessageReceived é disparado (broadcast)
   ↓
9. Mensagem aparece em /conversations
```

**Se travar em algum passo**: Verifique os logs

---

## 6. Logs Importantes para Monitorar

```
# Webhook recebido
Log::info('Webhook received', ['payload' => $payload]);

# Mensagem processada
Log::info('[MessageReceived Event] Dispatched', [...]);

# Erro de processamento
Log::error('...')
```

---

## 7. Próximos Passos

Se ainda não funcionar:

1. **Use `/webhook/debug`** para testar sem enviar mensagem real
2. **Execute `webhook:diagnose`** para ver status
3. **Verifique logs** em `storage/logs/laravel.log`
4. **Confirme configuração** no Meta Dashboard
5. **Teste com `curl`**:

```bash
curl -X POST http://localhost:8000/webhook/debug \
  -H "Content-Type: application/json" \
  -d '{
    "from_phone": "15556466644",
    "message_type": "text",
    "message_content": "teste",
    "contact_name": "Teste"
  }'
```

---

## 8. Contato e Suporte

Se após todos esses passos o webhook ainda não funcionar:

1. Verifique o arquivo de log completo
2. Use `/webhook/debug` para confirmar que o backend processa
3. Confirme a URL registrada na Meta é: `{{ config('app.url') }}/webhook`
4. Verifique se há firewall/proxy bloqueando
5. Teste com ngrok se estiver em desenvolvimento local
