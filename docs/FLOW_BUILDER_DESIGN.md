# Sistema de Fluxos Conversacionais Hierárquicos

**Data:** 2026-05-29  
**Status:** Design Approved  
**Escopo:** Sistema admin para criar fluxos automáticos de conversa com roteamento por setor

---

## 1. Visão Geral

Sistema que permite ao **admin criar fluxos de conversa automáticos** que são executados quando uma nova conversa chega. Os fluxos:

- Enviam mensagens automáticas (boas-vindas, menus)
- Coletam escolhas do cliente (opções numeradas 1, 2, 3...)
- Roteiam a conversa para um **setor específico** baseado na escolha
- Suportam **fluxos hierárquicos** (fluxo principal → subfluxos por setor)
- Integram automaticamente com a **fila de atendimento existente**

**Exemplo prático:**
```
Cliente envia mensagem
    ↓
Bot: "Escolha: 1=Suporte, 2=Comercial, 3=Admin"
    ↓
Cliente digita "2"
    ↓
Bot: "Comercial - Escolha: 1=Alocação Salão, 2=Compra Título, 3=Agendamento"
    ↓
Cliente digita "1"
    ↓
Conversa entra na fila do setor Comercial
    ↓
Agente atende (ve histórico completo do bot)
```

---

## 2. Arquitetura

### 2.1 Camadas

```
┌─────────────────────────────┐
│    Admin Panel (CRUD)       │  Form-based, listagem, gerenciamento
└──────────────┬──────────────┘
               │ saves/updates
┌──────────────▼──────────────┐
│   Flow Engine (Execution)   │  Executa fluxo em resposta a evento
└──────────────┬──────────────┘
               │ on completion
┌──────────────▼──────────────┐
│   Queue Integration         │  Atribui setor, entra em fila
└─────────────────────────────┘
```

### 2.2 Trigger Points

Fluxos são acionados em:
1. **`on_new_conversation`** – Quando webhook chega, conversa nova é criada
2. **`on_command`** – Quando admin ou cliente dispara comando (ex: `/menu`)
3. **`manual`** – Admin dispara manualmente via dashboard (futuro)

Apenas um fluxo `on_new_conversation` ativo por vez. Múltiplos subfluxos podem estar inativos, ativados conforme necessário.

---

## 3. Banco de Dados

### 3.1 Nova Tabela: `conversation_flows`

Armazena definição dos fluxos.

```sql
CREATE TABLE conversation_flows (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,                    -- "Fluxo Boas-vindas"
    type ENUM('primary', 'secondary') DEFAULT 'primary',
    trigger_type ENUM('on_new_conversation', 'on_command', 'manual') DEFAULT 'on_new_conversation',
    command_name VARCHAR(50) NULL,                 -- ex: "/menu", só se trigger=on_command
    is_active BOOLEAN DEFAULT true,
    parent_flow_id BIGINT NULL,                    -- FK se for subfluxo
    config JSON,                                   -- msg inicial, etc
    created_by BIGINT NOT NULL,                    -- FK users
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_flow_id) REFERENCES conversation_flows(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

**Exemplo `config` JSON:**
```json
{
  "initial_message": "Olá! 👋 Bem-vindo. Qual o tipo de atendimento?",
  "final_message": "Ótimo! Um agente irá ajudá-lo em breve. ⏳",
  "description": "Fluxo inicial de categorização"
}
```

### 3.2 Nova Tabela: `flow_nodes`

Armazena cada "bloco" (opção, mensagem, ação) dentro de um fluxo.

```sql
CREATE TABLE flow_nodes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    flow_id BIGINT NOT NULL,                       -- FK conversation_flows
    node_type ENUM('message', 'menu', 'action') DEFAULT 'message',
    position INT NOT NULL,                         -- ordem na sequência
    config JSON NOT NULL,                          -- dados específicos do node
    target_sector_id BIGINT NULL,                  -- FK sectors (se opção leva a setor)
    target_flow_id BIGINT NULL,                    -- FK conversation_flows (se opção leva a subfluxo)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (flow_id) REFERENCES conversation_flows(id) ON DELETE CASCADE,
    FOREIGN KEY (target_sector_id) REFERENCES sectors(id),
    FOREIGN KEY (target_flow_id) REFERENCES conversation_flows(id)
);
```

**Exemplo `config` para node tipo "menu":**
```json
{
  "option_number": 1,
  "label": "Suporte Técnico",
  "description": "Para problemas técnicos"
}
```

### 3.3 Nova Tabela: `flow_executions` (Histórico)

Registra cada vez que um fluxo é executado (para relatórios/debug).

```sql
CREATE TABLE flow_executions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,               -- FK conversations
    flow_id BIGINT NOT NULL,                       -- FK conversation_flows
    node_id BIGINT NULL,                           -- FK flow_nodes (último node)
    client_choice INT NULL,                        -- qual número o cliente digitou (1,2,3...)
    status ENUM('started', 'in_progress', 'completed', 'failed') DEFAULT 'in_progress',
    result_sector_id BIGINT NULL,                  -- qual setor a conversa foi atribuída
    result_subflow_id BIGINT NULL,                 -- qual subfluxo foi ativado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (flow_id) REFERENCES conversation_flows(id),
    FOREIGN KEY (node_id) REFERENCES flow_nodes(id)
};
```

---

## 4. Modelos Laravel

### 4.1 `ConversationFlow` Model

```php
class ConversationFlow extends Model {
    protected $fillable = ['name', 'type', 'trigger_type', 'command_name', 'is_active', 'parent_flow_id', 'config', 'created_by'];
    protected $casts = ['config' => 'array', 'is_active' => 'boolean'];

