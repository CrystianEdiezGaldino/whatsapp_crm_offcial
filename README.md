# SisZap — CRM WhatsApp (Santa Mônica CC)

Sistema de atendimento omnichannel integrado à **WhatsApp Cloud API (Meta)**. Centraliza conversas, distribui atendimentos por setor, automatiza fluxos iniciais (bot) e oferece painel para agentes e administradores.

---

## O que o sistema faz

| Área | Função |
|------|--------|
| **Atendimentos** | Chat em tempo real com clientes via WhatsApp (texto, áudio, imagem, documento) |
| **Contatos** | Cadastro e histórico de clientes |
| **Macros** | Respostas rápidas reutilizáveis (`/oi`, `/aguarde`, etc.) |
| **Fluxos (bot)** | Menu automático na nova conversa — direciona para setores |
| **Distribuição** | Fila e roteamento de conversas para agentes |
| **Admin** | Setores, atendentes, SLA, tags, reclamações, transferências, tokens WhatsApp |

---

## Requisitos

- PHP **8.1+**
- Composer
- MySQL **5.7+** ou MariaDB
- Node.js **18+** (assets front-end)
- **ffmpeg** (opcional — gravação de áudio no navegador)
- Conta **Meta Developer** com WhatsApp Business configurado

---

## Instalação rápida

```bash
# 1. Clonar e entrar na pasta
git clone https://github.com/CrystianEdiezGaldino/whatsapp_crm_offcial.git
cd whatsapp_crm_offcial

# 2. Dependências
composer install
npm install

# 3. Ambiente
cp .env.example .env
php artisan key:generate

# 4. Banco — edite DB_* no .env antes
php artisan migrate
php artisan db:seed

# 5. Storage e assets
php artisan storage:link
npm run build

# 6. Subir servidor
php artisan serve
```

Acesse: **http://127.0.0.1:8000**

---

## Configuração

### Banco de dados (`.env`)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=SisZap
DB_USERNAME=root
DB_PASSWORD=sua_senha

APP_URL=http://127.0.0.1:8000
```

### WhatsApp Cloud API (`.env`)

```env
WA_PHONE_NUMBER_ID=          # ID do número na Meta
WA_ACCESS_TOKEN=             # Token de acesso (Graph API)
WA_VERIFY_TOKEN=             # Token secreto do webhook (você define)
WA_API_VERSION=v23.0
WA_BASE_URL=https://graph.facebook.com

# Opcional — áudio gravado no navegador (WebM → OGG)
# WA_FFMPEG_PATH=ffmpeg
# Windows: WA_FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
```

| Variável | Para que serve |
|----------|----------------|
| `WA_PHONE_NUMBER_ID` | Identifica o número WhatsApp Business |
| `WA_ACCESS_TOKEN` | Autentica envio/recebimento na Meta |
| `WA_VERIFY_TOKEN` | Valida o webhook (mesmo valor no painel Meta) |
| `WA_FFMPEG_PATH` | Converte áudio gravado no ERP |

**Tokens no painel:** em produção você também pode gerenciar tokens em **Admin → Tokens WhatsApp** (com renovação automática).

### Webhook na Meta

1. Acesse [developers.facebook.com](https://developers.facebook.com) → seu app → **WhatsApp → Configuration**.
2. **Callback URL:** `https://SEU-DOMINIO/api/webhook/whatsapp`
3. **Verify token:** igual ao `WA_VERIFY_TOKEN` do `.env`.
4. Assine o campo **`messages`**.

