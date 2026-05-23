# 📋 Fluxo Completo do Sistema de Atendimento WhatsApp

## Visão Geral

Sistema OmniChannel ERP para gerenciamento de atendimento via WhatsApp, com automação de distribuição de leads, controle de capacidade de agentes e histórico de interações em tempo real.

---

## 🏗️ Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    WhatsApp Business API                     │
│         (Webhook para mensagens e atualizações)              │
└───────────────────────┬─────────────────────────────────────┘
                        │ Webhook Inbound
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Laravel Application Server                      │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │  WebhookCtrl    │  │ ConversCtrl  │  │ AgentCtrl    │   │
│  └────────┬────────┘  └──────┬───────┘  └──────┬───────┘   │
│           │                  │                 │             │
│  ┌────────▼────────┐  ┌──────▼───────┐  ┌──────▼───────┐   │
│  │ WhatsApp        │  │ Distribution │  │ Agent        │   │
│  │ Service         │  │ Service      │  │ Management   │   │
│  └────────┬────────┘  └──────┬───────┘  └──────┬───────┘   │
│           │                  │                 │             │
│  ┌────────▼──────────────────▼──────────────────▼────────┐  │
│  │              Database (MySQL)                         │  │
│  │  Users, Conversations, Messages, Contacts, Macros   │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Redis Pub/Sub (Broadcasting em Tempo Real)          │  │
│  │  - Mensagens: users.{id}                              │  │
│  │  - Conversas: conversations.{id}                      │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
└──────────────────────┬───────────────────────────────────────┘
                       │ SSE (Server-Sent Events)
                       ▼
        ┌──────────────────────────────┐
        │   Cliente Web (Frontend)     │
        │  - Dashboard                 │
        │  - Chat Interface            │
        │  - Admin Panel               │
        └──────────────────────────────┘
```

---

## 📱 1. FLUXO DE ATENDIMENTO (Cliente → Agente)

### 1.1 Cliente Envia Mensagem (Novo Contato)

```
┌─────────────────────────────────────────────────────────────┐
│ CLIENTE ENVIA MENSAGEM VIA WHATSAPP                         │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ WEBHOOK RECEBE NOTIFICAÇÃO DA MENSAGEM                      │
│ - URL: /webhook (POST)                                      │
│ - Valida token de verificação                               │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ WHATSAPPSERVICE::handleInboundMessage()                      │
│ ✓ Normaliza número de telefone                              │
│ ✓ Busca ou cria Contact                                     │
│ ✓ Cria/Atualiza Conversation (status=new)                   │
│ ✓ Cria Message com mídia (se houver)                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ DISTRIBUTIONSERVICE::assign()                                │
├─────────────────────────────────────────────────────────────┤
│ SE modo = MANUAL:                                           │
│   ⏳ Conversa aguarda agente reivindicar (status=new)       │
│                                                              │
│ SE modo = AUTOMATIC:                                        │
│   🤖 Sistema busca próximo agente disponível (round-robin)  │
│   - Verifica capacidade (conversas ativas < máximo)         │
│   - Busca agentes ativos ordenados por round_robin_position│
│                                                              │
│   SE encontrou agente disponível:                           │
│     ✓ Assign para o agente                                  │
│     ✓ Status = in_attendance                                │
│                                                              │
│   SE sem agentes disponíveis:                               │
│     - overflow = queue: status permanece new               │
│     - overflow = next_agent: assign ao menos carregado      │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ EVENTOS DISPARADOS                                          │
│ - ConversationStatusChanged → Redis Pub/Sub                 │
│ - Audit Log: action=auto_assigned                           │
│ - SSE atualiza frontend em tempo real                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ AGENTE RECEBE NOTIFICAÇÃO (SE ATRIBUÍDO AUTOMATICAMENTE)    │
│ ✓ Via SSE atualiza lista de conversas                       │
│ ✓ Conversa já está atribuída a ele                          │
│ ✓ Pode começar a responder imediatamente                    │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Agente Reivindica Conversa (Modo Manual)