    public function nodes() { return $this->hasMany(FlowNode::class); }
    public function parentFlow() { return $this->belongsTo(ConversationFlow::class, 'parent_flow_id'); }
    public function subflows() { return $this->hasMany(ConversationFlow::class, 'parent_flow_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function executions() { return $this->hasMany(FlowExecution::class); }
}
```

### 4.2 `FlowNode` Model

```php
class FlowNode extends Model {
    protected $fillable = ['flow_id', 'node_type', 'position', 'config', 'target_sector_id', 'target_flow_id'];
    protected $casts = ['config' => 'array'];

    public function flow() { return $this->belongsTo(ConversationFlow::class); }
    public function targetSector() { return $this->belongsTo(Sector::class, 'target_sector_id'); }
    public function targetFlow() { return $this->belongsTo(ConversationFlow::class, 'target_flow_id'); }
}
```

### 4.3 `FlowExecution` Model

```php
class FlowExecution extends Model {
    protected $fillable = ['conversation_id', 'flow_id', 'node_id', 'client_choice', 'status', 'result_sector_id', 'result_subflow_id'];

    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function flow() { return $this->belongsTo(ConversationFlow::class); }
    public function node() { return $this->belongsTo(FlowNode::class); }
}
```

---

## 5. Services / Business Logic

### 5.1 `FlowService`

Responsável por executar fluxos.

```php
class FlowService {
    // Executa um fluxo do início
    public function executeFlow(Conversation $conversation, ConversationFlow $flow): void {
        $execution = FlowExecution::create([
            'conversation_id' => $conversation->id,
            'flow_id' => $flow->id,
            'status' => 'started'
        ]);

        $this->processFlowNodes($conversation, $flow, $execution);
    }

    // Processa cada nó sequencialmente
    private function processFlowNodes(Conversation $conversation, ConversationFlow $flow, FlowExecution $execution): void {
        // 1. Envia mensagem inicial do fluxo
        $this->sendMessage($conversation, $flow->config['initial_message']);

        // 2. Para cada opção (menu node), aguarda resposta
        $menuNodes = $flow->nodes()->where('node_type', 'menu')->orderBy('position')->get();
        
        if ($menuNodes->isEmpty()) {
            // Fluxo sem menu: envia msg final e entra em fila
            $this->sendMessage($conversation, $flow->config['final_message']);
            $this->completeFlow($conversation, $execution, null);
            return;
        }

        // 3. Aguarda resposta do cliente (implementar via listener)
        // Quando cliente responde, chamar handleClientResponse()
    }

    // Processa resposta do cliente (1, 2, 3, etc)
    public function handleClientResponse(Conversation $conversation, int $clientChoice, FlowExecution $execution): void {
        $menuNode = $execution->flow->nodes()
            ->where('node_type', 'menu')
            ->where('config->option_number', $clientChoice)
            ->first();

        if (!$menuNode) {
            // Opção inválida: reenviar menu
            $this->replayMenu($conversation, $execution->flow);
            return;
        }

        // Registrar escolha
        $execution->update(['node_id' => $menuNode->id, 'client_choice' => $clientChoice]);

        // Se leva a subfluxo: ativar subfluxo
        if ($menuNode->target_flow_id) {
            $this->executeFlow($conversation, $menuNode->targetFlow);
            return;
        }

        // Se leva a setor: enviar msg final e atribuir setor
        $this->sendMessage($conversation, $execution->flow->config['final_message']);
        
        if ($menuNode->target_sector_id) {
            $this->completeFlow($conversation, $execution, $menuNode->target_sector_id);
        }
    }

    // Marca fluxo como completo, atribui setor, entra em fila
    private function completeFlow(Conversation $conversation, FlowExecution $execution, ?int $sectorId): void {
        $execution->update(['status' => 'completed', 'result_sector_id' => $sectorId]);

        if ($sectorId) {
            // Atribuir conversa ao setor
            $conversation->update(['sector_id' => $sectorId]);
        }

        // Conversa continua na fila normal (status = 'new', pronta para agente clamar)
    }

    private function sendMessage(Conversation $conversation, string $message): void {
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'content' => $message,
            'status' => 'pending'
        ]);
        // WhatsAppService envia via API
    }
}
```

### 5.2 `FlowManagementService`

Responsável por CRUD de fluxos (usado pelo admin panel).

```php
class FlowManagementService {
    public function createFlow(array $data): ConversationFlow {
        $flow = ConversationFlow::create($data);
        
        // Criar nodes (opções) a partir de dados
        foreach ($data['nodes'] ?? [] as $nodeData) {
            FlowNode::create([
                'flow_id' => $flow->id,
                ...$nodeData
            ]);
        }

        return $flow;
    }

