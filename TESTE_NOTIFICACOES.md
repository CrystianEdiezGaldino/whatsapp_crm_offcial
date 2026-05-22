# Guia de Teste — Notificações com Áudio

## ✅ O que foi implementado (Phase 1)

### 1. **Broadcasting com Pusher**
- ✓ Configuração Pusher (credenciais em `.env`)
- ✓ Laravel Echo para real-time updates
- ✓ Event `App\Events\MessageReceived` criado

### 2. **Frontend**
- ✓ Bootstrap.js com Echo configurado
- ✓ Listener no chat para canal `conversation.{id}`
- ✓ Toast HTML para notificação inline
- ✓ Som de notificação em `/public/sounds/notification.mp3`

### 3. **Backend**
- ✓ WhatsAppService dispara `MessageReceived` event ao receber webhook
- ✓ Evento é broadcastado para o canal da conversa
- ✓ Logging de eventos para debug

---

## 🧪 Como Testar

### Pré-requisitos
- Laravel server rodando: `php artisan serve`
- Vite dev server rodando: `npm run dev` (desenvolvimento)
- Navegador atualizado com suporte a WebSocket

### Teste Local (sem WhatsApp real)

#### 1. Abrir Chat
```
1. Acesse: http://localhost:8000/conversations
2. Selecione uma conversa
3. Abra DevTools (F12) → Console
```

#### 2. Simular Evento (via Tinker)
```bash
php artisan tinker

# Dentro do tinker:
$msg = App\Models\Message::first();
event(new App\Events\MessageReceived($msg, $msg->conversation_id, null));
```

#### 3. Resultado Esperado
- ✓ Som toca no navegador (beep de 1s)
- ✓ Toast verde aparece por 5 segundos
- ✓ Notificação desktop (se permissão concedida)
- ✓ Console mostra: `[Echo] Nova mensagem recebida:`

---

## 🔧 Troubleshooting

### Som não toca
**Causa:** Autoplay bloqueado pelo navegador ou arquivo não encontrado
**Solução:**
1. Verificar permissões do navegador (site → sound)
2. Verificar se `/public/sounds/notification.mp3` existe
3. Usar som padrão do sistema em produção (integrar mais tarde)

### Notificação Desktop não aparece
**Causa:** Permissão não concedida
**Solução:**
1. Navegador pedirá permissão ao abrir chat
2. Clique "Permitir" ou configure em preferências

### Echo não conecta
**Causa:** Pusher credentials inválidas ou Echo não carregado
**Solução:**
1. Verificar `.env` (BROADCAST_DRIVER=pusher, credenciais)
2. Verificar DevTools → Network → WS (WebSocket deve conectar)
3. Verificar console por erros de CORS

---

## 📊 Com WhatsApp Real

### 1. Configurar Webhook (ngrok)
```bash
# Terminal 1: ngrok
ngrok http 8000

# Terminal 2: Laravel
php artisan serve

# Terminal 3: Vite
npm run dev
```

### 2. Atualizar Webhook na Meta
- Callback URL: `https://xxxx.ngrok-free.app/api/webhook/whatsapp`
- Verify token: mesmo do `.env` (WA_VERIFY_TOKEN)

### 3. Enviar Mensagem pelo WhatsApp
```bash
# Usar cliente WhatsApp real ou API de teste:
curl -X POST "https://graph.facebook.com/v25.0/{PHONE_NUMBER_ID}/messages" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "messaging_product":"whatsapp",
    "to":"554197796908",
    "type":"text",
    "text":{"body":"Teste de notificação"}
  }'
```

### 4. Validar Logs
```bash
tail -f storage/logs/laravel.log | grep -E "MessageReceived|Webhook"
```

---

## 📝 Próximas Melhorias

- [ ] Implementar som customizável por usuário
- [ ] Adicionar badge no ícone de chat (número de não lidas)
- [ ] Integrar com vibração do device (mobile)
- [ ] Sistema de preferências de notificação (silenciar por hora)

---

## 🚀 Checklist Pré-Deploy

- [ ] `npm run build` compilou sem erros
- [ ] Som toca em Firefox, Chrome, Safari, Edge
- [ ] Desktop notification pede permissão
- [ ] Webhook verifica token (segurança)
- [ ] Logs mostram eventos (`[MessageReceived Event]`)
- [ ] Rate limiting ativo (prevenir spam)

---

**Data:** 21 de maio de 2026  
**Status:** ✅ Implementação concluída, teste manual pendente