```
┌─────────────────────────────────────────────────────────────┐
│ AGENTE VISUALIZA CONVERSA NA LISTA (status=new)             │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ AGENTE CLICA "ASSUMIR CONVERSA"                             │
│ POST /conversations/{id}/claim                              │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CONVERSATIONCLAIMCONTROLLER::claim()                         │
│ ✓ Cria ConversationClaim record                             │
│ ✓ Atualiza status para in_attendance                        │
│ ✓ Define claimed_by = agent_id                             │
│ ✓ Registra em audit_logs                                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ DISPARA ConversationStatusChanged EVENT                      │
│ - Broadcast via Redis                                       │
│ - SSE notifica todos os clientes                            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CONVERSA MOVE PARA "MINHA FILA" DO AGENTE                   │
│ ✓ Status agora é "in_attendance"                            │
│ ✓ Agente pode ler mensagens anteriores                      │
│ ✓ Agente pode enviar respostas                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.3 Agente Responde Cliente

```
┌─────────────────────────────────────────────────────────────┐
│ AGENTE DIGITA E ENVIA MENSAGEM                              │
│ - Texto, imagem, áudio, ou documento                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CONVERSATIONCONTROLLER::sendMessage()                        │
│ ✓ Valida permissões (agente reivindicou conversa)           │
│ ✓ Cria Message record (status=pending)                      │
│ ✓ Faz upload de mídia (se houver)                           │
│ ✓ Atualiza last_message_at da conversa                      │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ WHATSAPPSERVICE::sendMessage()                               │
│ ✓ Formata payload para WhatsApp API                         │
│ ✓ Envia via curl para https://graph.instagram.com/v18.0     │
│ ✓ Normaliza número do cliente                               │
│ ✓ Trata erros (SSL, timeout, permissões)                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ WHATSAPP API RESPONDE COM wa_message_id                      │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ SALVA wa_message_id NA MESSAGE RECORD                        │
│ - Status: pending → sent                                    │
│ - Audit log: message sent                                   │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CLIENTE RECEBE MENSAGEM NO WHATSAPP                         │
│ ✓ Status automático: sent → delivered → read                │
│ ✓ Webhook atualiza message.status em tempo real             │
└─────────────────────────────────────────────────────────────┘
```

### 1.4 Atualização de Status de Mensagem

```
┌─────────────────────────────────────────────────────────────┐
│ WHATSAPP WEBHOOK: MENSAGEM FOI ENTREGUE OU LIDA             │
│ - type: message_template_status_update                      │
│ - status: delivered | read                                  │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ WHATSAPPSERVICE::processStatusUpdate()                       │
│ ✓ Busca Message pelo wa_message_id                          │
│ ✓ Atualiza status: delivered, read, failed                  │
│ ✓ Registra timestamp                                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ DISPARA MessageStatusChanged EVENT                           │
│ - Redis broadcast                                           │
│ - SSE atualiza UI com novo status                           │
└─────────────────────┬───────────────────────────────────────┘
```

### 1.5 Encerramento da Conversa

```
┌─────────────────────────────────────────────────────────────┐
│ AGENTE CLICA "ENCERRAR" NA CONVERSA                         │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CONVERSATIONCONTROLLER::resolve()                            │
│ ✓ Atualiza status para resolved                             │
│ ✓ Libera a conversa (claimed_by = null)                     │
│ ✓ Registra em audit_logs                                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ CONVERSA SAI DA FILA DO AGENTE                              │
│ ✓ Status: in_attendance → resolved                          │
│ ✓ Libera capacidade do agente                               │
│ ✓ Move para histórico                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🤖 2. FLUXO DE DISTRIBUIÇÃO AUTOMÁTICA DE LEADS

