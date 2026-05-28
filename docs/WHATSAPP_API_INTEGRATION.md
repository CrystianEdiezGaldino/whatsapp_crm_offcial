# Documentação: Integração com WhatsApp via Meta API

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Autenticação](#autenticação)
3. [Endpoints Principais](#endpoints-principais)
4. [Enviando Mensagens de Texto](#enviando-mensagens-de-texto)
5. [Enviando Áudio](#enviando-áudio)
6. [Enviando Imagens/Fotos](#enviando-imagensfotos)
7. [Enviando Documentos/Anexos](#enviando-documentosanexos)
8. [Webhook](#webhook)
9. [Status de Mensagens](#status-de-mensagens)
10. [Tratamento de Erros](#tratamento-de-erros)
11. [Exemplos Práticos](#exemplos-práticos)

---

## Visão Geral

A integração com WhatsApp é feita via **Meta Cloud API** (anteriormente Facebook Graph API). O sistema utiliza a classe `WhatsAppService` para:

- Enviar e receber mensagens
- Gerenciar mídias (upload/download)
- Receber webhooks em tempo real
- Rastrear status de mensagens
- Gerenciar conversas

**Versão da API:** v23.0 (configurável em `config/services.php`)
**Base URL:** `https://graph.facebook.com`

---

## Autenticação

### Token de Acesso

O sistema suporta dois tipos de token:

#### 1. Token do Banco de Dados (Preferido)
```php
// Token armazenado em whatsapp_tokens com renovação automática
$token = WhatsAppToken::where('token_type', 'access')->first();
if ($token && !$token->isExpired()) {
    $accessToken = $token->token_value;
}
```

#### 2. Token do .env (Fallback)
```env
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=1234567890
WHATSAPP_VERIFY_TOKEN=webhook_verification_token
```

### Configuração

```php
// config/services.php
'whatsapp' => [
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'api_version' => 'v23.0',
    'base_url' => 'https://graph.facebook.com',
],
```

**Headers Obrigatórios:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

---

## Endpoints Principais

### Base URL
```
https://graph.facebook.com/v23.0/{phone_number_id}/messages
```

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/messages` | POST | Enviar mensagens |
| `/media` | POST | Upload de mídia |
| `/{media_id}` | GET | Obter informações da mídia |
| `/{phone}/profile_photo` | GET | Obter foto de perfil do contato |

---

## Enviando Mensagens de Texto

### Método: `sendText()`

```php
$whatsapp = new WhatsAppService();
$result = $whatsapp->sendText('5512987654321', 'Olá! Esta é uma mensagem de teste.');

if ($result) {
    $messageId = $result['messages'][0]['id'];
    echo "Mensagem enviada: {$messageId}";
} else {
    echo "Erro: " . $whatsapp->getUserFacingError();
}
```

### Estrutura da Requisição HTTP

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "5512987654321",
    "type": "text",
    "text": {
      "body": "Olá! Esta é uma mensagem de teste.",
      "preview_url": false
    }
  }'
```

### Resposta (Sucesso)

```json
{
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "5512987654321",
      "wa_id": "5512987654321"
    }
  ],
  "messages": [
    {
      "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz"
    }
  ]
}
```

### Parâmetros Disponíveis

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `to` | string | ✓ | Número do WhatsApp (com código país) |
| `body` | string | ✓ | Conteúdo da mensagem (até 4096 caracteres) |
| `preview_url` | boolean | | Mostrar preview de links (padrão: false) |

---

## Enviando Áudio

### Método: `sendAudio()`

O áudio deve ser previamente enviado para a Meta (upload). Existem dois tipos:

#### 1. Áudio Regular
```php
$whatsapp = new WhatsAppService();
$result = $whatsapp->sendAudio('5512987654321', 'media_id_from_upload', false);
```

#### 2. Áudio de Voz (Voice Message)
```php
$whatsapp = new WhatsAppService();
$result = $whatsapp->sendAudio('5512987654321', 'media_id_from_upload', true);
```

### Fluxo Completo: Upload e Envio de Áudio

```php
// 1. Upload do arquivo de áudio
$whatsapp = new WhatsAppService();
$mediaId = $whatsapp->uploadMedia(
    '/path/to/audio.mp3',
    'audio/mpeg',
    'audio.mp3'
);

if (!$mediaId) {
    echo "Erro no upload: " . $whatsapp->getUserFacingError();
    return;
}

// 2. Enviar áudio
$result = $whatsapp->sendAudio('5512987654321', $mediaId, true);

if ($result) {
    echo "Áudio enviado com sucesso!";
}
```

### Estrutura da Requisição HTTP

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "5512987654321",
    "type": "audio",
    "audio": {
      "id": "123456789",
      "voice": true
    }
  }'
```

### Formatos Suportados de Áudio

| Formato | MIME Type | Tamanho Máximo |
|---------|-----------|-----------------|
| AAC | audio/aac | 16 MB |
| MP3 | audio/mpeg | 16 MB |
| OGG | audio/ogg | 16 MB |
| OPUS | audio/opus | 16 MB |

---

## Enviando Imagens/Fotos

### Método: `sendMedia()` com type='image'

```php
// 1. Upload da imagem
$whatsapp = new WhatsAppService();
$mediaId = $whatsapp->uploadMedia(
    '/path/to/photo.jpg',
    'image/jpeg',
    'photo.jpg'
);

if (!$mediaId) {
    echo "Erro no upload: " . $whatsapp->getUserFacingError();
    return;
}

// 2. Enviar imagem com legenda (opcional)
$result = $whatsapp->sendMedia(
    '5512987654321',
    'image',
    $mediaId,
    'Esta é uma foto legal!'  // legenda opcional
);

if ($result) {
    echo "Imagem enviada com sucesso!";
}
```

### Estrutura da Requisição HTTP

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "5512987654321",
    "type": "image",
    "image": {
      "id": "123456789",
      "caption": "Esta é uma foto legal!"
    }
  }'
```

### Formatos Suportados de Imagem

| Formato | MIME Type | Tamanho Máximo |
|---------|-----------|-----------------|
| JPEG | image/jpeg | 16 MB |
| PNG | image/png | 16 MB |
| WebP | image/webp | 16 MB |

---

## Enviando Documentos/Anexos

### Método: `sendMedia()` com type='document'

```php
// 1. Upload do documento
$whatsapp = new WhatsAppService();
$mediaId = $whatsapp->uploadMedia(
    '/path/to/document.pdf',
    'application/pdf',
    'relatorio.pdf'
);

if (!$mediaId) {
    echo "Erro no upload: " . $whatsapp->getUserFacingError();
    return;
}

// 2. Enviar documento (filename é o nome que aparecerá no WhatsApp)
$result = $whatsapp->sendMedia(
    '5512987654321',
    'document',
    $mediaId,
    'relatorio.pdf'  // será usado como filename
);

if ($result) {
    echo "Documento enviado com sucesso!";
}
```

### Estrutura da Requisição HTTP

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "5512987654321",
    "type": "document",
    "document": {
      "id": "123456789",
      "caption": "Relatório Mensal",
      "filename": "relatorio.pdf"
    }
  }'
```

### Formatos Suportados de Documento

| Formato | MIME Type | Tamanho Máximo |
|---------|-----------|-----------------|
| PDF | application/pdf | 16 MB |
| DOC | application/msword | 16 MB |
| DOCX | application/vnd.openxmlformats-officedocument.wordprocessingml.document | 16 MB |
| XLS | application/vnd.ms-excel | 16 MB |
| XLSX | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet | 16 MB |
| PPT | application/vnd.ms-powerpoint | 16 MB |
| PPTX | application/vnd.openxmlformats-officedocument.presentationml.presentation | 16 MB |
| TXT | text/plain | 16 MB |

---

## Upload de Mídia

### Método: `uploadMedia()`

Antes de enviar qualquer mídia, ela deve ser uploadada para a Meta Cloud.

```php
$whatsapp = new WhatsAppService();
$mediaId = $whatsapp->uploadMedia(
    '/absolute/path/to/file.pdf',
    'application/pdf',
    'optional-filename.pdf'
);

if ($mediaId) {
    echo "Mídia ID: {$mediaId}";
} else {
    echo "Erro: " . $whatsapp->getUserFacingError();
}
```

### Estrutura da Requisição HTTP (Multipart)

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/media" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -F "file=@/path/to/file.pdf" \
  -F "type=application/pdf" \
  -F "messaging_product=whatsapp"
```

### Resposta (Sucesso)

```json
{
  "id": "123456789",
  "url": "https://media-mss1.cdn.fbsbx.com/..."
}
```

---

## Download de Mídia

### Método: `downloadMedia()`

```php
$whatsapp = new WhatsAppService();
$localPath = $whatsapp->downloadMedia('media_id_from_webhook');

if ($localPath) {
    echo "Arquivo salvo em: {$localPath}";
    // Arquivo está em storage/app/public/media/
} else {
    echo "Erro no download";
}
```

---

## Webhook

### Visão Geral

O webhook da Meta envia notificações em tempo real sobre:
- Mensagens recebidas
- Mudanças de status de mensagens
- Entregas
- Leituras

### Rotas Webhook

```php
// routes/api.php
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle'])->withoutMiddleware('auth');
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify'])->withoutMiddleware('auth');
```

### Verificação do Webhook (GET)

Quando você configura o webhook na Meta, ela envia uma requisição GET para verificação:

```bash
GET /api/webhook/whatsapp?hub_mode=subscribe&hub_challenge=CHALLENGE_TOKEN&hub_verify_token=VERIFY_TOKEN
```

O sistema deve responder com o `challenge` se o `verify_token` estiver correto:

```php
public function verify(Request $request)
{
    $mode = $request->input('hub_mode');
    $token = $request->input('hub_verify_token');
    $challenge = $request->input('hub_challenge');

    $verifyToken = config('services.whatsapp.verify_token');

    if ($mode === 'subscribe' && $token === $verifyToken) {
        return response($challenge, 200);  // Meta espera apenas o challenge como string
    }

    return response('Forbidden', 403);
}
```

### Recebimento de Webhook (POST)

Estrutura básica do payload recebido:

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "12345678",
      "changes": [
        {
          "field": "messages",
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "5512987654321",
              "phone_number_id": "123456789"
            },
            "contacts": [
              {
                "profile": {
                  "name": "João Silva"
                },
                "wa_id": "5512987654321"
              }
            ],
            "messages": [
              {
                "from": "5512987654321",
                "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
                "timestamp": "1671234567",
                "type": "text",
                "text": {
                  "body": "Olá!"
                }
              }
            ]
          }
        }
      ]
    }
  ]
}
```

### Processamento de Webhook

```php
public function handle(Request $request)
{
    $payload = $request->all();

    // Validar se é um webhook válido
    $object = $payload['object'] ?? null;
    if ($object !== 'whatsapp_business_account') {
        return response('Ignored', 200);
    }

    try {
        // Processar webhook
        WhatsAppService::processWebhook($payload);
        return response('OK', 200);
    } catch (\Exception $e) {
        Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
        return response('OK', 200);  // Retornar 200 mesmo em erro para evitar retry
    }
}
```

### Tipos de Mensagens Recebidas

#### 1. Mensagem de Texto
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "text",
  "text": {
    "body": "Olá!"
  }
}
```

#### 2. Imagem
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "image",
  "image": {
    "id": "123456789",
    "mime_type": "image/jpeg",
    "sha256": "abc123...",
    "caption": "Descrição opcional"
  }
}
```

#### 3. Documento/Arquivo
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "document",
  "document": {
    "id": "123456789",
    "mime_type": "application/pdf",
    "sha256": "abc123...",
    "filename": "documento.pdf"
  }
}
```

#### 4. Áudio
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "audio",
  "audio": {
    "id": "123456789",
    "mime_type": "audio/ogg",
    "sha256": "abc123...",
    "voice": true
  }
}
```

#### 5. Vídeo
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "video",
  "video": {
    "id": "123456789",
    "mime_type": "video/mp4",
    "sha256": "abc123...",
    "caption": "Descrição opcional"
  }
}
```

#### 6. Localização
```json
{
  "from": "5512987654321",
  "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
  "timestamp": "1671234567",
  "type": "location",
  "location": {
    "latitude": "-23.5505",
    "longitude": "-46.6333",
    "name": "São Paulo",
    "address": "Av. Paulista, 1000"
  }
}
```

### Webhook de Status

Quando o status de uma mensagem muda, recebemos:

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "changes": [
        {
          "field": "message_status",
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "phone_number_id": "123456789"
            },
            "statuses": [
              {
                "id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz",
                "status": "delivered",
                "timestamp": "1671234567",
                "recipient_id": "5512987654321"
              }
            ]
          }
        }
      ]
    }
  ]
}
```

---

## Status de Mensagens

### Ciclo de Vida

```
┌─────────────────────────────────────────┐
│                                         │
│  pending → sent → delivered → read      │
│                                         │
│  (ou failed em qualquer ponto)          │
└─────────────────────────────────────────┘
```

| Status | Descrição |
|--------|-----------|
| **pending** | Mensagem ainda não foi enviada |
| **sent** | Mensagem foi enviada para os servidores da Meta |
| **delivered** | Mensagem foi entregue ao dispositivo do contato |
| **read** | Contato leu a mensagem |
| **failed** | Falha no envio |

### Rastreamento de Status

```php
// Quando recebemos um webhook de status, atualizamos:
$message = Message::where('wa_message_id', $waMsgId)->first();
if ($message) {
    $message->update(['status' => $newStatus]);
    event(new MessageStatusChanged($message));
}
```

---

## Marcar Mensagem como Lida

### Método: `markAsRead()`

```php
$whatsapp = new WhatsAppService();
$success = $whatsapp->markAsRead('wamid.AbCdEfGhIjKlMnOpQrStUvWxYz');