**Desenvolvimento local** — use [ngrok](https://ngrok.com):

```bash
ngrok http 8000
# Use a URL HTTPS gerada: https://xxxx.ngrok-free.app/api/webhook/whatsapp
```

### Modo teste (Meta)

No painel **WhatsApp → API Setup → To**, cadastre os celulares que podem **receber** mensagens do número de teste. Sem isso o envio retorna erro **#131030**. Receber mensagens via webhook **não** depende dessa lista.

---

## Primeiro acesso

Após `php artisan db:seed`:

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Admin | `admin@erp.com` | `password` |
| Agente | `ana@erp.com` | `password` |

> Altere as senhas em produção.

---

## Como funciona

```
Cliente WhatsApp
      │
      ▼
 Meta Cloud API ──POST──▶ /api/webhook/whatsapp  (mensagem salva no banco)
      ▲
      │
 Agente no SisZap ──POST──▶ /conversations/send  (envia via API)
      │
      ▼
 Tela de chat atualiza via poll (a cada ~5s)
```

1. Cliente manda mensagem → Meta envia webhook → sistema cria/atualiza conversa.
2. Se houver **fluxo ativo**, o bot envia menu e direciona para um setor.
3. Conversa entra na **fila** e é atribuída a um agente (manual ou automática).
4. Agente responde pelo painel **Atendimentos** → mensagem vai para o WhatsApp do cliente.

---

## Uso do dia a dia

### Agente

1. Faça login e abra **Atendimentos**.
2. Clique em uma conversa na lista (ou use **Assumir** para pegar da fila).
3. Digite a resposta ou use **Macros** (`/atalho`).
4. Envie texto, anexo ou grave áudio.
5. Ao finalizar, use **Resolver** com motivo de encerramento.

### Administrador

Além do que o agente faz, o admin configura:

| Menu | O que configurar |
|------|------------------|
| **Setores** | Áreas de atendimento (Suporte, Vendas, etc.) |
| **Atendentes** | Usuários, setor, capacidade |
| **Fluxos** | Bot de boas-vindas e menu de opções |
| **Distribuição** | Regras da fila e capacidade por agente |
| **SLA** | Prazos e alertas |
| **Tags** | Etiquetas nas conversas |
| **Tokens / Números WhatsApp** | Integração com a Meta |

### Fluxos (bot)

Em **Admin → Fluxos** você cria o atendimento automático:

- **Mensagem inicial** — saudação ao cliente
- **Opções de menu** — número + texto + setor destino
- **Mensagem final** — confirmação após a escolha

Gatilhos: nova conversa, comando ou manual.

---

## Comandos úteis

```bash
# Servidor local
php artisan serve

# Assets em desenvolvimento
npm run dev

# Limpar cache
php artisan config:clear
php artisan cache:clear

# Reprocessar fila de mensagens com falha
php artisan messages:retry

# Renovar token WhatsApp
php artisan whatsapp:refresh-token
```

### Produção — agendador

O sistema precisa do scheduler rodando para retry de mensagens e renovação de token:

```bash
# Crontab (Linux)
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

---

## Estrutura do projeto

```
app/
  Http/Controllers/     # Rotas web e webhook
  Services/             # WhatsApp, distribuição, fluxos
  Models/               # User, Conversation, Message, Sector...
resources/views/        # Telas Blade (chat, admin, dashboard)
routes/web.php          # Rotas principais
config/services.php     # Config WhatsApp
```

---

## Rotas importantes

| URL | Função |
|-----|--------|
| `/login` | Entrada no sistema |
| `/dashboard` | Painel geral |
| `/conversations` | Chat de atendimento |
| `/admin/flows` | Gestão de fluxos (bot) |
| `GET/POST /api/webhook/whatsapp` | Webhook Meta |
| `/health` | Status do sistema |

---

## Documentação detalhada

| Arquivo | Conteúdo |
|---------|----------|
| [AJUDA_WHATSAPP.md](AJUDA_WHATSAPP.md) | Webhook, envio, mídia, erros comuns |
| [PROJECT_FLOW.md](PROJECT_FLOW.md) | Regras de negócio e arquitetura |
| [docs/FLOW_BUILDER_DESIGN.md](docs/FLOW_BUILDER_DESIGN.md) | Design do construtor de fluxos |
| [WEBHOOK_TROUBLESHOOTING.md](WEBHOOK_TROUBLESHOOTING.md) | Problemas no webhook |

---

## Problemas comuns

| Problema | Solução |
|----------|---------|
| Webhook não verifica | `WA_VERIFY_TOKEN` deve ser idêntico no `.env` e na Meta |
| Mensagem não envia (#131030) | Número não está na lista de teste da Meta |
| Chat não atualiza | Verifique se o servidor está rodando e o poll retorna 200 |
| Áudio não envia | Instale ffmpeg e configure `WA_FFMPEG_PATH` |
| Token expirado | Use **Admin → Tokens WhatsApp** ou `whatsapp:refresh-token` |

---

## Stack

- **Laravel 10** — back-end
- **Tailwind CSS** — interface
- **MySQL** — banco de dados
- **WhatsApp Cloud API** — mensageria

---

## Licença

MIT — uso interno Santa Mônica Clube de Campo.