### 2.1 Configuração Inicial

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN ACESSA: /admin/distribution                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ ESCOLHE MODO DE DISTRIBUIÇÃO                                │
├─────────────────────────────────────────────────────────────┤
│ ⭕ MANUAL:                                                   │
│   - Agentes vêem conversas em "Aguardando"                  │
│   - Agente clica "Assumir" manualmente                      │
│   - Maior controle, mais lento                              │
│                                                              │
│ ⭕ AUTOMÁTICO:                                               │
│   - Sistema assign automático ao próximo agente             │
│   - Respeta capacidade máxima                               │
│   - Round-robin para distribuição justa                     │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ SE AUTOMÁTICO: DEFINE AÇÃO EM OVERFLOW                       │
├─────────────────────────────────────────────────────────────┤
│ 🔴 next_agent:                                               │
│    - Assign ao agente com MENOR carga (ignora máximo)       │
│    - Ninguém fica esperando, mas agente pode sobrecarregar  │
│                                                              │
│ 🟡 queue:                                                    │
│    - Conversa aguarda fila (status=new)                     │
│    - Só assign quando houver vaga                           │
│    - Fila "Aguardando" fica visível para agentes            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ SALVA CONFIGURAÇÕES NA TABELA distribution_settings         │
│ - mode: manual | automatic                                  │
│ - overflow_action: next_agent | queue                       │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Configuração de Capacidade dos Agentes

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN VISUALIZA: Capacidade por Atendente                   │
├─────────────────────────────────────────────────────────────┤
│ TABELA COM:                                                 │
│ - Nome do agente                                            │
│ - Status (online/offline)                                   │
│ - Conversas ativas / Máximo                                 │
│ - Barra de progresso visual                                 │
│ - Toggle: Ativo na distribuição automática                  │
│ - Botão: Editar capacidade                                  │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ ADMIN CLICA "EDITAR" DE UM AGENTE                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ MODAL: EDITAR MÁXIMO DE CONVERSAS                           │
│ - Muda max_conversations (1-100)                            │
│ - Toggle is_active (sim/não)                                │
│ - Salva via PATCH /admin/distribution/agents/{id}/capacity  │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ AGENTCAPACITY ATUALIZADO                                    │
│ ✓ Imediatamente válido para próximas distribuições          │
│ ✓ Afeta cálculo de "tem capacidade?"                        │
└─────────────────────────────────────────────────────────────┘
```

### 2.3 Algoritmo Round-Robin com Capacidade

```
┌─────────────────────────────────────────────────────────────┐
│ NOVA CONVERSA CHEGA (status=new)                            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ DISTRIBUTIONSERVICE::autoAssign(conversa)                    │
├─────────────────────────────────────────────────────────────┤
│ PASSO 1: Busca agentes ativos com capacidade disponível    │
│   SELECT FROM agent_capacities                              │
│   WHERE is_active = true                                    │
│   AND activeConversationsCount() < max_conversations        │
│                                                              │
│ PASSO 2: Ordena por round_robin_position (ASC)              │
│   - Garante alternância entre agentes                       │
│   - Distribui uniformemente                                 │
│                                                              │
│ PASSO 3: Seleciona o PRIMEIRO da lista                      │
│   - Próximo agente na fila round-robin                      │
│                                                              │
│ PASSO 4: Incrementa round_robin_position                    │
│   - Garante que próxima conversa vá para próximo agente     │
└─────────────────────┬───────────────────────────────────────┘
                      │
        ┌─────────────┴──────────────┐
        │                            │
        ▼                            ▼
┌──────────────────────┐  ┌──────────────────────┐
│ SIM: Agente Disponível│  │ NÃO: Todos Cheios   │
└──────┬───────────────┘  └──────┬───────────────┘
       │                         │
       ▼                         ▼
┌──────────────────────┐  ┌──────────────────────┐
│ ASSIGN ao agente     │  │ Verifica overflow    │
│ - Claim conversation │  │                      │
│ - Status =           │  │ overflow=next_agent? │
│   in_attendance      │  │ overflow=queue?      │
│ - AuditLog:          │  └──────┬───┬───────────┘
│   auto_assigned      │         │   │
└──────────────────────┘         │   │
                        ┌────────┘   └─────┐
                        │                   │
                        ▼                   ▼
                 ┌─────────────┐    ┌──────────────┐
                 │ next_agent  │    │ queue        │
                 ├─────────────┤    ├──────────────┤
                 │ Busca agente│    │ Deixa        │
                 │ com MENOR   │    │ conversa em  │
                 │ carga       │    │ status=new   │
                 │ Assign mesmo│    │ Fila visível │
                 │ se cheio    │    │ para agentes │
                 └──────┬──────┘    └──────┬───────┘
                        │                  │
                        └────────┬─────────┘
                                 │
                                 ▼
                        ┌──────────────────────┐
                        │ Dispara evento       │
                        │ ConversationChanged  │
                        │ SSE atualiza frontend│
                        └──────────────────────┘