if ($success) {
    echo "Mensagem marcada como lida";
}
```

### Estrutura da Requisição HTTP

```bash
curl -X POST \
  "https://graph.facebook.com/v23.0/123456789/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "status": "read",
    "message_id": "wamid.AbCdEfGhIjKlMnOpQrStUvWxYz"
  }'
```

---

## Tratamento de Erros

### Erros Comuns

| Código | Mensagem | Solução |
|--------|----------|---------|
| 131030 | Invalid number format | Verificar formato do número com código país |
| 100 | Invalid parameter | Validar parâmetros da requisição |
| 500 | Internal Server Error | Tentar novamente após alguns segundos |
| 1003 | Message rate limit hit | Aguardar antes de enviar nova mensagem |
| 131000 | Invalid recipient | Contato não existe ou número inválido |

### Método de Erro

```php
$whatsapp = new WhatsAppService();
$result = $whatsapp->sendText('invalid_number', 'Teste');

if (!$result) {
    $error = $whatsapp->getLastError();
    echo "Código: " . $error['code'];
    echo "Mensagem: " . $error['message'];
    echo "User Message: " . $whatsapp->getUserFacingError();
}
```

### Resposta de Erro HTTP

```json
{
  "error": {
    "message": "Invalid number format",
    "type": "OAuthException",
    "code": 131030,
    "fbtrace_id": "..."
  }
}
```

---

## Exemplos Práticos

### 1. Enviar Mensagem Simples com Arquivo

```php
use App\Services\WhatsAppService;