    public function updateFlow(ConversationFlow $flow, array $data): ConversationFlow {
        $flow->update($data);
        
        // Atualizar nodes
        FlowNode::where('flow_id', $flow->id)->delete();
        foreach ($data['nodes'] ?? [] as $nodeData) {
            FlowNode::create([
                'flow_id' => $flow->id,
                ...$nodeData
            ]);
        }

        return $flow;
    }

    public function toggleFlow(ConversationFlow $flow): void {
        if ($flow->type === 'primary' && $flow->trigger_type === 'on_new_conversation') {
            // Desativar outros fluxos do mesmo tipo
            ConversationFlow::where('id', '!=', $flow->id)
                ->where('type', 'primary')
                ->where('trigger_type', 'on_new_conversation')
                ->update(['is_active' => false]);
        }

        $flow->update(['is_active' => !$flow->is_active]);
    }
}
```

---

## 6. Controllers & Routes

### 6.1 Routes

```php
Route::middleware(['auth', 'ensure_is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('flows', FlowController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/flows/{flow}/toggle', [FlowController::class, 'toggle'])->name('flows.toggle');
    Route::get('/flows/{flow}/executions', [FlowController::class, 'executions'])->name('flows.executions');
});
```

### 6.2 `FlowController`

```php
class FlowController extends Controller {
    public function index() {
        $flows = ConversationFlow::with('nodes', 'createdBy')
            ->orderBy('type', 'desc')
            ->paginate(20);
        return view('admin.flows.index', compact('flows'));
    }

    public function create() {
        $sectors = Sector::all();
        return view('admin.flows.create', compact('sectors'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:primary,secondary',
            'trigger_type' => 'required|in:on_new_conversation,on_command,manual',
            'config' => 'required|array',
            'config.initial_message' => 'required|string',
            'config.final_message' => 'required|string',
            'nodes' => 'array'
        ]);

        $data['created_by'] = auth()->id();
        $service = app(FlowManagementService::class);
        $flow = $service->createFlow($data);

        return redirect()->route('admin.flows.index')->with('success', 'Fluxo criado!');
    }

    public function edit(ConversationFlow $flow) {
        $sectors = Sector::all();
        return view('admin.flows.edit', compact('flow', 'sectors'));
    }

    public function update(ConversationFlow $flow, Request $request) {
        $data = $request->validate([...]);
        $service = app(FlowManagementService::class);
        $service->updateFlow($flow, $data);

        return redirect()->route('admin.flows.index')->with('success', 'Fluxo atualizado!');
    }

    public function toggle(ConversationFlow $flow) {
        $service = app(FlowManagementService::class);
        $service->toggleFlow($flow);

        return back()->with('success', $flow->is_active ? 'Fluxo ativado' : 'Fluxo desativado');
    }