```

### 2.4 Visualização de Métricas em Tempo Real

```
┌─────────────────────────────────────────────────────────────┐
│ DASHBOARD ADMIN: /admin/distribution                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  📊 MÉTRICAS (atualizam a cada webhook)                     │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • Total de Agentes: 3                                  │ │
│  │ • Carga Total: 5/30 conversas                          │ │
│  │ • Agentes Cheios: 1                                    │ │
│  │ • Na Fila: 2 conversas aguardando                      │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  📋 DISTRIBUIÇÕES RECENTES (últimas 20 automáticas)         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • Conversation #45 → Maria Silva (há 3 min)           │ │
│  │ • Conversation #44 → João Santos (há 5 min)           │ │
│  │ • Conversation #43 → Ana Costa (há 8 min)             │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  👥 FILA DE ESPERA (se overflow=queue e tem enfileiradas)   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • Cliente: +55 11 99999-8888 (aguardando 12 min)       │ │
│  │   "Última msg: Olá, preciso de ajuda"                  │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 👥 3. FLUXO DE GERENCIAMENTO DE ATENDENTES

### 3.1 Cadastro de Novo Atendente

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN ACESSA: /admin/agents → "Novo Atendente"             │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ FORMULÁRIO: CADASTRO DE ATENDENTE                           │
├─────────────────────────────────────────────────────────────┤
│ • Nome Completo: ________________                           │
│ • Email: _________________________                          │
│ • Senha: ____________________________ (min 8 caracteres)   │
│ • Confirmar Senha: _________________                       │
│ • Máx de Conversas: ____ (padrão: 10)                      │
│                                                              │
│ [Cancelar] [Cadastrar Atendente]                            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ VALIDAÇÃO NO SERVIDOR                                       │
│ ✓ Nome obrigatório                                          │
│ ✓ Email único (não pode duplicado)                          │
│ ✓ Senha confirmada                                          │
│ ✓ Capacidade entre 1-100                                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ AGENTCONTROLLER::store()                                    │
│                                                              │
│ 1. Cria User record:                                        │
│    - name, email, hashed password                           │
│    - role = 'agent'                                         │
│    - status = 'offline'                                     │
│                                                              │
│ 2. Cria AgentCapacity record:                               │
│    - user_id = novo usuário                                 │
│    - max_conversations = informado                          │
│    - is_active = true                                       │
│    - round_robin_position = 0                               │
│                                                              │
│ 3. Log de auditoria: [Agent] New agent registered           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ REDIRECIONADO COM SUCESSO                                   │
│ "Atendente 'João Silva' cadastrado com sucesso!"            │
│ → Volta para /admin/agents                                  │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Edição de Atendente

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN CLICA "EDITAR" EM UM AGENTE                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ FORMULÁRIO: EDITAR ATENDENTE                                │
├─────────────────────────────────────────────────────────────┤
│ • Nome: [João Silva]                                        │
│ • Email: [joao@example.com]                                 │
│ • Status: ⭕ Offline  ⭕ Online                             │
│ • Máx de Conversas: [10]                                    │
│                                                              │
│ 📊 Carga Atual: 2/10 conversas                              │
│                                                              │
│ [Cancelar]  [🗑️ Deletar Atendente]  [Salvar Alterações]    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ AGENTCONTROLLER::update()                                   │
│ ✓ Atualiza User: name, email, status                        │
│ ✓ Atualiza AgentCapacity: max_conversations                 │
│ ✓ Registra auditoria                                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ MUDANÇA IMEDIATAMENTE VÁLIDA                                │
│ ✓ Status online/offline → visível no frontend               │
│ ✓ Capacidade alterada → afeta próximas distribuições        │
└─────────────────────────────────────────────────────────────┘
```

### 3.3 Deleção de Atendente

```
┌─────────────────────────────────────────────────────────────┐
│ ADMIN CLICA "DELETAR" EM UM AGENTE                          │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ VALIDAÇÃO: PODE DELETAR?                                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ ✓ SEM CONVERSAS ATIVAS: Prossegue                           │
│   DELETE user e agent_capacity                              │
│                                                              │
│ ❌ COM CONVERSAS ATIVAS: Erro                               │
│    "Não é possível deletar atendente com X conversa(s)"    │
│    Mensagem: Reassigne as conversas primeiro                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 4. FLUXO DE INTEGRAÇÃO COM WHATSAPP

### 4.1 Webhook WhatsApp → Sistema

