# Plano: Sistema de Claim, Macros Avançadas e Auditoria

**Data:** 22 de maio de 2026  
**Status:** Em Planejamento  
**Escopo:** 3 Features principais em 4 fases

---

## 📋 Visão Geral

### Features Solicitadas

1. **Sistema de Claim (Clamar Atendimento)**
   - Agente pode "clamar" um atendimento aberto
   - Admin consegue desassignar/reassignar
   - Apenas quem tem o claim pode responder
   - Histórico de quem clamou e quando

2. **Macros Avançadas (Individual por Usuário)**
   - Cada usuário cria seus próprios macros
   - Suporte: Texto, Áudio, Vídeo, PDF
   - Atalhos e categorias
   - Preview antes de enviar

3. **Sistema de Histórico/Auditoria**
   - Registro completo de cada atendimento
   - Quem respondeu, quando, o quê
   - Logs de claim/unclaim
   - Timeline de eventos
   - Exportável

---

## 🏗️ Arquitetura de Banco de Dados

### Nova Tabela: `conversation_claims`

```sql
CREATE TABLE conversation_claims (
    id UNSIGNED BIGINT PRIMARY KEY
    conversation_id UNSIGNED BIGINT (FK → conversations)
    user_id UNSIGNED BIGINT (FK → users)
    claimed_at TIMESTAMP
    released_at TIMESTAMP NULLABLE
    reason TEXT NULLABLE
    created_at TIMESTAMP
    updated_at TIMESTAMP
    
    INDEX (conversation_id)
    INDEX (user_id)
    INDEX (claimed_at)
    UNIQUE KEY (conversation_id, user_id, released_at IS NULL)
)
```

**Lógica:**
- Apenas 1 claim ATIVO por conversa (released_at = NULL)
- Histórico de claims anteriores (released_at preenchido)
- Permite rastrear todas as atribuições

---

### Nova Tabela: `macro_files`

Estender suporte de macros para incluir attachments:

```sql
CREATE TABLE macro_files (
    id UNSIGNED BIGINT PRIMARY KEY
    macro_id UNSIGNED BIGINT (FK → macros, cascadeOnDelete)
    file_path STRING
    mime_type STRING
    file_size UNSIGNED INT
    file_type ENUM('audio', 'video', 'document', 'image')
    order_index INT
    created_at TIMESTAMP
    updated_at TIMESTAMP
    
    INDEX (macro_id)
)
```

**Uso:**
- Um macro pode ter 1+ arquivos
- Ordem de envio controlada por `order_index`
- Tipos restritos: audio, video, document, image

---

### Nova Tabela: `audit_logs` (Auditoria)

```sql
CREATE TABLE audit_logs (
    id UNSIGNED BIGINT PRIMARY KEY
    
    -- Contexto do evento
    auditable_type STRING (ex: 'Conversation', 'Message')
    auditable_id UNSIGNED BIGINT
    
    -- O que aconteceu
    action ENUM('created', 'updated', 'deleted', 'claimed', 'released', 'assigned')
    description TEXT
    
    -- Quem fez
    user_id UNSIGNED BIGINT (FK → users, nullable)
    
    -- Detalhes da mudança
    old_values JSON NULLABLE
    new_values JSON NULLABLE
    ip_address STRING NULLABLE
    user_agent STRING NULLABLE
    
    created_at TIMESTAMP
    
    INDEX (auditable_type, auditable_id)
    INDEX (action)
    INDEX (user_id)
    INDEX (created_at)
)
```

**Exemplos de registros:**
```json
{
  "action": "claimed",
  "description": "João Silva clamou o atendimento",
  "auditable_type": "Conversation",
  "auditable_id": 15,
  "user_id": 2,
  "new_values": {"claimed_by": 2, "claimed_at": "2026-05-22 10:30:00"}
}

{
  "action": "updated",
  "description": "Admin alterou atribuição",
  "auditable_type": "Conversation",
  "auditable_id": 15,
  "user_id": 1,
  "old_values": {"assigned_to": 2},
  "new_values": {"assigned_to": 3}
}

{
  "action": "created",
  "description": "Nova mensagem criada",
  "auditable_type": "Message",
  "auditable_id": 456,
  "user_id": 2,
  "new_values": {"content": "...", "type": "text"}
}
```

---

## 🔐 Controle de Acesso

### Regras por Role

