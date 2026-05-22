# Ajuda completa — WhatsApp Cloud API (SMCC ERP)

Guia de referência do projeto: webhook, envio de texto, anexos, áudio, telefone BR e interface de chat.

---

## Índice

1. [Visão geral](#1-visão-geral)
2. [Configuração](#2-configuração)
3. [Webhook (receber mensagens)](#3-webhook-receber-mensagens)
4. [Envio de texto](#4-envio-de-texto)
5. [Envio de anexos](#5-envio-de-anexos)
6. [Envio de áudio](#6-envio-de-áudio)
7. [Telefone brasileiro e contatos duplicados](#7-telefone-brasileiro-e-contatos-duplicados)
8. [Interface do chat (ERP)](#8-interface-do-chat-erp)
9. [Erros comuns](#9-erros-comuns)
10. [Comandos e manutenção](#10-comandos-e-manutenção)
11. [Mapa de arquivos do código](#11-mapa-de-arquivos-do-código)
12. [Checklist de produção / teste](#12-checklist-de-produção--teste)

---

## 1. Visão geral

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────────────────┐
│ Cliente WA  │────▶│ Meta Cloud   │────▶│ POST /api/webhook/whatsapp  │
└─────────────┘     │ API          │     │ (inbound → banco de dados)  │
       ▲            └──────────────┘     └─────────────────────────────┘
       │                    ▲                            │
       │                    │                            ▼
       │            ┌───────┴────────┐          ┌───────────────┐
       └────────────│ Agente no ERP  │◀─────────│ MySQL + UI    │
                    │ POST /send     │          │ poll 5s       │
                    └────────────────┘          └───────────────┘
```

| Direção | Rota ERP | Serviço |
|---------|----------|---------|
| Receber | `GET/POST /api/webhook/whatsapp` | `WebhookController` → `WhatsAppService::processWebhook` |
| Enviar | `POST /conversations/send` | `ConversationController::sendMessage` → `WhatsAppService` |
| Atualizar tela | `GET /conversations/{id}/poll` | Retorna mensagens novas (JSON) |

**Princípio de mídia outbound:** sempre **upload na Meta** → obter `media_id` → enviar mensagem com esse `id`. Não usar URL pública temporária no envio (instável).

---

## 2. Configuração

### 2.1 Variáveis `.env`

```env
APP_URL=http://127.0.0.1:8000

WA_PHONE_NUMBER_ID=1042095725664146
WA_ACCESS_TOKEN=EAAxxxx...
WA_VERIFY_TOKEN=seu_token_secreto_webhook
WA_API_VERSION=v25.0
WA_BASE_URL=https://graph.facebook.com

# Conversão de gravação WebM → MP3 (microfone no navegador)
WA_FFMPEG_PATH=ffmpeg
# Windows exemplo: WA_FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
```

| Variável | Uso |
|----------|-----|
| `WA_PHONE_NUMBER_ID` | ID do número WhatsApp Business na Meta |
| `WA_ACCESS_TOKEN` | Token permanente ou de teste (Graph API) |
| `WA_VERIFY_TOKEN` | Mesmo valor cadastrado no webhook da Meta |
| `WA_FFMPEG_PATH` | Binário ffmpeg para gravar áudio no ERP |

### 2.2 Meta Developer (webhook)

1. Acesse [developers.facebook.com](https://developers.facebook.com) → seu app → **WhatsApp** → **Configuration**.
2. **Callback URL:** `https://SEU-DOMINIO/api/webhook/whatsapp`  
   - Local: use **ngrok** → `https://xxxx.ngrok-free.app/api/webhook/whatsapp`
3. **Verify token:** igual a `WA_VERIFY_TOKEN`.
4. Assine o campo **`messages`** (recomendado também status de entrega, se disponível).

### 2.3 ngrok (desenvolvimento local)

```bash
ngrok http 8000
```

Use a URL HTTPS gerada no painel da Meta. O ERP deve estar rodando:

```bash
php artisan serve
php artisan storage:link
```

### 2.4 Modo teste — lista de números (envio)

Em **WhatsApp → API Setup → To**, cadastre os celulares que podem **receber** mensagens do número de teste:

- `554197796908` (formato Meta / webhook)
- `5541997796908` (formato com 9 após DDD)

Sem isso, o envio retorna erro **#131030** (não está na lista permitida). **Receber** webhook não depende dessa lista.

---

## 3. Webhook (receber mensagens)

### 3.1 Rotas

| Método | URL | Ação |
|--------|-----|------|
| `GET` | `/api/webhook/whatsapp` | Verificação (challenge da Meta) |
| `POST` | `/api/webhook/whatsapp` | Recebe mensagens e status |

Arquivo: `routes/api.php` → `WebhookController`.

### 3.2 Verificação (GET)

A Meta envia:

- `hub_mode=subscribe`
- `hub_verify_token=...` (deve bater com `WA_VERIFY_TOKEN`)
- `hub_challenge=...`

Se o token confere, o ERP responde com o **challenge** em texto puro (status 200).

### 3.3 Recebimento (POST)

Fluxo em `WhatsAppService::processWebhook`:

```
Payload JSON
  └─ object = whatsapp_business_account
       └─ entry[].changes[].value
            ├─ messages[]  → handleInboundMessage (nova mensagem)
            └─ statuses[]  → handleStatusUpdate (sent/delivered/read)
```

**Para cada mensagem inbound:**

1. Normaliza telefone (`PhoneNormalizer`).
2. `Contact::findOrCreateByPhone` — um contato por número (variantes BR unificadas).
3. `Conversation` aberta (`status = open`) do contato.
4. Cria `Message` com `direction = inbound`.
5. Se tiver mídia (`image`, `audio`, `video`, `document`…), baixa via API e salva em `storage/app/public/media/`.

**Deduplicação:** se `wa_message_id` já existe no banco, ignora (evita processar o mesmo evento duas vezes).

### 3.4 Exemplo simplificado de payload (texto)

```json
{
  "object": "whatsapp_business_account",
  "entry": [{
    "changes": [{
      "field": "messages",
      "value": {
        "messaging_product": "whatsapp",
        "metadata": { "phone_number_id": "1042095725664146" },
        "contacts": [{ "profile": { "name": "Maycon" }, "wa_id": "554197796908" }],
        "messages": [{
          "from": "554197796908",
          "id": "wamid.xxx",
          "timestamp": "1716312000",
          "type": "text",
          "text": { "body": "Olá" }
        }]
      }
    }]
  }]
}
```

### 3.5 Logs

```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

Procure por: `Webhook received`, erros de token ou payload ignorado (`object` diferente de `whatsapp_business_account`).

---

## 4. Envio de texto

### 4.1 Na interface

1. Abra **Chats** → selecione a conversa.
2. Digite no campo de mensagem.
3. Envie com o botão ou **Ctrl+Enter**.

### 4.2 No código

```
POST /conversations/send
  conversation_id, content (texto)
    → WhatsAppService::sendText
    → Graph API: POST /{phone_number_id}/messages
         { type: "text", text: { body: "..." } }
    → Salva Message (outbound) no banco
```

A API tenta variantes do telefone BR (`554199...` / `554197...`) se a Meta rejeitar um formato.

### 4.3 Resposta de sucesso (JSON)

```json
{
  "success": true,
  "message": {
    "id": 120,
    "dedupe_key": "wa:wamid.HBg...",
    "direction": "outbound",
    "type": "text",
    "content": "Olá!",
    "created_at": "2026-05-21T18:30:00+00:00"
  }
}
```

---

## 5. Envio de anexos

Anexos = **imagem**, **vídeo** ou **documento** (PDF, planilha, etc.). Fluxo em duas etapas (recomendado pela Meta).

### 5.1 Etapa 1 — Upload na Meta

```http
POST https://graph.facebook.com/v25.0/{PHONE_NUMBER_ID}/media
Authorization: Bearer {WA_ACCESS_TOKEN}
Content-Type: multipart/form-data

file=@arquivo.pdf
type=application/pdf
messaging_product=whatsapp
```

Resposta: `{ "id": "1037543291543632" }` ← este é o **media_id**.

Implementação: `WhatsAppService::uploadMedia($caminho, $mime, $nomeArquivo)`.

### 5.2 Etapa 2 — Enviar mensagem

| Tipo no WhatsApp | Campo JSON | Limite ERP |
|------------------|------------|------------|
| Imagem | `type: image`, `image: { id }` | até 16 MB (`max:16384` KB) |
| Vídeo | `type: video`, `video: { id }` | até 16 MB |
| Documento | `type: document`, `document: { id, filename }` | até 16 MB |

Legenda opcional: `caption` em imagem/vídeo/documento (quando suportado).

### 5.3 Na interface ERP

1. Clique no ícone de **anexo** na barra do chat.
2. Escolha arquivo (ou grave áudio — ver seção 6).
3. Opcional: texto de legenda.
4. Enviar.

O arquivo é salvo localmente em `storage/app/public/media/` **e** enviado à Meta via upload.

### 5.4 Fluxo no código

```
ConversationController::sendMessage (attachment)
  → detecta MIME: image/*, video/*, ou documento
  → uploadMedia → media_id
  → sendMedia(phone, type, media_id, caption|filename)
  → Message outbound no banco (media_url local para preview no ERP)
```

### 5.5 Tipos MIME comuns

| Arquivo | MIME típico | type WhatsApp |
|---------|-------------|---------------|
| JPG/PNG | `image/jpeg`, `image/png` | `image` |
| MP4 | `video/mp4` | `video` |
| PDF | `application/pdf` | `document` |
| DOCX | `application/vnd...` | `document` |

---

## 6. Envio de áudio

### 6.1 Por que não enviamos `type: audio`?

Mensagens de áudio nativas (`type: audio`) podem falhar no celular do cliente com **“mídia não existe”** ou pedido para reenviar, dependendo de codec e ambiente.

**Solução adotada no SMCC:** todo áudio **enviado pelo ERP** vai como **documento anexo** (arquivo `.mp3` baixável no WhatsApp).

**Áudio recebido** do cliente continua como `type: audio` no webhook — reprodução no ERP funciona normalmente.

### 6.2 Fluxo de envio de áudio

```
Arquivo (MP3 / gravação WebM / etc.)
  → AudioMediaPreparer::prepare(..., asAttachment: true)
       ├─ MP3/M4A/AAC/AMR: usa direto (nome arquivo .mp3, .m4a…)
       └─ WebM / OGG: ffmpeg converte para MP3
  → uploadMedia (MIME audio/mpeg)
  → sendMedia(..., type: document, filename: audio.mp3)
  → Cliente vê ANEXO no WhatsApp (pode baixar e ouvir)
```

### 6.3 Formatos

| Origem | Tratamento |
|--------|------------|
| Upload MP3, M4A, AAC, AMR | Documento com extensão correta |
| Gravação pelo microfone (WebM) | **ffmpeg** → MP3 → documento |
| OGG enviado manualmente | Converte para MP3 antes do upload |

### 6.4 ffmpeg

Obrigatório para **gravar pelo navegador**. Sem ffmpeg:

- Erro claro pedindo instalação ou envio de MP3 pronto.

Teste no terminal:

```bash
ffmpeg -version
```

### 6.5 Voice message (bolha nativa)

Não usamos no envio outbound. Só faria sentido com OGG + OPUS + `voice: true` na API; o projeto prioriza **anexo estável**.

### 6.6 Limites

- Tamanho máximo upload ERP: **16 MB** (16384 KB).
- Formatos aceitos pela Meta para documento/áudio: ver documentação oficial; o ERP normaliza para MP3 no envio.

---

## 7. Telefone brasileiro e contatos duplicados

### 7.1 Dois formatos do mesmo celular

| Formato | Exemplo |
|---------|---------|
| Com 9 após DDD | `5541997796908` |
| Formato Meta (webhook) | `554197796908` |

Classes: `App\Support\PhoneNormalizer`, `Contact::findOrCreateByPhone()`.

### 7.2 Sintoma de duplicata

- Webhook grava mensagem em **outra conversa**.
- Você olha a conversa 15 e não vê mensagens novas.

### 7.3 Correção

```bash
php artisan contacts:merge-duplicates
```

Unifica contatos e move mensagens para a conversa aberta correta.

**Canônico para envio API:** `554197796908` (12 dígitos após `55`).

---

## 8. Interface do chat (ERP)

### 8.1 Atualização em tempo quase real

- **Poll** a cada 5 segundos: `GET /conversations/{id}/poll?last_id=N`
- Retorna apenas mensagens com `id > N`.

### 8.2 Envio sem recarregar página

- Formulário `#chatForm` → AJAX `POST /conversations/send`
- Helper JS: `public/js/helpers/chat-inbox.js` (`ChatInboxHelper`)

### 8.3 Deduplicação (sem mensagem duplicada na tela)

Problema: AJAX adiciona a mensagem e o poll traz a mesma de novo.

Solução:

| Camada | Componente |
|--------|------------|
| PHP | `App\Helpers\ChatInboxHelper::dedupeKey()` → `wa:{wa_message_id}` ou `id:{id}` |
| JS | `ChatInboxHelper.appendIfNew()` + `Set` de chaves já exibidas |

Funciona para **texto**, **anexo** e **áudio**.

### 8.4 Preview no ERP

Arquivos locais: `/storage/media/...`  
Mídia inbound baixada da Meta: salva em `storage/app/public/media/`.

---

## 9. Erros comuns

| Código / sintoma | Causa | O que fazer |
|------------------|-------|-------------|
| **#131030** | Número não está na lista de teste | Meta → API Setup → adicionar em **To** |
| **#190** | Token inválido/expirado | Gerar novo token, atualizar `WA_ACCESS_TOKEN` |
| **#131053** | Upload de mídia falhou | Verificar MIME, tamanho ≤ 16 MB, certificado SSL |
| **131047** | Janela 24h fechada | Usar template aprovado |
| HTTP 500 no `/send` | Erro genérico antigo | Hoje retorna 422 com mensagem em português |
| Mensagem duplicada na UI | AJAX + poll | Já tratado com `ChatInboxHelper` |
| Áudio não toca no cliente | Enviado como audio nativo | Projeto envia como **documento** — atualize código |
| WebM sem ffmpeg | Conversão impossível | Instalar ffmpeg ou enviar MP3 |
| Conversa errada / sem msg | Telefone duplicado | `contacts:merge-duplicates` |
| Webhook não chega | ngrok parado / URL errada | Verificar URL e logs `Webhook received` |
| cURL error 60 SSL | Certificado CA local | `storage/cacert.pem` no Guzzle |

Mensagens amigáveis: `App\Support\WhatsAppApiError`.

---

## 10. Comandos e manutenção

```bash
# Servidor local
php artisan serve

# Link storage público
php artisan storage:link

# Unificar telefones duplicados
php artisan contacts:merge-duplicates

# Migrations (cuidado em produção)
php artisan migrate

# Testes relacionados
php artisan test --filter=PhoneNormalizerTest
php artisan test --filter=ChatInboxHelperTest
php artisan test --filter=AudioMediaPreparerTest
php artisan test --filter=WhatsAppApiErrorTest

# Logs
tail -f storage/logs/laravel.log
```

---

## 11. Mapa de arquivos do código

| Arquivo | Responsabilidade |
|---------|------------------|
| `routes/api.php` | Rotas webhook |
| `routes/web.php` | Chat, send, poll |
| `app/Http/Controllers/WebhookController.php` | Verify + handle webhook |
| `app/Http/Controllers/ConversationController.php` | Enviar mensagem/anexo/áudio, poll |
| `app/Services/WhatsAppService.php` | API Meta: send, upload, download, webhook |
| `app/Support/PhoneNormalizer.php` | Variantes telefone BR |
| `app/Support/AudioMediaPreparer.php` | Preparar áudio → MP3 anexo |
| `app/Support/WhatsAppApiError.php` | Mensagens de erro PT-BR |
| `app/Helpers/ChatInboxHelper.php` | JSON poll/send + dedupe_key |
| `app/Models/Contact.php` | `findOrCreateByPhone` |
| `app/Models/Message.php` | Mensagens |
| `app/Console/Commands/MergeDuplicateContacts.php` | Artisan merge |
| `public/js/helpers/chat-inbox.js` | UI: poll, send, dedupe |
| `resources/views/conversations/index.blade.php` | Tela de chat |
| `config/services.php` | Config WhatsApp + ffmpeg |
| `WEBHOOK_E_TELEFONE.md` | Resumo rápido (legado) |
| **`AJUDA_WHATSAPP.md`** | **Este guia completo** |

---

## 12. Checklist de produção / teste

### Ambiente

- [ ] `.env` com `WA_*` preenchidos
- [ ] `php artisan serve` ou servidor web apontando para `public/`
- [ ] `storage:link` executado
- [ ] HTTPS público para webhook (ngrok ou domínio real)
- [ ] Webhook verificado na Meta (ícone verde)
- [ ] ffmpeg no PATH (se usar gravação de áudio)

### Teste receber

- [ ] Cliente envia texto → aparece no ERP em até 5s
- [ ] Cliente envia imagem/áudio → preview no ERP
- [ ] Log: `Webhook received`

### Teste enviar

- [ ] Texto → chega no WhatsApp do cliente
- [ ] PDF/imagem → chega como mídia/anexo
- [ ] Áudio/gravação → chega como **arquivo .mp3** (documento)
- [ ] Não duplica mensagem na tela do ERP
- [ ] Número na lista **To** (modo teste)

### Contatos

- [ ] Um único contato por número (`merge-duplicates` se necessário)
- [ ] Telefone canônico `554197796908` após normalização

---

## Referências externas

- [WhatsApp Cloud API — Overview](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Send messages](https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages)
- [Webhooks](https://developers.facebook.com/docs/whatsapp/cloud-api/guides/set-up-webhooks)
- [Upload media](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/media)

---

*Documento gerado para o projeto SMCC WhatsApp ERP. Em caso de dúvida, consulte os logs em `storage/logs/laravel.log`.*