```
┌──────────────────────────────────────────────────────────────┐
│ WHATSAPP ENVIA WEBHOOK (POST /webhook)                      │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│ Payload Examples:                                            │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ 1. MENSAGEM INBOUND (texto, imagem, áudio)             │  │
│ │    {                                                    │  │
│ │      "entry": [{                                       │  │
│ │        "changes": [{                                   │  │
│ │          "value": {                                    │  │
│ │            "messages": [{                              │  │
│ │              "id": "wamid.xxx",                        │  │
│ │              "from": "5511999999999",                  │  │
│ │              "timestamp": "1234567890",                │  │
│ │              "type": "text|image|audio|document",      │  │
│ │              "text": { "body": "Olá" } ou             │  │
│ │              "image": { "link": "..." }                │  │
│ │            }]                                          │  │
│ │          }                                             │  │
│ │        }]                                              │  │
│ │      }]                                                │  │
│ │    }                                                    │  │
│ │                                                         │  │
│ │ 2. STATUS UPDATE (mensagem entregue/lida)             │  │
│ │    {                                                    │  │
│ │      "entry": [{                                       │  │
│ │        "changes": [{                                   │  │
│ │          "value": {                                    │  │
│ │            "statuses": [{                              │  │
│ │              "id": "wamid.xxx",                        │  │
│ │              "status": "delivered|read|failed",        │  │
│ │              "timestamp": "1234567890"                 │  │
│ │            }]                                          │  │
│ │          }                                             │  │
│ │        }]                                              │  │
│ │      }]                                                │  │
│ │    }                                                    │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                               │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ WEBHOOKCONTROLLER::handle()                                  │
│                                                               │
│ PASSO 1: Verificar origem (X-Hub-Signature)                 │
│   - Valida HMAC SHA256 com App Secret                       │
│   - Rejeita requisições não autorizadas                      │
│                                                               │
│ PASSO 2: Log completo do webhook                            │
│   - Armazena em storage/logs para debug                      │
│                                                               │
│ PASSO 3: Direciona para handler apropriado                  │
│   - type=message → WhatsAppService::handleInboundMessage()  │
│   - type=status_update → WhatsAppService::processStatusUpd()│
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

### 4.2 Envio de Mensagem WhatsApp

```
┌──────────────────────────────────────────────────────────────┐
│ AGENTE ENVIA MENSAGEM VIA INTERFACE WEB                      │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ CONVERSATIONCONTROLLER::sendMessage()                         │
│                                                               │
│ ✓ Valida autorização (agente reivindicou conversa)           │
│ ✓ Cria Message record (status=pending)                       │
│ ✓ Upload de mídia para storage (se houver)                   │
│ ✓ Atualiza conversation.last_message_at                      │
│                                                               │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ WHATSAPPSERVICE::sendMessage/sendImage/sendAudio/etc()       │
│                                                               │
│ Processa por tipo:                                           │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ TEXTO:                                               │   │
│ │ POST /messages                                       │   │
│ │ {                                                    │   │
│ │   "messaging_product": "whatsapp",                   │   │
│ │   "recipient_type": "individual",                    │   │
│ │   "to": "5511999999999",                             │   │
│ │   "type": "text",                                    │   │
│ │   "text": { "body": "Resposta do agente" }           │   │
│ │ }                                                    │   │
│ └──────────────────────────────────────────────────────┘   │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ IMAGEM/ÁUDIO/DOCUMENTO:                              │   │
│ │ 1. Upload para servidor (com SSL cert validation)   │   │
│ │ 2. POST /messages com URL da mídia                   │   │
│ │ {                                                    │   │
│ │   "to": "5511999999999",                             │   │
│ │   "type": "image|audio|document",                    │   │
│ │   "image": { "link": "https://storage/file.jpg" }    │   │
│ │ }                                                    │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ ✓ Normaliza número (remove caracteres especiais)            │
│ ✓ Trata erros: SSL, timeout, permissões, rate limit        │
│ ✓ Retry automático em caso de falha                         │
│                                                              │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ WHATSAPP API RESPONDE COM SUCESSO                            │
│ {                                                             │
│   "messages": [{                                             │
│     "id": "wamid.new_message_id",                            │
│     "message_status": "accepted"                             │
│   }]                                                          │
│ }                                                             │
│                                                               │
│ ✓ Salva wa_message_id no Message record                      │
│ ✓ Status: pending → sent                                     │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

---

## 📊 5. FLUXO DE DADOS EM TEMPO REAL (SSE + Redis)

### 5.1 Broadcasting via Redis Pub/Sub

