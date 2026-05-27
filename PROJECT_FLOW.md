# SMCC WhatsApp - Fluxo do Projeto e Regras de Negócio

**Data:** Maio 2026  
**Versão:** 1.0  
**Sistema:** OmniChannel ERP - WhatsApp Service

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Módulos Principais](#módulos-principais)
4. [Fluxos de Negócio](#fluxos-de-negócio)
5. [Regras de Negócio](#regras-de-negócio)
6. [Modelos de Dados](#modelos-de-dados)
7. [Endpoints API](#endpoints-api)
8. [Integrações Externas](#integrações-externas)
9. [Segurança e Conformidade](#segurança-e-conformidade)

---

## 🎯 Visão Geral

**SMCC WhatsApp** é um sistema de atendimento omnichannel integrado ao WhatsApp Cloud API, projetado para gerenciar conversas, contatos e distribuição de leads. O sistema é de **uso exclusivo** (single-tenant) para a empresa Santa Monica.

### Objetivos Principais
- ✅ Integração full com WhatsApp Cloud API
- ✅ Gestão centralizada de conversas
- ✅ Distribuição automática/manual de leads por setor
- ✅ Auditoria completa de todas as ações
- ✅ Escalabilidade para 200+ conexões simultâneas
- ✅ Conformidade com políticas Meta de templates e mensagens

---

## 🏗️ Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                   WhatsApp Cloud API                        │
│                    (Meta Business)                          │
└────────────────┬────────────────────────────────────────────┘
                 │ Webhook (POST /webhook/handle)
                 │ Polling (GET /api/conversations/{id}/poll)
                 │
┌────────────────▼─────────────────────────────────────────────┐
│              Laravel Application Server                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Controllers (Web Routes)                             │   │
│  │ - DashboardController, ConversationController       │   │
│  │ - ContactController, MacroController                │   │
│  │ - Admin/* (Sectors, Agents, Distribution)           │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Services (Business Logic)                            │   │
│  │ - WhatsAppService                                    │   │
│  │ - DistributionService                               │   │
│  │ - PhoneValidationService                            │   │
│  │ - Builders (Template, Carousel, Contact, OTP, etc)  │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Models (Data Layer)                                  │   │
│  │ - User, Sector, Contact, Conversation               │   │
│  │ - Message, ConversationClaim, AuditLog              │   │
│  └──────────────────────────────────────────────────────┘   │
└────────┬───────────────────────────────────────────────────┘
         │
    ┌────▼────┬────────────┬──────────────┐
    │          │            │              │
┌───▼──┐  ┌──▼───┐  ┌─────▼──┐  ┌──────▼──┐
│ MySQL│  │Redis │  │ Storage│  │ Session│
│      │  │ (SSE)│  │ (Media)│  │ (Sanctum)
└──────┘  └──────┘  └────────┘  └─────────┘
```

### Componentes Principais

| Camada | Componentes | Responsabilidades |
|--------|-------------|------------------|
| **API** | HTTP Routes, Webhooks | Receber requisições, validar, rotear |
| **Controllers** | Web, API, Admin | Lógica de requisição, views |
| **Services** | Business Logic | Regras de negócio, integrações |
| **Models** | Eloquent ORM | Persistência, relacionamentos |
| **Queue** | Redis, Jobs | Processamento assíncrono |
| **Storage** | S3, Local | Mídia, arquivos, backups |

---

## 📦 Módulos Principais

### 1. **Dashboard**
- Visão geral de estatísticas
- Conversas por status (new, in_attendance, resolved)
- Agentes online/offline
- Ocupação em tempo real
- Métricas de resposta

### 2. **Conversas (Chats)**
- Listar todas as conversas
- Visualizar histórico da conversa
- Enviar mensagens (texto, áudio, imagem, arquivo, media, contact, reaction, carousel, OTP)
- Reivindicar (claim) conversa
- Reassignar para outro agente
- Resolver conversa
- Bloqueio de contato

### 3. **Contatos**
- CRUD de contatos
- Atribuição de contatos a agentes
- Histórico de interações
- Status de bloqueio

### 4. **Macros**
- Templates de resposta rápida
- Gerenciamento de macros com arquivos em PDF/imagem
- Reordenação de arquivos

### 5. **Admin > Setores**
- CRUD de setores
- Atribuição de opções de teclado (0-9)
- Mensagens de boas-vindas por setor
- Ativar/desativar setores
- Visualizar agentes por setor

### 6. **Admin > Atendentes**
- CRUD de atendentes
- Atribuição a setores
- Definição de cargo (agent, supervisor, admin)
- Capacidade máxima de conversas
- Reset de senha
- Ativar/desativar agente
- Exportar CSV

### 7. **Admin > Distribuição**
- Configurar modo (manual vs automático)
- Definir overflow (queue vs next_agent)
- Visualizar carga por agente
- Monitor de fila de espera
- Histórico de distribuição automática

### 8. **Auditoria**
- Timeline de eventos por conversa
- Histórico de atividades por agente
- Log de todas as ações do sistema
- Rastreabilidade completa

### 9. **Relatórios**
- Dashboard de métricas
- Relatório de conversas
- Exportação de dados

---

## 🔄 Fluxos de Negócio

### **Fluxo 1: Recepção de Mensagem WhatsApp**

```
Webhook WhatsApp → Verificação de assinatura
    ↓
POST /webhook/handle
    ↓
WhatsAppService::handleInboundMessage()
    ├─ Normalizando número de telefone (BR format)
    ├─ Encontrando ou criando Contato
    ├─ Encontrando ou criando Conversa (status: 'new')
    ├─ Salvando Mensagem (texto, mídia, tipo)
    ├─ Processando mídia (URL, download, validação SSL)
    ├─ Registrando em AuditLog
    └─ DistributionService::assign($conversation)
        ├─ Se modo = 'manual': sem atribuição (agente reclama)
        └─ Se modo = 'automático': 
            ├─ Buscar agentes ativos com capacidade
            ├─ Aplicar round-robin
            ├─ Atribuir automaticamente (claim)
            ├─ Atualizar status para 'in_attendance'
            └─ Disparar evento ConversationStatusChanged
    ↓
Server-Sent Events (SSE)
    ├─ Atualiza interface em tempo real
    ├─ Notifica agentes online
    └─ Mostra fila de espera (se overflow=queue)
```

### **Fluxo 2: Agente Reclamar Conversa (Manual)**

```
Agente clica "Reivindicar"
    ↓
POST /conversations/{conversation}/claim
    ↓
ConversationClaimController::claim()
    ├─ Verificar se conversa está disponível (status='new')
    ├─ Criar ConversationClaim (user_id, claimed_at)
    ├─ Atualizar Conversation (status='in_attendance', claimed_by=user_id)
    ├─ Registrar em AuditLog
    └─ Disparar ConversationStatusChanged
    ↓
SSE atualiza dashboard de todos os agentes
    └─ Remove conversa da fila
```

### **Fluxo 3: Envio de Mensagem por Agente**

```
Agente escreve mensagem + opcional (áudio/imagem/arquivo)
    ↓
POST /conversations/send
    ↓
ConversationController::sendMessage()
    ├─ Validar autorização (agente ou admin)
    ├─ Validar que conversa está atribuída ao agente
    ├─ Processar conteúdo (texto, mídia, tipo)
    ├─ Normalizando número de telefone
    └─ WhatsAppService::send{Type}()
        ├─ Validar template (se aplicável)
        ├─ Formatar payload para Meta API
        ├─ POST https://graph.instagram.com/v18.0/...
        ├─ Retry exponencial (3x) em caso de erro
        ├─ Salvar Message (status: 'pending')
        ├─ Registrar em AuditLog
        └─ Retornar message_id (WAMID)
    ↓
Webhook WhatsApp notifica (status_callback)
    ├─ Atualizar Message (status: 'sent' → 'delivered' → 'read')
    └─ SSE atualiza UI
```

### **Fluxo 4: Resolver Conversa**

```
Agente clica "Resolver"
    ↓
PATCH /conversations/{conversation}/resolve
    ↓
ConversationController::resolve()
    ├─ Verificar autorização
    ├─ Atualizar status para 'resolved'
    ├─ Registrar tempo de resolução
    ├─ Registrar em AuditLog
    └─ Disparar ConversationStatusChanged
    ↓
Conversa desaparece da lista do agente
    └─ Disponibiliza nova conversa
```

### **Fluxo 5: Bloqueio/Desbloqueio de Contato**

```
Agente clica "Bloquear Contato"
    ↓
WhatsAppBlockingService::blockUser(phone)
    ├─ Validar formação do número
    ├─ PUT /v18.0/{phone_number_id}/blocked_contacts
    ├─ Requer 24h desde última mensagem (regra Meta)
    ├─ Atualizar Contact.is_blocked = true
    ├─ Registrar em AuditLog (action: 'contact_blocked')
    └─ Retornar status
    ↓
Contato não pode mais enviar mensagens
    └─ Mensagens são descartadas automaticamente
```

### **Fluxo 6: Pré-Atendimento IVR (Futuro)**

```
Cliente inicia chat
    ↓
Sistema envia menu IVR:
"Bem-vindo! Digite:
 1 - Secretaria
 2 - Financeiro
 3 - Atendimento"
    ↓
Cliente digita opção (1, 2, 3, etc)
    ↓
Webhook captura entrada
    ├─ Mapear opção para Sector (keyboard_option)
    ├─ Criar Conversa com setor pré-selecionado
    ├─ Enviar greeting_message do setor
    └─ DistributionService::assign() → atribui a agente do setor
    ↓
Agente recebe conversa já no setor correto
```

---

## ⚙️ Regras de Negócio

### **Conversas**

| Regra | Descrição | Validação |
|-------|-----------|-----------|
| **Status Progression** | new → in_attendance → resolved | Controller, AuditLog |
| **Atribuição Manual** | Agente reclama apenas se status='new' | ConversationClaimController |
| **Atribuição Automática** | Sistema escolhe agente com menor carga | DistributionService |
| **Round-Robin** | Próximo agente alternado sequencialmente | AgentCapacity.round_robin_position |
| **Capacidade Máxima** | Agente não recebe > max_conversations | AgentCapacity.hasCapacity() |
| **Overflow** | Se lotado: próximo_agente OR fila | DistributionSetting.overflow_action |
| **Reassignação** | Apenas supervisor/admin podem reassignar | ConversationClaimController::reassign |
| **Resolução** | Apenas agente ou supervisor podem resolver | ConversationController::resolve |

### **Agentes**

| Regra | Descrição | Validação |
|-------|-----------|-----------|
| **Roles** | agent, supervisor, admin | User.role |
| **Setor Obrigatório** | agents/supervisors devem ter sector_id | AgentController::validate |
| **Ativo** | is_active=false não recebe novas conversas | DistributionService::autoAssign |
| **Capacidade Padrão** | 10 conversas simultâneas | AgentCapacity.create() |
| **Senha Mínima** | 8 caracteres, confirmada | AgentController::validate |
| **Não Deletar Admin** | Proteger eliminar role='admin' | AgentController::destroy |
| **Não Deletar com Ativas** | Bloquear delete se conversa ativa | AgentController::destroy |

### **Setores**

| Regra | Descrição | Validação |
|-------|-----------|-----------|
| **Opção Única** | 0-9, uma por setor (IVR) | Sector.keyboard_option unique |
| **Não Deletar com Agentes** | Bloquear delete se sector_id referenciado | SectorController::destroy |
| **Mensagem Padrão** | Se vazio, usa formato genérico | Sector::getGreetingOrDefault() |
| **Ativo/Inativo** | Inativo não aparece no IVR | SectorController::create |

### **Mensagens**

| Regra | Descrição | Validação |
|-------|-----------|-----------|
| **Tipos Suportados** | text, image, document, audio, video, template, contact, reaction, carousel, otp | Message.type |
| **Templates Validados** | utility, marketing, authentication | WhatsAppTemplateBuilder::validate |
| **Placeholder Matching** | Dinâmicos devem coincidir com valores | WhatsAppTemplateBuilder::build |
| **Limite de Caracteres** | Textos max 4096, corpos max 160 | Controller validate |
| **Status de Entrega** | pending → sent → delivered → read | Webhook status_callback |
| **Retry Automático** | 3x exponencial (1s, 2s, 4s) | WhatsAppService::send |
| **WAMID Rastreamento** | Cada mensagem tem ID único Meta | Message.external_id |

### **Bloqueio de Contato**

| Regra | Descrição | Validação |
|-------|-----------|-----------|
| **24h Obrigatório** | Só bloqueia se mensagem > 24h atrás | WhatsAppBlockingService::blockUser |
| **Erro 551** | Meta retorna se violado | API Error Handling |
| **Permanente** | Bloqueado até desbloqueio explícito | Contact.is_blocked |
| **Webhook Descarta** | Mensagens de bloqueado ignoradas | handleInboundMessage() |

### **Auditoria**

| Regra | Descrição | Ações Registradas |
|-------|-----------|------------------|
| **Log Completo** | Toda ação crítica registrada | created, updated, deleted, claimed, assigned, resolved, sent, blocked |
| **Sem Logs Sensíveis** | Senhas/OTP nunca no log | Helper::sanitizeForLog() |
| **Timeline por Conversa** | Histórico ordenado por data | AuditLog::where('conversation_id') |
| **Rastreabilidade** | Quem, quando, o quê, resultado | user_id, action, created_at, details |

### **Segurança**

| Regra | Descrição | Implementação |
|-------|-----------|---------------|
| **Autenticação** | Login obrigatório | Auth::check() middleware |
| **Admin Only** | Rotas admin protegidas | ensure_is_admin middleware |
| **CSRF Protection** | Form tokens validados | csrf_token, @csrf |
| **Webhook Signature** | Meta signature verificada | WhatsAppService::verifyWebhook |
| **Phone Normalization** | Validar + formatar telefones | PhoneValidationService |
| **SSL Verify** | HTTPS forçado em APIs | verify: true guzzle |
| **Rate Limiting** | Controle via Redis | Redis counters (futuro) |
| **Session Sanctum** | API tokens seguros | Laravel Sanctum |

---

## 📊 Modelos de Dados

### **Tabelas Principais**

```sql
-- Usuários (Agentes, Supervisores, Admins)
users
├── id (PK)
├── name
├── email (unique)
├── password (hashed)
├── role: enum('agent', 'supervisor', 'admin')
├── sector_id (FK) → sectors.id, nullable
├── status: enum('online', 'offline')
├── is_active: boolean
├── notes
├── avatar (opcional)
├── created_at, updated_at

-- Setores (pré-atendimento IVR)
sectors
├── id (PK)
├── name (unique)
├── description
├── keyboard_option: integer (0-9, unique)
├── greeting_message
├── is_active: boolean
├── order
├── created_at, updated_at

-- Capacidade de Agentes
agent_capacities
├── id (PK)
├── user_id (FK) → users.id, unique
├── max_conversations: integer (default 10)
├── is_active: boolean
├── round_robin_position: integer (default 0)
├── created_at, updated_at

-- Contatos (clientes)
contacts
├── id (PK)
├── phone (unique)
├── name
├── email
├── avatar
├── assigned_to (FK) → users.id, nullable
├── is_blocked: boolean (default false)
├── blocked_at: timestamp, nullable
├── block_reason
├── created_at, updated_at

-- Conversas
conversations
├── id (PK)
├── contact_id (FK) → contacts.id
├── sector_id (FK) → sectors.id, nullable (pré-atendimento)
├── claimed_by (FK) → users.id, nullable
├── status: enum('new', 'in_attendance', 'resolved')
├── resolved_at: timestamp, nullable
├── created_at, updated_at

-- Reclames de Conversa (histórico de claim)
conversation_claims
├── id (PK)
├── conversation_id (FK) → conversations.id
├── user_id (FK) → users.id
├── claimed_at: timestamp
├── released_at: timestamp, nullable
├── reassigned_to_id (FK) → users.id, nullable

-- Mensagens
messages
├── id (PK)
├── conversation_id (FK) → conversations.id
├── sender_id (FK) → users.id, nullable (null = contato)
├── body: text
├── type: enum('text', 'image', 'document', 'audio', 'video', 'template', 'contact', 'reaction', 'carousel', 'otp')
├── external_id (WAMID): string, nullable
├── status: enum('pending', 'sent', 'delivered', 'read', 'failed')
├── media_url
├── media_type
├── template_name (se template)
├── created_at, updated_at

-- Mídia (anexos)
media
├── id (PK)
├── message_id (FK) → messages.id
├── file_path
├── file_size
├── mime_type
├── url
├── created_at, updated_at

-- Macros (templates de resposta)
macros
├── id (PK)
├── user_id (FK) → users.id
├── name
├── body: text
├── category
├── created_at, updated_at

-- Macro Files (PDFs, imagens da macro)
macro_files
├── id (PK)
├── macro_id (FK) → macros.id
├── file_path
├── file_name
├── file_type
├── order
├── created_at, updated_at

-- Logs de Auditoria
audit_logs
├── id (PK)
├── user_id (FK) → users.id, nullable
├── conversation_id (FK) → conversations.id, nullable
├── action: string ('created', 'updated', 'deleted', 'claimed', 'assigned', etc)
├── description
├── old_values: json, nullable
├── new_values: json, nullable
├── details: json, nullable
├── ip_address
├── user_agent
├── created_at

-- Configurações de Distribuição
distribution_settings
├── id (PK, sempre 1)
├── mode: enum('manual', 'automatic')
├── overflow_action: enum('next_agent', 'queue')
├── created_at, updated_at

-- Webhook Logs (rastreamento)
webhook_logs
├── id (PK)
├── type: string ('message', 'status_update', 'delivery_callback')
├── status: enum('success', 'failed', 'processing')
├── payload: json
├── response: json
├── processing_time_ms
├── created_at, updated_at
```

### **Relacionamentos**

```
User (Agent)
├── 1→N conversations (claimed_by)
├── 1→N messages (sender_id)
├── 1→N audit_logs
├── 1→1 agent_capacity
└── N←1 sectors (sector_id)

Sector
├── 1→N users
├── 1→N conversations
└── Keyboard Options: 0-9 para IVR

Contact
├── 1→N conversations
├── 0←1 assigned_to (User)
└── is_blocked status

Conversation
├── N←1 contact
├── N←1 claimed_by (User)
├── N←1 sector
├── 1→N messages
└── 1→N conversation_claims

Message
├── N←1 conversation
├── N←1 sender_id (User, nullable)
└── external_id (Meta WAMID)
```

---

## 🔌 Endpoints API

### **Autenticação**

```
POST   /login                    → Formulário login
POST   /login                    → Processar login
POST   /logout                   → Sair
```

### **Dashboard**

```
GET    /dashboard                → Página principal (auth)
GET    /health                   → Status do sistema
GET    /health/api               → Status da API
GET    /health/dashboard         → Métricas (auth)
GET    /health/webhooks          → Logs de webhook (auth)
```

### **Conversas**

```
GET    /conversations            → Listar conversas (new, in_attendance)
POST   /conversations/start      → Iniciar nova conversa
GET    /conversations/{id}       → Detalhes
POST   /conversations/send       → Enviar mensagem
GET    /conversations/{id}/poll  → Polling SSE
PATCH  /conversations/{id}/assign   → Atribuir agente (admin)
PATCH  /conversations/{id}/resolve  → Resolver conversa
GET    /conversations/{id}/history  → Histórico completo
GET    /conversations/{id}/history-view → Ver histórico
GET    /conversations/{id}/claim-history → Claims da conversa
GET    /conversations/{id}/audit/timeline → Timeline de auditoria
POST   /conversations/{id}/claim → Reivindicar conversa
DELETE /conversations/{id}/claim → Liberar conversa (release)
PATCH  /conversations/{id}/reassign → Reassignar para outro agente
```

### **Contatos**

```
GET    /contacts                 → Listar contatos (paginated)
POST   /contacts                 → Criar contato
GET    /contacts/{id}            → Detalhes
PUT    /contacts/{id}            → Editar contato
DELETE /contacts/{id}            → Deletar contato
```

### **Macros**

```
GET    /macros                   → Listar macros do agente
POST   /macros                   → Criar macro
GET    /macros/{id}              → Detalhes
PUT    /macros/{id}              → Editar macro
DELETE /macros/{id}              → Deletar macro
POST   /macros/{id}/files        → Upload arquivo da macro
DELETE /macros/{id}/files/{file} → Deletar arquivo
PATCH  /macros/{id}/files/{file}/reorder → Reordenar
GET    /macros/{id}/preview      → Preview da macro
```

### **Admin - Setores**

```
GET    /admin/sectors            → Listar setores
POST   /admin/sectors            → Criar setor
GET    /admin/sectors/{id}       → Detalhes
PUT    /admin/sectors/{id}       → Editar setor
DELETE /admin/sectors/{id}       → Deletar setor
PATCH  /admin/sectors/{id}/toggle-active → Ativar/Desativar
```

### **Admin - Atendentes**

```
GET    /admin/agents             → Listar atendentes
POST   /admin/agents             → Criar atendente
GET    /admin/agents/{id}        → Detalhes
PUT    /admin/agents/{id}        → Editar atendente
DELETE /admin/agents/{id}        → Deletar atendente
POST   /admin/agents/{id}/reset-password → Reset senha
PATCH  /admin/agents/{id}/toggle-active  → Ativar/Desativar
GET    /admin/agents/export/csv  → Export CSV
```

### **Admin - Distribuição**

```
GET    /admin/distribution       → Página de configuração
POST   /admin/distribution/settings    → Salvar modo e overflow
PATCH  /admin/distribution/agents/{id}/capacity → Atualizar capacidade
GET    /admin/distribution/metrics     → JSON de métricas em tempo real
```

### **Auditoria**

```
GET    /audit/conversations      → Histórico de conversas (auditoria)
GET    /audit/activity           → Atividades por agente
GET    /conversations/{id}/audit/timeline → Timeline da conversa
```

### **Relatórios**

```
GET    /reports/dashboard-data   → Dados do dashboard
GET    /reports/conversations    → Relatório de conversas
GET    /reports/export-conversations → Export conversas (CSV/XLSX)
```

### **Webhooks**

```
POST   /webhook/handle           → Webhook do WhatsApp (verificação + mensagens)
GET    /webhook/debug            → Debug webhook (desenvolvimento)
POST   /webhook/debug            → Debug webhook (POST)
```

### **Documentação**

```
GET    /docs                     → Página de documentação
GET    /docs/components/{component} → Componentes específicos
```

### **API Interna (SSE / Tempo Real)**

```
GET    /api/agents               → Lista de agentes (JSON)
GET    /api/sse/conversations    → Server-Sent Events (real-time)
GET    /api/sse/metrics          → Métricas em tempo real
```

---

## 🔗 Integrações Externas

### **WhatsApp Cloud API (Meta)**

**Base URL:** `https://graph.instagram.com/v18.0`

#### Endpoints Utilizados

```
POST   /{phone_number_id}/messages
       └─ Enviar mensagens (texto, mídia, template, etc)
       └─ Corpo: { messaging_product, recipient_type, to, type, ... }
       └─ Response: { messages: [{ id: WAMID }] }

POST   /{phone_number_id}/register_phone
       └─ Registrar número para webhooks
       └─ Uma única vez, obtém prefixo de webhook

POST   /{app_id}/subscriptions
       └─ Inscrever em webhooks (messages, message_status)
       └─ Recebe POST em /webhook/handle

GET    /{phone_number_id}/phone_numbers
       └─ Verificar número registrado

PUT    /{phone_number_id}/blocked_contacts
       └─ Bloquear/desbloquear contato
       └─ Requer 24h desde última mensagem

GET    /{phone_number_id}/messages/{wamid}
       └─ Consultar status de mensagem por WAMID (opcional)
```

#### Webhook Payload (Inbound)

```json
{
  "entry": [{
    "changes": [{
      "value": {
        "messaging_product": "whatsapp",
        "messages": [{
          "from": "5585987654321",
          "id": "wamid.xxx",
          "timestamp": "1234567890",
          "type": "text|image|document|audio|video",
          "text": { "body": "..." },
          "image|document|audio|video": { "id": "...", "mime_type": "...", "sha256": "..." },
          "contacts": [{...}],
          "reactions": [{ "emoji": "👍", "message_id": "wamid.xxx" }]
        }],
        "contacts": [{
          "profile": { "name": "João Silva" },
          "wa_id": "5585987654321"
        }]
      }
    }]
  }]
}
```

#### Webhook Payload (Status Callback)

```json
{
  "entry": [{
    "changes": [{
      "value": {
        "statuses": [{
          "id": "wamid.xxx",
          "status": "sent|delivered|read|failed",
          "timestamp": "1234567890",
          "recipient_id": "5585987654321",
          "errors": [{ "code": "...", "title": "...", "message": "..." }]
        }]
      }
    }]
  }]
}
```

### **Validação de Webhook Meta**

```php
$token = hash_hmac('sha256', $body, $app_secret);
if ($token !== $signature) {
    return response('Forbidden', 403);
}
```

### **Erros Comuns Meta**

| Código | Mensagem | Causa | Solução |
|--------|----------|-------|---------|
| 131049 | Invalid phone number | Formato incorreto | Usar PhoneValidationService |
| 100 | Unknown error | API instável | Retry exponencial |
| 551 | Message blocked | Bloqueio ativo | Respeitar 24h para desbloquear |
| 131026 | Rate limit exceeded | Muitas requisições | Implementar queue |

---

## 🔐 Segurança e Conformidade

### **Conformidade Meta**

#### Templates

- ✅ **Utility Templates** - Notificações pós-transação
  - Blocklist: "discount", "offer", "sale", "limited time", etc
  - Permitir apenas templates aprovados
  - Usar placeholders {{1}}, {{2}}, etc

- ✅ **Marketing Templates** - Promoções, atualizações
  - Requer opt-in do usuário
  - Limite: 1 update/dia, 10/mês

- ✅ **Authentication Templates** - OTP, verificação
  - Formato: "[CODE] is your verification code"
  - CRÍTICO: Apenas template, nunca SMS freeform
  - Suportar iOS 26+ native keyboard

#### Mensagens

- ❌ **Bloqueio de palavras-chave** em templates utility
- ❌ **Envio sem consentimento** para marketing
- ❌ **OTP por SMS freeform** (apenas template)
- ❌ **Mensagens duplicadas** em curto período
- ✅ **Conta com domínio verificado** recomendado
- ✅ **HTTPS obrigatório** para webhooks

#### Bloqueio de Usuários

- Regra Meta: Respeitar 24h desde última mensagem
- Erro 551 se violado: "User Blocked"
- Após bloqueio: ignorar todas as mensagens
- Desbloqueio: disponível sempre

### **Segurança da Aplicação**

#### Autenticação & Autorização

```
Guard: session (web)
Middleware: auth, auth.admin, ensure_is_admin
Roles: agent (conversa própria), supervisor (equipe), admin (tudo)
```

#### Dados Sensíveis

```
Senhas: Hash com bcrypt, never log
OTPs: Nunca log, apenas hash se auditado
Tokens Meta: .env, never repo
Session: Sanctum tokens, HTTPS only
```

#### Validação & Sanitização

```
Phone: PhoneValidationService (format + country)
URLs: filter_var() para media
Emails: filter_var() com FILTER_VALIDATE_EMAIL
Text: strip_tags() se necessário
Templates: validar placeholders vs argumentos
```

#### Rate Limiting (Futuro)

```php
// Implementar com Redis
$rate = Redis::incr("api:user:{$id}:minute");
if ($rate > 100) abort(429); // Too Many Requests
Redis::expire("api:user:{$id}:minute", 60);
```

#### CORS (se necessário)

```php
// Não aplicável (single-tenant, mesmo domínio)
// Mas validar origin se integrar com terceiros
```

---

## 📈 Escalabilidade

### **Arquitetura para 200+ Conexões Simultâneas**

#### Redis

```
- SSE: Redis Pub/Sub para broadcast
- Cache: Queries frequentes (agents online, sectors)
- Queue: Jobs assíncrono (envio, webhook)
- Sessions: Sessões distribuídas
```

#### Database

```
Índices críticos:
- conversations (status, claimed_by, created_at)
- messages (conversation_id, created_at)
- contacts (phone, is_blocked)
- users (role, sector_id, is_active)
- audit_logs (conversation_id, action, created_at)

Partição (futura):
- audit_logs por mês
- messages por conversation_id
```

#### Workers

```
Queue jobs:
- SendWhatsAppMessage (priority: high)
- ProcessWebhook (priority: high)
- ExportCSV (priority: low)
- GenerateReports (priority: low)
```

---

## 🔧 Configuração & Deploy

### **Arquivo .env Essencial**

```env
APP_NAME="SMCC WhatsApp"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=smcc_whatsapp
DB_USERNAME=root
DB_PASSWORD=***

REDIS_HOST=localhost
REDIS_PORT=6379

WHATSAPP_BUSINESS_ACCOUNT_ID=xxx
WHATSAPP_PHONE_NUMBER_ID=xxx
WHATSAPP_PHONE_NUMBER=555185987654321
WHATSAPP_API_TOKEN=EAAxx...
WHATSAPP_APP_SECRET=xxx

WEBHOOK_VERIFY_TOKEN=random_string_123

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=file
```

### **Comandos Iniciais**

```bash
# Setup
php artisan migrate
php artisan db:seed
php artisan storage:link

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue
php artisan queue:work redis --queue=default,high --tries=3

# Teste
php artisan serve
```

---

## 📝 Histórico de Versões

| Versão | Data | Principais Mudanças |
|--------|------|-------------------|
| 1.0 | Maio 2026 | MVP com WhatsApp, distribuição, admin, auditoria |
| 0.9 | Maio 2026 | Integração completa WhatsApp, templates, blocking |
| 0.8 | Maio 2026 | Setores e IVR foundation |
| 0.7 | Maio 2026 | Distribuição automática com round-robin |
| 0.6 | Maio 2026 | Media handling (áudio, imagem, PDF) |
| 0.5 | Maio 2026 | Webhooks WhatsApp e SSE |

---

## 📞 Suporte

**Contato:** ti@santamonica.rec.br  
**Documentação:** `/docs`  
**Status:** `/health`

---

**Última atualização:** Maio 2026  
**Sistema:** OmniChannel ERP - WhatsApp Service  
**Ambiente:** Production-Ready