    public function executions(ConversationFlow $flow) {
        $executions = $flow->executions()
            ->with('conversation.contact')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.flows.executions', compact('flow', 'executions'));
    }
}
```

---

## 7. Frontend / Views

### 7.1 Listagem (`admin.flows.index`)

- Tabela: Nome | Tipo | Trigger | Status | Ações
- Ações: Editar, Ativar/Desativar, Ver Histórico, Deletar
- Botão: + Criar Novo Fluxo
- Indicador visual de qual fluxo está ativo

### 7.2 Criar/Editar (`admin.flows.create`, `admin.flows.edit`)

- Form fields:
  - Nome do fluxo
  - Tipo (Primary / Secondary)
  - Trigger (on_new_conversation / on_command / manual)
  - Mensagem Inicial
  - Tabela de Opções:
    - Número (1, 2, 3...)
    - Label (ex: "Suporte Técnico")
    - Destino (Setor / Subfluxo)
    - Botão remover
  - Botão: + Adicionar Opção
  - Mensagem Final
  - Salvar e Ativar / Salvar

### 7.3 Histórico (`admin.flows.executions`)

- Tabela: Contact | Opção Escolhida | Setor Resultado | Timestamp | Status
- Filtros: Data, Status, Cliente
- Gráfico: Distribuição de opções escolhidas (qual opção é mais popular)

---

## 8. Integração com Sistema Existente

### 8.1 WhatsApp Webhook

Em `WhatsAppService::handleInboundMessage()`:

```php
public function handleInboundMessage(array $webhook): void {
    // ... código existente de criar conversation ...

    $conversation = Conversation::firstOrCreate([...]);

    // NOVO: Verificar se existe fluxo ativo para esta conversa
    if ($conversation->status === 'new') {
        $flow = ConversationFlow::where('trigger_type', 'on_new_conversation')
            ->where('is_active', true)
            ->first();

        if ($flow) {
            $flowService = app(FlowService::class);
            $flowService->executeFlow($conversation, $flow);
            return; // Fluxo vai processar as mensagens
        }
    }

    // ... resto do código existente ...
}
```

### 8.2 Listener para Respostas do Cliente

Quando cliente responde durante um fluxo:

```php
// Evento disparado quando msg chega
class MessageReceived {
    public function handle(): void {
        $conversation = $this->message->conversation;
        $execution = FlowExecution::where('conversation_id', $conversation->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($execution) {
            // Cliente respondeu durante fluxo
            if (is_numeric($this->message->content)) {
                $flowService = app(FlowService::class);
                $flowService->handleClientResponse(
                    $conversation,
                    (int) $this->message->content,
                    $execution
                );
            }
        }
    }
}
```

### 8.3 Queue/Distribuição

Após fluxo completar, conversa segue fluxo normal:
- Status = `new`
- `sector_id` preenchido (se foi atribuído)
- Distribution system pega conversas `new` com setor específico
- Agente clama normalmente

---

## 9. Tratamento de Erros

| Cenário | Comportamento |
|---------|---------------|
| Cliente digita opção inválida | Re-envia menu, aguarda resposta válida |
| Fluxo não tem opções | Envia msg final e entra em fila |
| Falha ao enviar mensagem WhatsApp | Registra erro em audit_log, tenta reenviar |
| Fluxo target não existe | Log de erro, reverter a conversa para "new" sem setor |
| Setor target não existe | Log de erro, conversa sem setor |

---

## 10. Segurança

- ✅ Apenas admin pode criar/editar fluxos (middleware `ensure_is_admin`)
- ✅ Fluxos salvos em BD (não executáveis diretamente)
- ✅ Validação: nome, opções, setores existem
- ✅ Audit log: quem criou/editou fluxo e quando
- ✅ Rate limiting na API (standard)

---

## 11. Métricas/Observabilidade

Registrar em `flow_executions`:
- Qual fluxo foi executado
- Qual opção cliente escolheu
- Qual setor foi atribuído
- Timestamp

Dashboard futuro pode mostrar:
- Opção mais popular
- Taxa de conclusão de fluxo
- Tempo médio no fluxo

---

## 12. Fases de Implementação

**Fase 1 (MVP):** Criar fluxos, listar, editar, executar (uma única opção por fluxo, roteamento simples)
**Fase 2:** Múltiplas opções por fluxo, subfluxos hierárquicos
**Fase 3:** Relatórios, métricas, command-based triggers
**Fase 4:** Condicional avançada, webhooks, delays

---

## 13. Checklist de Aceitação

- ✅ Admin cria fluxo com mensagens e opções
- ✅ Cliente recebe fluxo automaticamente em nova conversa
- ✅ Cliente escolhe opção → conversa entra em fila do setor
- ✅ Admin edita fluxo existente
- ✅ Admin ativa/desativa fluxos
- ✅ Histórico de execuções visível
- ✅ Subfluxos funcionam (opção leva a outro fluxo)
- ✅ Conversa mantém contexto (agente vê de onde veio)
- ✅ Mensagens salvas no histórico da conversa