```
┌─────────────────────────────────────────────────────────────┐
│ EVENTO DISPARADO NA APLICAÇÃO                               │
│ (ex: ConversationStatusChanged, MessageStatusChanged)        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ LARAVEL EVENT LISTENER                                       │
│ event(new ConversationStatusChanged($conversation))          │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ BROADCAST PARA REDIS                                         │
│ Channel: conversation.{conversation_id}                      │
│ Data: { status, old_status, claimed_by, claimed_at }        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ REDIS PUB/SUB                                                │
│ - Subscribers: Todos os clientes SSE conectados             │
│ - Distribui evento de forma eficiente                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ SSE ENDPOINT TRANSMITE PARA CLIENTE                         │
│ GET /sse/conversation/{id}                                  │
│                                                              │
│ Response (Server-Sent Events):                              │
│ data: {"status":"in_attendance","claimed_by":2}             │
│                                                              │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ JAVASCRIPT RECEBE NO CLIENTE                                │
│ EventSource listener → custom event                         │
│                                                              │
│ eventSource.addEventListener('conversation-status-changed'  │
│   (e) => {                                                   │
│     const data = JSON.parse(e.data)                         │
│     updateConversationUI(data)                               │
│   }                                                          │
│ )                                                            │
│                                                              │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ UI ATUALIZA EM TEMPO REAL                                    │
│ - Conversa move de "Aguardando" para "Minhas Conversas"     │
│ - Status exibido muda instantaneamente                      │
│ - Badges de contador atualizam                              │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 SSE Manager (Frontend)

```
┌─────────────────────────────────────────────────────────────┐
│ PÁGINA CARREGA (conversations.index)                         │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ SSEMANAGER INICIALIZA (resources/js/sse-manager.js)         │
│                                                              │
│ 1. Cria EventSource para:                                   │
│    - /sse/conversation/{id} (se em conversa específica)    │
│    - /sse/conversations (listagem geral)                    │
│    - /sse/messages (todas as mensagens)                     │
│                                                              │
│ 2. Setup listeners para eventos customizados:               │
│    - message-status-changed                                 │
│    - conversation-status-changed                            │
│                                                              │
│ 3. Reconnect automático com exponential backoff:            │
│    - 1º tentativa: 1s                                       │
│    - 2º tentativa: 2s                                       │
│    - 3º tentativa: 4s                                       │
│    - máximo: 30s                                            │
│                                                              │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ DURANTE NAVEGAÇÃO                                            │
│                                                              │
│ - beforeunload: Fecha EventSource                            │
│ - popstate: Reconecta para novo contexto                    │
│ - visibility change: Pausa se abas invisível                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📱 6. ESTRUTURA DE DADOS

### 6.1 Banco de Dados (MySQL)

```sql
-- USUÁRIOS E AUTENTICAÇÃO
users
├── id (PK)
├── name
├── email (UNIQUE)
├── password (hashed)
├── role (enum: admin, agent)
├── status (online, offline)
├── avatar
├── timestamps

-- DISTRIBUIÇÃO
distribution_settings
├── id (PK) [Singleton: sempre id=1]
├── mode (enum: manual, automatic)
├── overflow_action (enum: next_agent, queue)
├── timestamps

agent_capacities
├── id (PK)
├── user_id (FK → users) [UNIQUE]
├── max_conversations (1-100)
├── is_active (boolean)
├── round_robin_position (int, para alternância)
├── timestamps

-- CONVERSAS
contacts
├── id (PK)
├── phone (UNIQUE, normalizado)
├── name
├── avatar
├── timestamps

conversations
├── id (PK)
├── contact_id (FK → contacts)
├── assigned_to (FK → users, antigo, deprecado)
├── claimed_by (FK → users, agente atual)
├── claimed_at (timestamp)
├── status (enum: new, in_attendance, resolved)
├── priority (low, medium, high)
├── last_message_at
├── timestamps

conversation_claims
├── id (PK)
├── conversation_id (FK → conversations)
├── user_id (FK → users, agente)
├── claimed_at
├── released_at (NULL se ainda ativo)
├── reason (motivo do claim/release)

-- MENSAGENS
messages
├── id (PK)
├── conversation_id (FK → conversations)
├── sender_id (FK → users)
├── wa_message_id (ID WhatsApp, UNIQUE)
├── content (texto)
├── type (text, image, audio, document)
├── media_url (para mídia)
├── status (pending, sent, delivered, read, failed)
├── timestamps

-- MACROS (Templates)
macros
├── id (PK)
├── user_id (FK → users, criador)
├── name
├── trigger (atalho)
├── description
├── timestamps

macro_files
├── id (PK)
├── macro_id (FK → macros)
├── file_path
├── file_type
├── order
├── timestamps

-- AUDITORIA
audit_logs
├── id (PK)
├── auditable_type (classe auditada)
├── auditable_id
├── action (enum: created, updated, deleted, claimed, released, assigned, used, auto_assigned)
├── description
├── user_id (FK → users, quem fez, NULL para sistema)
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
├── created_at
```