$phone = '5512987654321';
$filePath = '/path/to/file.pdf';
$mimeType = 'application/pdf';

$whatsapp = new WhatsAppService();

// Upload
$mediaId = $whatsapp->uploadMedia($filePath, $mimeType);
if (!$mediaId) {
    return redirect()->back()->with('error', $whatsapp->getUserFacingError());
}

// Envio
$result = $whatsapp->sendMedia($phone, 'document', $mediaId, 'Documento importante');
if (!$result) {
    return redirect()->back()->with('error', $whatsapp->getUserFacingError());
}

return redirect()->back()->with('success', 'Arquivo enviado com sucesso!');
```

### 2. Processar Webhook e Salvar Mensagem

```php
public function handleWebhook(Request $request)
{
    $payload = $request->all();
    $entry = $payload['entry'][0]['changes'][0]['value'] ?? null;

    if (!$entry) {
        return response('OK', 200);
    }

    // Processar mensagens
    foreach ($entry['messages'] ?? [] as $waMessage) {
        $phone = $waMessage['from'];
        $type = $waMessage['type'];
        
        // Encontrar ou criar contato
        $contact = Contact::findOrCreateByPhone($phone, [
            'name' => $entry['contacts'][0]['profile']['name'] ?? $phone,
        ]);

        // Criar conversa
        $conversation = Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'status' => 'new'],
            ['last_message_at' => now()]
        );

        // Extrair conteúdo baseado no tipo
        $content = match($type) {
            'text' => $waMessage['text']['body'] ?? null,
            'image' => $waMessage['image']['caption'] ?? null,
            'document' => $waMessage['document']['filename'] ?? null,
            default => null,
        };

        // Salvar mensagem
        $conversation->messages()->create([
            'wa_message_id' => $waMessage['id'],
            'direction' => 'inbound',
            'type' => $type,
            'content' => $content,
            'media_id' => $waMessage[$type]['id'] ?? null,
            'status' => 'delivered',
        ]);
    }

    return response('OK', 200);
}
```

### 3. Enviar Áudio Gravado

```php
public function sendRecordedAudio(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations',
        'audio_file' => 'required|file|mimes:webm,mp3,ogg|max:16384',
    ]);

    $conversation = Conversation::findOrFail($request->conversation_id);
    $audioFile = $request->file('audio_file');
    
    $whatsapp = new WhatsAppService();

    // Detectar MIME type
    $mimeType = $audioFile->getMimeType() ?: 'audio/ogg';

    // Converter se necessário (webm para mp3, etc)
    $filePath = $audioFile->store('temp', 'local');
    
    // Upload
    $mediaId = $whatsapp->uploadMedia(
        storage_path("app/{$filePath}"),
        $mimeType,
        $audioFile->getClientOriginalName()
    );

    if (!$mediaId) {
        Storage::delete($filePath);
        return response()->json(['error' => $whatsapp->getUserFacingError()], 422);
    }

    // Enviar como áudio de voz
    $result = $whatsapp->sendAudio(
        $conversation->contact->phone,
        $mediaId,
        true  // asVoice
    );

    // Limpar arquivo temporário
    Storage::delete($filePath);

    if (!$result) {
        return response()->json(['error' => $whatsapp->getUserFacingError()], 422);
    }

    // Salvar registro de mensagem
    $conversation->messages()->create([
        'wa_message_id' => $result['messages'][0]['id'],
        'direction' => 'outbound',
        'type' => 'audio',
        'content' => 'Voice Message',
        'media_id' => $mediaId,
        'status' => 'sent',
        'sender_id' => auth()->id(),
    ]);

    return response()->json(['success' => true]);
}
```

### 4. Configurar Webhook na Meta

```bash
# 1. Fazer POST para configurar webhook
curl -X POST "https://graph.facebook.com/v23.0/YOUR_PHONE_NUMBER_ID/subscribed_apps" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "subscribed_fields": [
      "messages",
      "message_status",
      "message_template_status_update",
      "message_template_quality_status_update"
    ]
  }'

# 2. Configurar o webhook URL no painel da Meta
# Webhook URL: https://seu-dominio.com/api/webhook/whatsapp
# Verify Token: (configure uma senha forte em .env)
```

---

## Referências

- [Meta WhatsApp Cloud API - Documentação Oficial](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Tipos de Mensagens](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages)
- [Webhook de Status](https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks/payload-examples)
- [Endpoints da API](https://developers.facebook.com/docs/whatsapp/cloud-api/reference)

---

**Última atualização:** Maio de 2026
**Versão da API:** v23.0