#### **ADMIN**
- ✅ Ver todos os atendimentos
- ✅ Desassignar qualquer atendimento
- ✅ Reatribuir para qualquer agente
- ✅ Forçar release de claim
- ✅ Ver histórico de todos
- ✅ Ver audit logs
- ✅ Criar/editar/deletar macros de outros

#### **AGENTE**
- ✅ Ver apenas atendimentos seus (assigned_to = user_id)
- ✅ Clamar atendimento (se disponível)
- ✅ Release próprio claim
- ✅ Responder APENAS se tiver o claim
- ✅ Criar/editar/deletar próprios macros
- ❌ Ver macros de outros agentes
- ❌ Reatribuir atendimentos

### Verificação de Claim

```php
// Middleware/Autorização
if (!Auth::user()->isAdmin() && !$conversation->hasActiveClaim(Auth::id())) {
    return response('Apenas quem clamou este atendimento pode responder', 403);
}
```

---

## 📅 Fases de Implementação

### Phase 0: Preparação (Migrations + Models)
**Duração:** 1-2 dias

#### 0.1 Criar Migration `conversation_claims`
- Arquivo: `database/migrations/2026_05_22_create_conversation_claims_table.php`
- Campos, índices, foreign keys
- Executar: `php artisan migrate`

#### 0.2 Criar Migration `macro_files`
- Arquivo: `database/migrations/2026_05_22_create_macro_files_table.php`
- Atualizar `macros` table com campo `content_type` (text|files)

#### 0.3 Criar Migration `audit_logs`
- Arquivo: `database/migrations/2026_05_22_create_audit_logs_table.php`
- Campos JSON para old_values/new_values

#### 0.4 Criar Models
- `app/Models/ConversationClaim.php`
- `app/Models/MacroFile.php`
- `app/Models/AuditLog.php`

#### 0.5 Adicionar Relacionamentos
- **Conversation**: `claims()` hasMany(ConversationClaim)
- **Macro**: `files()` hasMany(MacroFile)
- **User**: `claims()` hasMany(ConversationClaim)

---

### Phase 1: Sistema de Claim
**Duração:** 2-3 dias

#### 1.1 Métodos no Model Conversation

```php
public function getActiveClaimAttribute()
public function hasActiveClaim(?int $userId = null): bool
public function getClaimedByAttribute()
public function claim(int $userId, ?string $reason = null): ConversationClaim
public function releaseClaim(?string $reason = null): bool
public function reassign(int $newUserId, ?string $reason = null): void
```

#### 1.2 ConversationClaimController

```php
// Novo controller
POST   /conversations/{conversation}/claim          → claim()
DELETE /conversations/{conversation}/claim          → release()
PATCH  /conversations/{conversation}/reassign       → reassign() [Admin only]
GET    /conversations/{conversation}/claim-history  → history()
```

#### 1.3 Atualizar ConversationController

```php
// Bloquear envio de mensagem se não tem claim
public function sendMessage(Request $request)
{
    $conversation = Conversation::find($request->conversation_id);
    
    if (!Auth::user()->isAdmin() && !$conversation->hasActiveClaim(Auth::id())) {
        return response('Você não tem este atendimento. Clame-o primeiro!', 403);
    }
    
    // Continuar com envio...
}
```

#### 1.4 Views Updates

**Conversations Index:**
- Mostrar status do claim (ícone de cadeado com nome de quem clamou)
- Botão "Clamar" (se disponível)
- Botão "Liberar" (se é seu claim)
- Botão "Reatribuir" (admin only)

**Chat View:**
- Mostrar "Atendimento clamado por: João Silva"
- Mostrar "Clamar este atendimento" se disponível
- Desabilitar input de mensagem se não tem claim

#### 1.5 Broadcasting Events

```php
// Novo event: ConversationClaimed
event(new ConversationClaimed($conversation, $user))

// Broadcast channel: conversation.{id}.claim
Echo.channel(`conversation.${id}.claim`)
    .listen('conversation.claimed', callback)
```

#### 1.6 Verificação Checklist

- [ ] Migrations executadas
- [ ] Models com relacionamentos funcionam
- [ ] Agente consegue clamar atendimento aberto
- [ ] Apenas quem tem claim consegue responder
- [ ] Admin consegue reatribuir
- [ ] Histórico de claims aparece
- [ ] Auditoria registra eventos de claim

---

### Phase 2: Macros Avançadas com Attachments
**Duração:** 3-4 dias

#### 2.1 Atualizar Model Macro

```php
// Adicionar método
public function files(): HasMany
public function getPreviewAttribute(): array
public function sendToConversation(Conversation $conv): void
```