---

## 🔐 7. SEGURANÇA E VALIDAÇÃO

```
┌─────────────────────────────────────────────────────────────┐
│ AUTENTICAÇÃO                                                 │
├─────────────────────────────────────────────────────────────┤
│ • Login: Email + Senha hasheada (bcrypt)                    │
│ • Sessions: Cookie-based (Laravel default)                  │
│ • CSRF Protection: Token em cada form                       │
│ • Rate Limiting: 60 requisições por minuto                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ AUTORIZAÇÃO                                                  │
├─────────────────────────────────────────────────────────────┤
│ • Admin: Full access                                        │
│ • Agent: Pode ver/responder apenas conversas reivindicadas  │
│ • Middleware: EnsureIsAdmin, auth                           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ WEBHOOK SECURITY                                             │
├─────────────────────────────────────────────────────────────┤
│ • HMAC SHA256 signature verification                         │
│ • Exclude from CSRF (webhook routes)                        │
│ • Phone number sanitization (remove não-dígitos)            │
│ • SSL certificate validation (cURL)                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ VALIDAÇÃO DE FORMULÁRIOS                                     │
├─────────────────────────────────────────────────────────────┤
│ • Server-side validation (FormRequest ou inline)            │
│ • Email: unique, valid format                               │
│ • Capacidade: integer 1-100                                 │
│ • Senha: min 8 caracteres, confirmação                      │
│ • Mensagens: não-vazias, max 4000 caracteres                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 8. FLUXO COMPLETO: DO CLIENTE AO AGENTE

```
CLIENTE ENVIA MSG VIA WHATSAPP
    │
    ▼
WEBHOOK RECEBE NOTIFICAÇÃO
    │
    ├─→ Normaliza número
    ├─→ Busca/cria Contact
    ├─→ Cria Conversation (status=new)
    └─→ Cria Message record
    │
    ▼
DISTRIBUTION SERVICE (modo?)
    │
    ├─→ MANUAL: conversa aguarda agente clicar "assumir"
    │
    └─→ AUTOMATIC:
        │
        ├─→ Agentes disponíveis? (capacidade OK + ativo)
        │   │
        │   ├─→ SIM: Assign ao próximo da fila (round-robin)
        │   │        Status: in_attendance
        │   │
        │   └─→ NÃO: Verifica overflow
        │           │
        │           ├─→ next_agent: Assign ao menos carregado
        │           └─→ queue: Deixa em fila (status=new)
    │
    ▼
EVENTO DISPARADO (ConversationStatusChanged)
    │
    ├─→ Redis Pub/Sub broadcast
    ├─→ SSE notifica clientes
    └─→ UI atualiza em tempo real
    │
    ▼
AGENTE RECEBE NOTIFICAÇÃO
    │
    ├─→ Se automatic: conversa já está atribuída
    └─→ Se manual: agente vê na fila e clica "assumir"
    │
    ▼
AGENTE LEITURA HISTÓRICO
    │
    └─→ Acessa conversation.messages (todas as mensagens)
    │
    ▼
AGENTE RESPONDE
    │
    ├─→ Digita/grava mensagem
    ├─→ Cria Message record (status=pending)
    ├─→ Faz upload de mídia (se houver)
    └─→ Envia para WhatsApp API
    │
    ▼
WHATSAPP ENVIA PARA CLIENTE
    │
    ├─→ Cliente recebe no WhatsApp
    ├─→ Webhook atualiza status: sent → delivered → read
    └─→ SSE notifica agente sobre entrega/leitura
    │
    ▼
CONVERSA RESOLVIDA
    │
    ├─→ Agente clica "Encerrar"
    ├─→ Status: in_attendance → resolved
    ├─→ Libera capacidade do agente
    └─→ Move para histórico
