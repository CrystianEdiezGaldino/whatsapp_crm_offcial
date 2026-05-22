# Webhook WhatsApp + Telefone (SMCC)

> **Guia completo:** veja [AJUDA_WHATSAPP.md](./AJUDA_WHATSAPP.md) (webhook, texto, anexos, áudio, erros, checklist).

---

## URLs

| Uso | URL local | URL pública (ngrok) |
|-----|-----------|---------------------|
| Verificação (GET) | `http://127.0.0.1:8000/api/webhook/whatsapp` | `https://SEU-NGROK.ngrok-free.app/api/webhook/whatsapp` |
| Mensagens (POST) | mesma rota | mesma rota |

No Meta: **developers.facebook.com** → App → WhatsApp → Configuration → Webhook.

- **Callback URL:** `https://SEU-NGROK.ngrok-free.app/api/webhook/whatsapp`
- **Verify token:** igual ao `WA_VERIFY_TOKEN` no `.env`
- **Campos:** marcar `messages` (e `message_status` se quiser status entregue/lido)

## Fluxo inbound (receber — funciona)

```
WhatsApp → Meta → POST webhook → WebhookController::handle
  → WhatsAppService::processWebhook
  → Contact::findOrCreateByPhone (unifica 5541997796908 e 554197796908)
  → Conversation open do contato
  → Message direction=inbound
```

A tela atualiza pelo **poll** a cada 5s: `GET /conversations/{id}/poll?last_id=...`

**Deduplicação na UI:** `ChatInboxHelper` (PHP + JS em `public/js/helpers/chat-inbox.js`) usa `dedupe_key` (`wa_message_id` ou `id`) para não exibir a mesma mensagem duas vezes após envio AJAX + poll (texto ou anexo).

## Fluxo outbound (enviar — ERP → Meta)

```
UI POST /conversations/send
  → ConversationController::sendMessage
  → WhatsAppService::sendText (tenta variantes do telefone)
  → Graph API POST /{phone_number_id}/messages
  → salva Message direction=outbound
```

## Telefone BR — regra importante

O mesmo celular pode aparecer em **dois formatos**:

| Formato | Exemplo | Quem usa |
|---------|---------|----------|
| Com 9 após DDD | `5541997796908` | Cadastro manual, alguns formulários |
| Sem 9 (Meta/WhatsApp) | `554197796908` | Webhook `from`, API de envio |

O sistema normaliza com `PhoneNormalizer` e `Contact::findOrCreateByPhone()` para **um único contato**.

**Canônico para API/envio:** `554197796908` (12 dígitos após `55`).

## Erro ao enviar (#131030)

```
Recipient phone number not in allowed list
```

Número de **teste** da Meta só envia para telefones na **lista permitida**.

1. Abra **developers.facebook.com** → seu app → **WhatsApp** → **API Setup**
2. Em **"Send and receive messages"** / **"To"**, adicione:
   - `554197796908`
   - `5541997796908` (se um falhar, teste o outro)
3. Salve e envie de novo pelo ERP

Receber webhook **não exige** lista; **enviar** em modo teste **exige**.

## Variáveis `.env`

```env
WA_PHONE_NUMBER_ID=1042095725664146
WA_ACCESS_TOKEN=seu_token
WA_VERIFY_TOKEN=seu_token_webhook
WA_API_VERSION=v25.0
WA_BASE_URL=https://graph.facebook.com
```

## Comandos úteis

```bash
# Unificar contatos duplicados (mesmo celular, formatos diferentes)
php artisan contacts:merge-duplicates

# Ver logs do webhook/envio
tail -f storage/logs/laravel.log
```

## Envio de áudio (sempre como anexo)

O ERP **não** usa `type: audio` no envio (evita “mídia não existe” no celular do cliente).

1. **Upload** → `POST /media` (MP3/M4A, etc.)
2. **Enviar** → `type: document` + `document: { id, filename: "audio.mp3" }`

| Origem | Tratamento |
|--------|------------|
| MP3, M4A, AAC, AMR | upload + envio como **documento** |
| Gravação WebM | ffmpeg → **MP3** → documento |
| Áudio recebido (webhook) | continua `type: audio` inbound (sem mudança) |

```env
# Opcional se ffmpeg não estiver no PATH
WA_FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
```

Instale ffmpeg no servidor para gravação pelo microfone funcionar no WhatsApp.

## Checklist rápido

- [ ] `php artisan serve` rodando
- [ ] ngrok apontando para porta 8000
- [ ] Webhook verificado (GET com challenge OK)
- [ ] `WA_ACCESS_TOKEN` válido (não expirado)
- [ ] Telefone do cliente na lista **To** da Meta (modo teste)
- [ ] Conversa aberta: `?conversation=15` com contato `554197796908`