#### 2.2 MacroFileController

```php
POST   /macros/{macro}/files          → store()     // Upload arquivo
DELETE /macros/{macro}/files/{file}   → destroy()   // Remover arquivo
GET    /macros/{macro}/preview        → preview()   // Preview em JSON
PATCH  /macros/{macro}/files/{file}   → reorder()   // Reordenar
```

#### 2.3 Tipos Suportados

| Tipo | MIME | Tamanho Max | Formatos |
|------|------|-------------|----------|
| Audio | audio/* | 16 MB | mp3, m4a, aac, ogg |
| Video | video/* | 16 MB | mp4, mov, avi, mkv |
| Document | application/* | 16 MB | pdf, doc, docx, xls |
| Image | image/* | 16 MB | jpg, png, gif, webp |

#### 2.4 Upload Flow

1. **Frontend:** Multi-file upload com preview
2. **Backend:** Validar MIME, tamanho, extensão
3. **Storage:** Salvar em `storage/app/macros/{user_id}/{macro_id}/`
4. **DB:** Registrar em macro_files com metadata
5. **Security:** Path transversal prevention, virus scan (optional)

#### 2.5 Views Updates

**Macros Index:**
- Mostrar tipo de macro (com ícone: 📝 Texto, 🎵 Áudio, 🎬 Vídeo, 📄 Documento)
- Preview no hover
- Botão "Editar" abre modal com upload

**Macros Form (Nova/Edit):**
- Text input para conteúdo
- Dropzone para múltiplos arquivos
- Preview dos arquivos adicionados
- Reordenar por drag-n-drop
- Remover arquivo individual

**Chat - Quick Macros:**
- Seletor de macro
- Preview rápido
- Botão "Enviar" (copia para input ou envia direto)

#### 2.6 Verificação Checklist

- [ ] Migrations e models criados
- [ ] Upload de arquivo funciona
- [ ] Validação de MIME/tamanho funciona
- [ ] Preview exibe corretamente
- [ ] Reordenação funciona
- [ ] Macros de texto/arquivo combinados funcionam
- [ ] Cada usuário vê apenas seus macros
- [ ] Admin consegue ver macros de todos (opcional)

---

### Phase 3: Sistema de Auditoria/Histórico
**Duração:** 2-3 dias

#### 3.1 AuditLog Model e Listener

```php
// Model
class AuditLog extends Model
{
    protected $fillable = ['auditable_type', 'auditable_id', 'action', 'description', 'user_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    protected $casts = ['old_values' => 'json', 'new_values' => 'json'];
}

// Listener - registra eventos automaticamente
class ConversationEventListener
{
    public function onUpdated(Conversation $conversation)
    {
        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'updated',
            'description' => 'Conversa atualizada',
            'user_id' => Auth::id(),
            'old_values' => $conversation->getOriginal(), // antes
            'new_values' => $conversation->getAttributes(), // depois
        ]);
    }
}
```

#### 3.2 Timeline View

**Conversation Detail Page:**
```
Histórico do Atendimento
═══════════════════════

📅 22/05/2026 - 10:45 | 👤 João Silva | CLAIMED
   Clamou o atendimento

📅 22/05/2026 - 10:48 | 👤 João Silva | MESSAGE
   Enviou: "Oi, como posso ajudar?"

📅 22/05/2026 - 10:50 | 👤 Cliente | MESSAGE
   Recebeu: "Preciso da nota fiscal"

📅 22/05/2026 - 10:52 | 👤 João Silva | MESSAGE
   Enviou: [PDF - nota_fiscal.pdf]

📅 22/05/2026 - 11:00 | 👤 Admin | REASSIGNED
   Reatribuiu de João Silva para Maria Santos

📅 22/05/2026 - 11:15 | 👤 Maria Santos | MESSAGE
   Enviou: "Aqui está!"
```

#### 3.3 AuditController

```php
GET /conversations/{conversation}/audit      → timeline()
GET /conversations/{conversation}/audit/export → export() // CSV/PDF
GET /conversations                           → listWithStats() // Com tempo médio, volume, etc
```

#### 3.4 Eventos Auditáveis

```
Conversation:
- created (novo atendimento)
- claimed (clamado)
- released (liberado)
- assigned (reatribuído)
- priority_changed (prioridade alterada)
- status_changed (aberto/fechado)

Message:
- created (nova mensagem)
- deleted (deletada - soft delete)

Macro:
- created (novo macro)
- updated (editado)
- deleted (deletado)
- used (enviado via macro)
```

#### 3.5 Verificação Checklist

- [ ] AuditLog model criado
- [ ] Eventos automáticos registram mudanças
- [ ] Timeline mostra eventos corretos
- [ ] Timestamps precisos
- [ ] old_values vs new_values funciona
- [ ] Export em CSV funciona
- [ ] Admin consegue filtrar por data/agente
- [ ] Relatório de atividade por agente funciona

---

### Phase 4: Integração e Testes
**Duração:** 2-3 dias

#### 4.1 Atualizar Views Existentes

- [ ] conversations/index.blade.php — Mostrar status de claim
- [ ] conversations/show.blade.php — Histórico na sidebar
- [ ] macros/index.blade.php — Tipos de arquivo suportados
- [ ] dashboard.blade.php — Métricas de claims (tempo médio, taxa, etc)

#### 4.2 Atualizar APIs

- [ ] ReportController — Novo endpoint para métricas de claim
- [ ] Atualizar response de conversations para incluir claim_info

#### 4.3 Frontend JavaScript

- [ ] Chat-inbox.js — Desabilitar input se não tem claim
- [ ] Listeners de events para claim/release em tempo real
- [ ] Modal de "Clamar" com confirmação
- [ ] Modal de histórico/timeline

#### 4.4 Testes (Unit + Feature)

```php
Tests/Feature/ConversationClaimTest.php
- test_agente_pode_clamar_atendimento()
- test_apenas_quem_clamou_pode_responder()
- test_admin_pode_reatribuir()
- test_liberar_claim_remove_restrição()
- test_auditoria_registra_claim()

Tests/Feature/MacroFileTest.php
- test_upload_arquivo_funciona()
- test_valida_mime_type()
- test_enviar_macro_com_arquivo()
- test_reordenar_arquivos()

Tests/Feature/AuditLogTest.php
- test_auditoria_registra_mudanças()
- test_export_csv_funciona()
- test_timeline_mostra_eventos()
```

#### 4.5 Migração de Dados (Histórico Retroativo)

Opcionalmente, criar script para populate histórico anterior:
```php
// Inferir eventos a partir de timestamps
// Para cada conversa: created_at → created event
// updated_at > created_at → updated event
// Salvar com valores genéricos (user_id = null)
```

#### 4.6 Verificação Final Checklist

- [ ] Todas as features funcionam integradas
- [ ] Sem breaking changes em features existentes
- [ ] Performance aceitável (índices adicionados)
- [ ] Testes passam (>90% coverage)
- [ ] Documentação atualizada
- [ ] Deploy checklist completo

---

## 🛠️ Stack Técnico

### Banco de Dados
- ✓ Migrations (Laravel)
- ✓ Índices otimizados
- ✓ Foreign keys com cascades

### Backend
- ✓ Eloquent models com relacionamentos
- ✓ Event listeners para auditoria
- ✓ Controllers RESTful
- ✓ Validações (FormRequest)
- ✓ Autorização (Gates/Policies)

### Frontend
- ✓ Blade templates
- ✓ Tailwind CSS
- ✓ JavaScript vanilla
- ✓ AJAX com deduplicação
- ✓ Drag-n-drop (Sortable.js ou similar)

### Features Existentes a Integrar
- ✓ Broadcasting (via Pusher/Echo)
- ✓ WhatsApp API
- ✓ Cache (para performance)
- ✓ Logging

---

## 📊 Estimativa de Tempo

| Phase | Tasks | Duração | Início | Fim |
|-------|-------|---------|--------|-----|
| **0** | Preparação | 1-2 dias | Dia 1 | Dia 2 |
| **1** | Claim | 2-3 dias | Dia 3 | Dia 5 |
| **2** | Macros Avançadas | 3-4 dias | Dia 6 | Dia 9 |
| **3** | Auditoria | 2-3 dias | Dia 10 | Dia 12 |
| **4** | Integração + Testes | 2-3 dias | Dia 13 | Dia 15 |

**Total: ~15 dias (3 semanas)**

---

## 🚀 Próximos Passos

1. **Aprovação do Plano** ✋
2. **Phase 0: Migrations + Models**
3. **Phase 1: Claim System**
4. **Phase 2: Advanced Macros**
5. **Phase 3: Audit/History**
6. **Phase 4: Integration + QA**
7. **Deploy + Documentation**

---

**Pronto para começar?** Quer que eu implemente alguma phase específica?