```

---

## 📈 9. PERFORMANCE E ESCALABILIDADE

```
┌─────────────────────────────────────────────────────────────┐
│ CAPACIDADE ESPERADA                                          │
├─────────────────────────────────────────────────────────────┤
│ • 200+ conexões SSE simultâneas                             │
│ • 1000+ mensagens por minuto                                │
│ • Latência: < 1s para atualizar UI                          │
│ • Redis: ~10k ops/sec                                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ OTIMIZAÇÕES                                                  │
├─────────────────────────────────────────────────────────────┤
│ • Database queries with eager loading (with)                │
│ • Pagination: 15 itens por página                           │
│ • Cache: avatares, contatos frequentes                      │
│ • CDN: assets (CSS, JS, imagens)                            │
│ • Compression: gzip para responses                          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ MONITORAMENTO                                                │
├─────────────────────────────────────────────────────────────┤
│ • Logs: storage/logs/laravel.log                            │
│ • Auditoria: audit_logs table                               │
│ • Redis info: monitor subscribers, memory                   │
│ • Métricas: dashboard admin (carga, fila, distribuição)     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ 10. TECNOLOGIAS UTILIZADAS

| Componente | Tecnologia | Versão |
|-----------|-----------|--------|
| **Backend** | Laravel | 11.x |
| **Banco Dados** | MySQL | 8.0+ |
| **Cache/Pub-Sub** | Redis | 7.0+ |
| **Frontend** | Blade Templates + Tailwind | Latest |
| **Real-time** | SSE (Server-Sent Events) | N/A |
| **API WhatsApp** | Graph API | v18.0+ |
| **Autenticação** | Laravel Sessions | Builtin |
| **Deploy** | Docker/Nginx | Latest |

---

## 📝 11. TABELA DE ROTAS PRINCIPAIS

| Método | Rota | Controlador | Descrição |
|--------|------|-----------|----------|
| GET | `/login` | LoginController | Form de login |
| POST | `/login` | LoginController | Processar login |
| POST | `/logout` | LoginController | Logout |
| GET | `/dashboard` | DashboardController | Dashboard principal |
| GET | `/conversations` | ConversationController | Listar conversas |
| POST | `/conversations/send` | ConversationController | Enviar mensagem |
| POST | `/conversations/{id}/claim` | ConversationClaimController | Assumir conversa |
| DELETE | `/conversations/{id}/claim` | ConversationClaimController | Liberar conversa |
| PATCH | `/conversations/{id}/resolve` | ConversationController | Encerrar conversa |
| GET | `/admin/agents` | AgentController | Listar agentes |
| POST | `/admin/agents` | AgentController | Criar agente |
| PUT | `/admin/agents/{id}` | AgentController | Editar agente |
| DELETE | `/admin/agents/{id}` | AgentController | Deletar agente |
| GET | `/admin/distribution` | DistributionController | Config distribuição |
| POST | `/admin/distribution/settings` | DistributionController | Salvar distribuição |
| PATCH | `/admin/distribution/agents/{id}/capacity` | DistributionController | Atualizar capacidade |
| GET | `/webhook` | WebhookController | Validar webhook |
| POST | `/webhook` | WebhookController | Receber webhook |
| GET | `/sse/conversations` | ConversationController | SSE conversas |
| GET | `/sse/conversation/{id}` | ConversationController | SSE conversa específica |
| GET | `/sse/messages` | MessageController | SSE mensagens |

---

## 💡 RESUMO DO FLUXO

```
INGRESSO DE LEAD
    ↓
┌─────────────────────────────────────────┐
│ Webhook WhatsApp                        │
│ → Cria Contact + Conversation + Message │
└─────────────────────────────────────────┘
    ↓
DISTRIBUIÇÃO (Manual ou Automático)
    ↓
┌─────────────────────────────────────────┐
│ Assign para Agente ou Fila de Espera    │
└─────────────────────────────────────────┘
    ↓
ATENDIMENTO ATIVO
    ↓
┌─────────────────────────────────────────┐
│ Agente responde cliente via WhatsApp    │
│ Histórico de mensagens em tempo real    │
│ Múltiplas conversas simultâneas         │
└─────────────────────────────────────────┘
    ↓
ENCERRAMENTO
    ↓
┌─────────────────────────────────────────┐
│ Conversa movida para histórico           │
│ Capacidade liberada para novo lead       │
│ Métricas e auditoria registradas        │
└─────────────────────────────────────────┘
```

---

**Documentação Atualizada: 2026-05-23**
**Sistema: OmniChannel ERP - WhatsApp Service**
