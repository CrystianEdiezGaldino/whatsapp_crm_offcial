# Plano Completo — Melhorias SMCC WhatsApp ERP

**Data:** 21 de maio de 2026  
**Status:** Em Planejamento  
**Prioridade:** Notificações → Relatórios → Ajustes Técnicos

---

## 📋 Fases

1. [Phase 1: Notificações com Áudio](#phase-1-notificações-com-áudio) (3-4 dias)
2. [Phase 2: Relatórios e Dashboard](#phase-2-relatórios-e-dashboard) (3-4 dias)
3. [Phase 3: Ajustes Técnicos e Performance](#phase-3-ajustes-técnicos-e-performance) (2-3 dias)

---

## PHASE 1: Notificações com Áudio

### Objetivo
Agentes recebem **alertas em tempo real** quando chega mensagem nova, com opção de **som de notificação**. Suporta navegador minimizado / abas inativas.

### Arquitetura

```
WhatsApp → Webhook → WebhookController
             ↓
    WhatsAppService::processWebhook
             ↓
         Event::dispatch (MessageReceived)
             ↓
         ┌────────────────┐
         │  Broadcast     │
         │  (Pusher/      │
         │   Laravel Echo)│
         └────────────────┘
             ↓
    Client JS (chat-inbox.js)
             ↓
    ┌───────────────────────┐
    │ Browser Notification  │
    │ + Sound Asset         │
    │ + Desktop Alert       │
    └───────────────────────┘
```

### Tasks

#### 1.1 Setup Broadcasting com Laravel Echo + Pusher (FREE tier)

**Referência:** `config/broadcasting.php`, `package.json`, `resources/js/bootstrap.js`

**O que fazer:**
1. Registrar conta **Pusher** (free tier: 100 conexões simultâneas)
   - Copiar: Cluster (ex: `us2`), Key, Secret, App ID
   
2. Atualizar `.env`:
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=xxxx
   PUSHER_APP_KEY=xxxx
   PUSHER_APP_SECRET=xxxx
   PUSHER_HOST=api-{cluster}.pusher.com
   PUSHER_PORT=443
   PUSHER_SCHEME=https
   PUSHER_CLUSTER=us2
   ```

3. Instalar dependências npm:
   ```bash
   npm install pusher-js laravel-echo --save
   ```

4. Criar arquivo: `resources/js/bootstrap.js` com Echo listener:
   ```javascript
   import Echo from 'laravel-echo';
   import Pusher from 'pusher-js';

   window.Echo = new Echo({
       broadcaster: 'pusher',
       key: import.meta.env.VITE_PUSHER_APP_KEY,
       cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
       forceTLS: true,
       encrypted: true,
   });
   ```

5. Atualizar `.env.example`:
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=
   PUSHER_APP_KEY=
   PUSHER_APP_SECRET=
   PUSHER_CLUSTER=us2
   ```

**Referência de implementação:** Laravel Broadcasting docs (https://laravel.com/docs/11.x/broadcasting)

**Verificação:**
- [ ] Pusher account criada e credenciais no `.env`
- [ ] `npm install pusher-js laravel-echo` executado
- [ ] `resources/js/bootstrap.js` importado em `resources/views/layouts/app.blade.php`
- [ ] `npm run dev` não retorna erros

---

#### 1.2 Criar Event `MessageReceived`

**Arquivo a criar:** `app/Events/MessageReceived.php`

```php
<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use SerializesModels;

    public Message $message;
    public $conversationId;
    public $agentId;

    public function __construct(Message $message, $conversationId, $agentId)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;
        $this->agentId = $agentId;
    }

    public function broadcastOn(): Channel
    {
        // Broadcast apenas para agente atribuído e admins
        return new Channel('conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'content' => $this->message->content,
            'sender_name' => $this->message->conversation->contact->name,
            'conversation_id' => $this->conversationId,
            'type' => $this->message->type,
            'timestamp' => now()->format('H:i'),
        ];
    }
}
```

**Verificação:**
- [ ] Arquivo criado em `app/Events/MessageReceived.php`
- [ ] Classe implementa `ShouldBroadcast`
- [ ] Método `broadcastOn()` retorna Channel
- [ ] Método `broadcastWith()` retorna dados simplificados (não serializa message completa)

---

#### 1.3 Disparar Event em `WebhookController` (ou `WhatsAppService`)

**Arquivo a editar:** `app/Services/WhatsAppService.php` (método `handleInboundMessage`)

**Depois de salvar a mensagem no banco (Message::create), adicionar:**

```php
// No final de WhatsAppService::handleInboundMessage
use App\Events\MessageReceived;

// ... após Message::create($messageData)
$message = Message::where('wa_message_id', $waMessage['id'])->first();
$conversation = $message->conversation;
$agentId = $conversation->assigned_to; // pode ser null

event(new MessageReceived($message, $conversation->id, $agentId));
```

**Verificação:**
- [ ] Import `use App\Events\MessageReceived` adicionado
- [ ] `event()` disparado após Message::create
- [ ] Logs mostram `[MessageReceived] Event fired` (adicionar Log::info)

---

#### 1.4 Frontend: Listener JavaScript no `chat-inbox.js`

**Arquivo a editar:** `public/js/helpers/chat-inbox.js` (ou criar `resources/js/notifications.js`)

**Adicionar listener:**

```javascript
// Listener de notificações via Laravel Echo
Echo.channel(`conversation.${conversationId}`)
    .listen('message.received', (event) => {
        console.log('Nova mensagem recebida:', event);
        
        // 1. Reproduzir som de notificação
        playNotificationSound();
        
        // 2. Mostrar Browser Notification
        if (Notification.permission === 'granted') {
            showDesktopNotification(
                event.sender_name,
                event.content,
                event.conversation_id
            );
        }
        
        // 3. Atualizar UI se chat está aberto
        if (isCurrentChatOpen(event.conversation_id)) {
            // Já vai chegar pelo poll, não precisa adicionar manualmente
        }
    });

// Função para reproduzir som
function playNotificationSound() {
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.5;
    audio.play().catch(err => console.log('Autoplay blocked:', err));
}

// Função para mostrar notificação desktop
function showDesktopNotification(sender, message, conversationId) {
    const notification = new Notification(sender, {
        body: message.substring(0, 100),
        icon: '/images/whatsapp-icon.png',
        badge: '/images/badge.png',
        tag: 'whatsapp-' + conversationId, // evita duplicatas
    });
    
    notification.onclick = () => {
        window.focus();
        window.location.href = `/conversations?conversation=${conversationId}`;
    };
}

// Verificar permissão de notificação no load
document.addEventListener('DOMContentLoaded', () => {
    if (Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            console.log('Notification permission:', permission);
        });
    }
});
```

**Assets necessários:**
- Som: `public/sounds/notification.mp3` (carregar arquivo MP3 pequeno, ~50KB)
- Ícone: `public/images/whatsapp-icon.png` (192x192)
- Badge: `public/images/badge.png` (96x96)

**Verificação:**
- [ ] Arquivo criado/editado com listener Echo
- [ ] `playNotificationSound()` implementada
- [ ] `showDesktopNotification()` implementada
- [ ] Arquivo `notification.mp3` presente em `public/sounds/`
- [ ] Teste: abrir Chrome DevTools → Console, simular evento com:
  ```javascript
  Echo.channel('conversation.1').listen('message.received', console.log)
  ```

---

#### 1.5 Adicionar HTML para Notificação Inline (opcional)

**Arquivo a editar:** `resources/views/conversations/index.blade.php`

**Adicionar elemento para toast/notificação visível:**

```html
<!-- Notificação inline (toast) -->
<div id="notificationToast" class="fixed bottom-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg hidden transition-all duration-300">
    <div class="flex items-center gap-3">
        <span class="text-sm">📩 <strong id="toastSender"></strong>: <span id="toastMessage"></span></span>
        <button onclick="document.getElementById('notificationToast').classList.add('hidden')" class="text-white">✕</button>
    </div>
</div>

<script>
// Atualizar toast quando evento chegar
function showNotificationToast(sender, message) {
    document.getElementById('toastSender').textContent = sender;
    document.getElementById('toastMessage').textContent = message.substring(0, 50);
    
    const toast = document.getElementById('notificationToast');
    toast.classList.remove('hidden');
    
    // Auto-hide após 5 segundos
    setTimeout(() => toast.classList.add('hidden'), 5000);
}

// Integrar com Echo listener
Echo.channel(`conversation.${window.conversationId}`)
    .listen('message.received', (event) => {
        showNotificationToast(event.sender_name, event.content);
        playNotificationSound();
    });
</script>
```

**Verificação:**
- [ ] Toast HTML adicionado ao template
- [ ] Função `showNotificationToast()` funciona
- [ ] Toast desaparece após 5 segundos

---

#### 1.6 Testes e Validação

**Fluxo de teste:**

1. Agente A abre chat da Conversa #1
2. Agente B envia mensagem via WhatsApp CLI ou cliente real:
   ```bash
   curl -X POST https://graph.facebook.com/v23.0/{PHONE_NUMBER_ID}/messages \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"messaging_product":"whatsapp","to":"554197796908","type":"text","text":{"body":"Teste"}}'
   ```
3. Webhook processa → Event disparado
4. **Resultado esperado:**
   - [ ] Som toca no browser do Agente A
   - [ ] Notificação desktop aparece
   - [ ] Toast inline aparece por 5s
   - [ ] Mensagem aparece no chat em < 1s (via Echo, não apenas poll)
   - [ ] Log: `MessageReceived event dispatched for conversation 1`

**Anti-patterns a evitar:**
- ❌ Serializar `Message` completa no Event (use apenas IDs + strings simples)
- ❌ Broadcast em `messages` channel global (use `conversation.{id}` para privacidade)
- ❌ Reproduzir som sem verificar `Notification.permission`
- ❌ Duplicar mensagens: Echo dispara antes do poll (use `dedupeKey`)

---

### Resultado Phase 1

✅ Agentes recebem notificações em tempo real com som  
✅ Suporta browser minimizado / abas inativas  
✅ Toast visual + desktop notification  
✅ Integrado com webhook existente  

**Tempo estimado:** 3-4 dias

---

## PHASE 2: Relatórios e Dashboard

### Objetivo
Dashboard expandido com **charts, métricas aprofundadas e exportação em CSV/PDF** de conversas e mensagens.

### Arquitetura

```
Database (Conversations, Messages)
    ↓
ReportController (queries agregadas)
    ↓
    ├─→ JSON (gráficos no front)
    └─→ CSV/PDF Download
    
Dashboard.blade.php
    ├─ Charts (Chart.js ou ApexCharts)
    ├─ Filtros (data, agente, status)
    └─ Botão Export
```

### Tasks

#### 2.1 Instalar Chart.js e Biblioteca de PDF

**Arquivo a editar:** `package.json`

```bash
npm install chart.js chartjs-plugin-datalabels jspdf xlsx
```

**Adicionar ao `vite.config.js` (se necessário):**
```javascript
// Geralmente o Vite detecta automaticamente
```

**Verificação:**
- [ ] `npm install` executado sem erros
- [ ] `node_modules/chart.js` existe
- [ ] `node_modules/jspdf` existe
- [ ] `node_modules/xlsx` existe

---

#### 2.2 Criar ReportController com Queries Agregadas

**Arquivo a criar:** `app/Http/Controllers/ReportController.php`

```php
<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET /reports/dashboard-data
    public function dashboardData(Request $request)
    {
        $startDate = $request->query('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->query('end_date', now()->endOfDay());
        $agentId = $request->query('agent_id', null);

        $query = Message::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($agentId) {
            $query->whereHas('conversation', fn($q) => $q->where('assigned_to', $agentId));
        }

        // 1. Mensagens por hora (para gráfico timeline)
        $byHour = Message::selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // 2. Mensagens por tipo (text, image, audio, document)
        $byType = Message::selectRaw('type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('type')
            ->get();

        // 3. Mensagens inbound vs outbound
        $byDirection = Message::selectRaw('direction, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('direction')
            ->get();

        // 4. Status de delivery (sent, delivered, read)
        $byStatus = Message::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('status')
            ->get();

        // 5. Conversas por agente
        $byAgent = Conversation::selectRaw('users.name, COUNT(DISTINCT conversations.id) as count')
            ->leftJoin('users', 'conversations.assigned_to', '=', 'users.id')
            ->whereHas('messages', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('conversations.assigned_to', 'users.name')
            ->get();

        // 6. Top contatos (mais comunicativos)
        $topContacts = Contact::selectRaw('contacts.name, contacts.phone, COUNT(DISTINCT conversations.id) as conversations, COUNT(messages.id) as messages')
            ->leftJoin('conversations', 'contacts.id', '=', 'conversations.contact_id')
            ->leftJoin('messages', 'conversations.id', '=', 'messages.conversation_id')
            ->whereBetween('messages.created_at', [$startDate, $endDate])
            ->groupBy('contacts.id', 'contacts.name', 'contacts.phone')
            ->orderByDesc('messages')
            ->limit(10)
            ->get();

        // 7. Tempo médio de primeira resposta (outbound após inbound)
        $avgResponseTime = Message::selectRaw('AVG(TIMESTAMPDIFF(SECOND, inbound.created_at, outbound.created_at)) as seconds')
            ->from('messages as outbound')
            ->join('messages as inbound', 'outbound.conversation_id', '=', 'inbound.conversation_id')
            ->where('inbound.direction', 'inbound')
            ->where('outbound.direction', 'outbound')
            ->whereBetween('inbound.created_at', [$startDate, $endDate])
            ->whereRaw('outbound.created_at > inbound.created_at')
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->value('seconds');

        return response()->json([
            'by_hour' => $byHour,
            'by_type' => $byType,
            'by_direction' => $byDirection,
            'by_status' => $byStatus,
            'by_agent' => $byAgent,
            'top_contacts' => $topContacts,
            'avg_response_time_seconds' => round($avgResponseTime ?? 0),
            'date_range' => ['start' => $startDate, 'end' => $endDate],
        ]);
    }

    // GET /reports/conversations (com filtros)
    public function conversations(Request $request)
    {
        $query = Conversation::query()
            ->with(['contact', 'assignedUser', 'messages'])
            ->orderByDesc('last_message_at');

        // Filtros
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->query('agent_id')) {
            $query->where('assigned_to', $request->query('agent_id'));
        }
        if ($request->query('priority')) {
            $query->where('priority', $request->query('priority'));
        }
        if ($request->query('start_date')) {
            $query->whereBetween('created_at', [
                $request->query('start_date'),
                $request->query('end_date', now()),
            ]);
        }
        if ($request->query('search')) {
            $search = $request->query('search');
            $query->whereHas('contact', fn($q) => 
                $q->where('name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
            );
        }

        return response()->json($query->paginate(50));
    }

    // POST /reports/export-conversations
    public function exportConversations(Request $request)
    {
        $format = $request->query('format', 'csv'); // csv ou pdf
        
        $conversations = Conversation::with(['contact', 'assignedUser', 'messages'])
            ->when($request->query('status'), fn($q) => $q->where('status', $request->query('status')))
            ->when($request->query('agent_id'), fn($q) => $q->where('assigned_to', $request->query('agent_id')))
            ->orderByDesc('last_message_at')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($conversations);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($conversations);
        }
    }

    protected function exportCsv($conversations)
    {
        $csv = fopen('php://memory', 'r+');
        
        // Cabeçalho
        fputcsv($csv, [
            'ID', 'Contato', 'Telefone', 'Agente Atribuído', 'Status', 
            'Prioridade', 'Mensagens', 'Última Mensagem', 'Criada em'
        ]);

        // Linhas
        foreach ($conversations as $conv) {
            fputcsv($csv, [
                $conv->id,
                $conv->contact->name,
                $conv->contact->phone,
                $conv->assignedUser?->name ?? 'Não atribuído',
                $conv->status,
                $conv->priority,
                $conv->messages->count(),
                $conv->last_message_at?->format('d/m/Y H:i') ?? '-',
                $conv->created_at->format('d/m/Y H:i'),
            ]);
        }

        rewind($csv);
        $csv_contents = stream_get_contents($csv);
        fclose($csv);

        return response($csv_contents, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="conversas_' . date('Y-m-d_His') . '.csv"',
        ]);
    }

    protected function exportPdf($conversations)
    {
        // Usar jsPDF no frontend (mais prático que gerar PDF em PHP)
        // Esta função aqui é placeholder
        return response()->json([
            'message' => 'Use exportação CSV ou implemente com mPDF/TCPDF',
        ]);
    }
}
```

**Rotas a adicionar em `routes/web.php`:**

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard-data', [ReportController::class, 'dashboardData'])->name('reports.dashboard-data');
        Route::get('/conversations', [ReportController::class, 'conversations'])->name('reports.conversations');
        Route::get('/export-conversations', [ReportController::class, 'exportConversations'])->name('reports.export-conversations');
    });
});
```

**Verificação:**
- [ ] Arquivo `app/Http/Controllers/ReportController.php` criado
- [ ] Todos os métodos implementados
- [ ] Rotas adicionadas em `routes/web.php`
- [ ] Test: `php artisan tinker` → `Http::get('/reports/dashboard-data')`
- [ ] Retorna JSON com estrutura esperada

---

#### 2.3 Expandir Dashboard com Charts

**Arquivo a editar:** `resources/views/dashboard.blade.php`

**Estrutura geral:**

```html
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto p-6">
    
    <!-- Filtros -->
    <div class="mb-6 p-4 bg-gray-100 rounded-lg">
        <form id="filterForm" class="grid grid-cols-4 gap-4">
            <input type="date" name="start_date" class="px-3 py-2 border rounded" placeholder="Data inicial">
            <input type="date" name="end_date" class="px-3 py-2 border rounded" placeholder="Data final">
            <select name="agent_id" class="px-3 py-2 border rounded">
                <option value="">Todos os agentes</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Filtrar</button>
        </form>
    </div>

    <!-- KPIs (Cards) -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Total de Mensagens</h3>
            <p id="totalMessages" class="text-3xl font-bold">--</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Conversas Abertas</h3>
            <p id="openConversations" class="text-3xl font-bold">--</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Tempo Médio de Resposta</h3>
            <p id="avgResponseTime" class="text-3xl font-bold">--</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Top Contato</h3>
            <p id="topContact" class="text-lg font-semibold">--</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <!-- Chart 1: Mensagens por Hora (Line Chart) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Mensagens por Hora (últimos 7 dias)</h3>
            <canvas id="messagesByHourChart"></canvas>
        </div>

        <!-- Chart 2: Tipo de Mensagem (Pie Chart) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Distribuição por Tipo</h3>
            <canvas id="messagesByTypeChart"></canvas>
        </div>

        <!-- Chart 3: Inbound vs Outbound (Bar Chart) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Inbound vs Outbound</h3>
            <canvas id="directionChart"></canvas>
        </div>

        <!-- Chart 4: Mensagens por Agente (Bar Chart) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Atividade por Agente</h3>
            <canvas id="byAgentChart"></canvas>
        </div>
    </div>

    <!-- Top Contatos -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Top 10 Contatos</h3>
            <button id="exportBtn" class="px-4 py-2 bg-green-600 text-white rounded">
                📥 Exportar (CSV)
            </button>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-2">Nome</th>
                    <th class="text-left px-4 py-2">Telefone</th>
                    <th class="text-right px-4 py-2">Mensagens</th>
                    <th class="text-right px-4 py-2">Conversas</th>
                </tr>
            </thead>
            <tbody id="topContactsTable">
                <tr><td colspan="4" class="text-center p-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// JavaScript inline para charts e filtros (ver abaixo)
</script>

@endsection
```

**JavaScript (`resources/views/dashboard.blade.php` - seção `<script>`)**

```javascript
let charts = {};

async function loadDashboardData() {
    const params = new URLSearchParams(document.getElementById('filterForm').elements);
    const response = await fetch(`/reports/dashboard-data?${params}`);
    const data = await response.json();

    // 1. Atualizar KPIs
    const totalMessages = data.by_direction.reduce((sum, d) => sum + d.count, 0);
    document.getElementById('totalMessages').textContent = totalMessages;
    document.getElementById('avgResponseTime').textContent = 
        formatSeconds(data.avg_response_time_seconds);
    document.getElementById('topContact').textContent = 
        (data.top_contacts[0]?.name || '--');

    // 2. Renderizar Charts
    renderMessagesByHourChart(data.by_hour);
    renderMessagesByTypeChart(data.by_type);
    renderDirectionChart(data.by_direction);
    renderByAgentChart(data.by_agent);
    renderTopContactsTable(data.top_contacts);
}

function renderMessagesByHourChart(data) {
    const ctx = document.getElementById('messagesByHourChart').getContext('2d');
    
    if (charts.byHour) charts.byHour.destroy();
    
    charts.byHour = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.hour.substring(11, 16)),
            datasets: [{
                label: 'Mensagens',
                data: data.map(d => d.count),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}

function renderMessagesByTypeChart(data) {
    const ctx = document.getElementById('messagesByTypeChart').getContext('2d');
    
    if (charts.byType) charts.byType.destroy();
    
    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'];
    
    charts.byType = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => {
                const map = { text: 'Texto', image: 'Imagem', audio: 'Áudio', 
                             video: 'Vídeo', document: 'Documento', sticker: 'Sticker' };
                return map[d.type] || d.type;
            }),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: colors.slice(0, data.length),
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function renderDirectionChart(data) {
    const ctx = document.getElementById('directionChart').getContext('2d');
    
    if (charts.direction) charts.direction.destroy();
    
    charts.direction = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.direction === 'inbound' ? 'Recebidas' : 'Enviadas'),
            datasets: [{
                label: 'Quantidade',
                data: data.map(d => d.count),
                backgroundColor: data.map(d => d.direction === 'inbound' ? '#10b981' : '#3b82f6'),
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}

function renderByAgentChart(data) {
    const ctx = document.getElementById('byAgentChart').getContext('2d');
    
    if (charts.byAgent) charts.byAgent.destroy();
    
    charts.byAgent = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.name || 'Sem atribuição'),
            datasets: [{
                label: 'Conversas',
                data: data.map(d => d.count),
                backgroundColor: '#f59e0b',
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}

function renderTopContactsTable(topContacts) {
    const tbody = document.getElementById('topContactsTable');
    tbody.innerHTML = topContacts.map(contact => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${contact.name}</td>
            <td class="px-4 py-3 text-gray-600">${contact.phone}</td>
            <td class="px-4 py-3 text-right font-semibold">${contact.messages}</td>
            <td class="px-4 py-3 text-right text-gray-600">${contact.conversations}</td>
        </tr>
    `).join('');
}

function formatSeconds(seconds) {
    if (!seconds) return '--';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs}s`;
}

// Listener para form de filtros
document.getElementById('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    loadDashboardData();
});

// Botão de exportação
document.getElementById('exportBtn').addEventListener('click', async () => {
    const params = new URLSearchParams(document.getElementById('filterForm').elements);
    params.set('format', 'csv');
    window.location.href = `/reports/export-conversations?${params}`;
});

// Carregar ao abrir página
document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
```

**Verificação:**
- [ ] Dashboard carrega sem erros (npm run dev)
- [ ] Charts renderizam com dados
- [ ] Filtros funcionam (data, agente)
- [ ] Exportação CSV baixa arquivo
- [ ] Teste: adicionar mensagens de teste e recarregar

---

#### 2.4 Página de Relatórios Detalhados (opcional)

**Arquivo a criar:** `resources/views/reports/conversations.blade.php`

```html
@extends('layouts.app')

@section('title', 'Relatório de Conversas')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Relatório de Conversas</h1>

    <!-- Filtros e Exportação -->
    <div class="mb-6 p-4 bg-gray-100 rounded-lg">
        <form id="reportFilterForm" class="grid grid-cols-5 gap-4">
            <select name="status" class="px-3 py-2 border rounded">
                <option value="">Todos os status</option>
                <option value="open">Abertas</option>
                <option value="closed">Fechadas</option>
            </select>
            <select name="priority" class="px-3 py-2 border rounded">
                <option value="">Todas as prioridades</option>
                <option value="low">Baixa</option>
                <option value="medium">Média</option>
                <option value="high">Alta</option>
            </select>
            <select name="agent_id" class="px-3 py-2 border rounded">
                <option value="">Todos os agentes</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
            <input type="search" name="search" placeholder="Buscar contato/telefone" class="px-3 py-2 border rounded">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Filtrar</button>
        </form>
        <div class="mt-4">
            <button id="exportCsvBtn" class="px-4 py-2 bg-green-600 text-white rounded mr-2">📥 CSV</button>
            <button id="exportPdfBtn" class="px-4 py-2 bg-red-600 text-white rounded">📋 PDF (via navegador)</button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">Contato</th>
                    <th class="text-left px-4 py-3">Telefone</th>
                    <th class="text-left px-4 py-3">Agente</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Prioridade</th>
                    <th class="text-right px-4 py-3">Mensagens</th>
                    <th class="text-left px-4 py-3">Última Mensagem</th>
                </tr>
            </thead>
            <tbody id="conversationsTable">
                <tr><td colspan="7" class="text-center p-4">Carregando...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div id="pagination" class="mt-6 flex justify-center gap-2"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1/dist/html2canvas.min.js"></script>
<script>
// Carregar relatório inicial e setup filtros
let currentPage = 1;
let currentFilters = {};

async function loadConversations(page = 1) {
    const params = new URLSearchParams({
        page,
        ...Object.fromEntries(new FormData(document.getElementById('reportFilterForm')))
    });

    const response = await fetch(`/reports/conversations?${params}`);
    const data = await response.json();

    renderTable(data.data);
    renderPagination(data);
    currentPage = page;
}

function renderTable(conversations) {
    document.getElementById('conversationsTable').innerHTML = conversations.map(conv => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${conv.contact.name}</td>
            <td class="px-4 py-3 font-mono text-xs">${conv.contact.phone}</td>
            <td class="px-4 py-3">${conv.assigned_user?.name || '-'}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-semibold ${
                    conv.status === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                }">
                    ${conv.status === 'open' ? 'Aberta' : 'Fechada'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-semibold ${
                    conv.priority === 'high' ? 'bg-red-100 text-red-800' : 
                    conv.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-blue-100 text-blue-800'
                }">
                    ${conv.priority?.toUpperCase() || '-'}
                </span>
            </td>
            <td class="px-4 py-3 text-right">${conv.messages?.length || 0}</td>
            <td class="px-4 py-3 text-xs text-gray-600">${
                conv.last_message_at ? new Date(conv.last_message_at).toLocaleDateString('pt-BR') : '-'
            }</td>
        </tr>
    `).join('');
}

function renderPagination(data) {
    const container = document.getElementById('pagination');
    let html = '';
    
    for (let i = 1; i <= data.last_page; i++) {
        const isActive = i === data.current_page;
        html += `
            <button 
                onclick="loadConversations(${i})"
                class="px-3 py-2 rounded ${isActive ? 'bg-blue-600 text-white' : 'bg-gray-200'}"
            >
                ${i}
            </button>
        `;
    }
    
    container.innerHTML = html;
}

// Listeners
document.getElementById('reportFilterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    loadConversations(1);
});

document.getElementById('exportCsvBtn').addEventListener('click', () => {
    const params = new URLSearchParams(
        Object.fromEntries(new FormData(document.getElementById('reportFilterForm')))
    );
    params.set('format', 'csv');
    window.location.href = `/reports/export-conversations?${params}`;
});

// Inicializar
document.addEventListener('DOMContentLoaded', () => loadConversations(1));
</script>

@endsection
```

**Rota a adicionar em `routes/web.php`:**

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/conversations-page', function () {
        $agents = \App\Models\User::all();
        return view('reports.conversations', compact('agents'));
    })->name('reports.conversations-page');
});
```

**Verificação:**
- [ ] Página `/reports/conversations-page` acessível
- [ ] Tabela carrega conversas
- [ ] Filtros funcionam
- [ ] Exportação CSV funciona
- [ ] Paginação funciona

---

### Resultado Phase 2

✅ Dashboard expandido com 4 charts  
✅ KPIs calculados (tempo de resposta, top contatos)  
✅ Exportação CSV de conversas  
✅ Filtros por data, agente, status  
✅ Página de relatórios detalhados  

**Tempo estimado:** 3-4 dias

---

## PHASE 3: Ajustes Técnicos e Performance

### Objetivo
Melhorar **estabilidade, performance e escalabilidade** do sistema.

### Tasks

#### 3.1 Implementar Cache para Queries Pesadas

**Arquivo a editar:** `app/Http/Controllers/DashboardController.php` e `ReportController.php`

**Padrão:**
```php
use Illuminate\Support\Facades\Cache;

// Dashboard queries
public function index()
{
    $conversations = Cache::remember('dashboard:conversations', 300, function () {
        return Conversation::where('status', 'open')->count();
    });

    $totalAgents = Cache::remember('dashboard:agents_online', 300, function () {
        return User::where('status', 'online')->count();
    });
    
    return view('dashboard', compact('conversations', 'totalAgents'));
}
```

**Cache invalidation (invalidar quando webhook processa mensagem):**

```php
// Em WhatsAppService::handleInboundMessage
use Illuminate\Support\Facades\Cache;

// Após salvar mensagem
Cache::forget('dashboard:conversations');
Cache::forget('reports:dashboard-data');
```

**Verificação:**
- [ ] Cache queries tem TTL 5-10 minutos
- [ ] Cache é limpo quando dados mudam (webhook)
- [ ] Teste: ver logs com `php artisan cache:clear` vs com cache

---

#### 3.2 Adicionar Índices no Database

**Arquivo a criar:** `database/migrations/2026_05_21_add_performance_indexes.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index para queries de webhook
        Schema::table('messages', function (Blueprint $table) {
            $table->index('wa_message_id');          // Deduplicação
            $table->index('conversation_id');        // FK queries
            $table->index('created_at');             // Range queries (relatórios)
            $table->index(['created_at', 'direction']); // Compound: 24h + tipo
        });

        // Index para conversas abertas
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('status');                 // WHERE status = 'open'
            $table->index(['assigned_to', 'status']); // Agente + Status
            $table->index('contact_id');             // FK
            $table->index('last_message_at');        // Ordenação
        });

        // Index para contatos
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('phone');                  // Lookup por telefone
            $table->index('created_at');             // KPI novos contatos
        });

        // Index para usuários
        Schema::table('users', function (Blueprint $table) {
            $table->index('status');                 // Online check
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['wa_message_id']);
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'direction']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['contact_id']);
            $table->dropIndex(['last_message_at']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
```

**Executar:**
```bash
php artisan migrate
```

**Verificação:**
- [ ] Migration executada sem erros
- [ ] Índices criados: `SHOW INDEX FROM messages;` no MySQL
- [ ] Queries ficam mais rápidas (verificar com `EXPLAIN`)

---

#### 3.3 Otimizar Webhook Handler (Async via Queue)

**Arquivo a criar:** `app/Jobs/ProcessWhatsAppWebhook.php`

```php
<?php
namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onConnection('database'); // Ou 'sync' para dev
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        try {
            Log::info('[Webhook Job] Processing payload', [
                'object' => $this->payload['object'] ?? null,
            ]);

            WhatsAppService::processWebhook($this->payload);

            Log::info('[Webhook Job] Completed successfully');
        } catch (\Exception $e) {
            Log::error('[Webhook Job] Failed', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);
            throw $e; // Retry
        }
    }

    public function failed(\Exception $exception): void
    {
        Log::critical('[Webhook Job] Failed after retries', [
            'error' => $exception->getMessage(),
        ]);
    }
}
```

**Arquivo a editar:** `app/Http/Controllers/WebhookController.php`

```php
use App\Jobs\ProcessWhatsAppWebhook;

public function handle(Request $request)
{
    // Validar assinatura (se quiser adicionar segurança)
    // ...

    Log::info('[WebhookController] Received payload');

    // Dispatch async job
    ProcessWhatsAppWebhook::dispatch($request->all());

    // Responder imediatamente (202 Accepted)
    return response('Accepted', 202);
}
```

**Configurar queue driver em `.env`:**

```env
QUEUE_CONNECTION=database  # ou 'sync' para dev/teste rápido
```

**Criar jobs table:**

```bash
php artisan queue:table
php artisan migrate
```

**Para dev/teste (modo sync - imediato):**

```env
QUEUE_CONNECTION=sync
```

**Para produção (modo async - background):**

```bash
php artisan queue:work --queue=webhooks
```

**Verificação:**
- [ ] Job criado em `app/Jobs/ProcessWhatsAppWebhook.php`
- [ ] WebhookController dispara job
- [ ] `php artisan queue:work` roda sem erros
- [ ] Webhook responde em < 100ms (fila em background)
- [ ] Log: `[Webhook Job] Processing payload`

---

#### 3.4 Adicionar Query Logging e Slow Query Detection

**Arquivo a editar:** `app/Providers/AppServiceProvider.php`

```php
<?php
namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Log queries mais lentas que 500ms
        DB::listen(function (QueryExecuted $query) {
            if ($query->time > 500) { // milisegundos
                Log::warning('[Slow Query] ' . round($query->time) . 'ms', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                ]);
            }
        });

        // Em dev, log TODAS as queries
        if (config('app.debug')) {
            DB::listen(function (QueryExecuted $query) {
                // Opcional: comentar para reduzir ruído
                // Log::debug('[Query] ' . round($query->time, 2) . 'ms: ' . $query->sql);
            });
        }
    }
}
```

**Verificação:**
- [ ] AppServiceProvider tem listener
- [ ] Teste: `php artisan tinker` → executar query
- [ ] Logs mostram queries lentas (> 500ms)
- [ ] StorageFacade em `storage/logs/laravel.log`

---

#### 3.5 Configurar Sentry para Error Tracking (opcional)

**Instalar:**
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish
```

**Configurar em `.env`:**
```env
SENTRY_LARAVEL_DSN=https://xxxx@sentry.io/xxxx
SENTRY_ENVIRONMENT=production
```

**Verificação:**
- [ ] Erros aparecem no Sentry dashboard
- [ ] Erro de teste: `throw new \Exception('test');`

---

#### 3.6 Adicionar Rate Limiting ao Webhook

**Arquivo a editar:** `app/Http/Middleware/ThrottleRequests.php` (ou criar custom)

**Ou via `routes/api.php`:**

```php
Route::post('/api/webhook/whatsapp', [WebhookController::class, 'handle'])
    ->middleware('throttle:60,1'); // 60 requisições por minuto
```

**Verificação:**
- [ ] Rota tem middleware de rate limit
- [ ] Teste: 61+ requests em 60s → HTTP 429 Too Many Requests

---

#### 3.7 Melhorar Error Handling no WhatsAppService

**Arquivo a editar:** `app/Services/WhatsAppService.php`

**Adicionar retry logic:**

```php
private function makeRequest(string $method, string $endpoint, array $data = []): ?array
{
    $maxRetries = 3;
    $delay = 1000; // ms

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $response = $this->client->request($method, $endpoint, [
                'json' => $data,
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($attempt < $maxRetries) {
                Log::warning("[WhatsApp API] Retry $attempt/$maxRetries", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
                usleep($delay * 1000);
                continue;
            }

            Log::error("[WhatsApp API] Failed after $maxRetries retries", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody(),
            ]);

            return null;
        }
    }
}
```

**Verificação:**
- [ ] Retry logic implementado
- [ ] Teste: Simular falha da API, verificar retries nos logs

---

### Resultado Phase 3

✅ Queries cacheadas (5-10x mais rápidas)  
✅ Índices de database otimizados  
✅ Webhook processado async (response < 100ms)  
✅ Query logging para detecção de gargalos  
✅ Error tracking com Sentry (opcional)  
✅ Rate limiting no webhook  
✅ Retry logic na API WhatsApp  

**Tempo estimado:** 2-3 dias

---

## 🎯 Cronograma Resumido

| Fase | Duração | Início | Fim |
|------|---------|--------|-----|
| **1. Notificações + Áudio** | 3-4 dias | Dia 1 | Dia 4 |
| **2. Relatórios + Dashboard** | 3-4 dias | Dia 5 | Dia 8 |
| **3. Ajustes Técnicos** | 2-3 dias | Dia 9 | Dia 11 |
| **Testes Integrados** | 1 dia | Dia 12 | Dia 12 |
| **Deploy + Docs** | 0,5 dias | Dia 13 | Dia 13 |

**Total: ~13 dias (2-3 semanas)**

---

## ✅ Checklist Final (Phase 4: Validation)

### Notificações
- [ ] Som toca em navegador (Firefox, Chrome, Safari, Edge)
- [ ] Desktop notification pede permissão
- [ ] Funciona com chat minimizado
- [ ] Não duplica notificações se message já está no chat
- [ ] Testa com Pusher real (não dev)

### Relatórios
- [ ] Dashboard carrega em < 3s
- [ ] Charts renderizam corretamente
- [ ] Filtros funcionam (data, agente, status)
- [ ] Exportação CSV abre em Excel/Sheets
- [ ] Paginação funciona

### Performance
- [ ] Webhook responde em < 200ms
- [ ] Dashboard queries < 2s (com cache)
- [ ] Nenhuma N+1 query (verificar com Laravel Debugbar)
- [ ] Índices estão presentes no banco
- [ ] Logs não explodem em tamanho

### Segurança
- [ ] Webhook verifica token da Meta
- [ ] Rate limiting ativo
- [ ] Dados sensíveis não logados (tokens, senhas)
- [ ] CSRF protection intacta
- [ ] SQL injection impossível (usar binding)

---

## 📚 Documentação a Atualizar

1. **AJUDA_WHATSAPP.md** - adicionar seções:
   - Notificações (som, permissões)
   - Relatórios (como exportar)
   - Performance (cache, índices)

2. **README.md** - incluir:
   - Como ativar notificações
   - Como gerar relatórios
   - Credenciais Pusher

3. **DEPLOYMENT.md** (novo) - passos para produção:
   - Instalar ffmpeg, Pusher
   - Migrar database (índices)
   - Configurar queue worker
   - Ativar Sentry

---

## 📞 Contatos / Referências

- **Pusher Free Tier:** https://pusher.com (100 conexões, 200 eventos/dia)
- **Chart.js Docs:** https://www.chartjs.org/docs/latest/
- **Laravel Broadcasting:** https://laravel.com/docs/11.x/broadcasting
- **Laravel Queues:** https://laravel.com/docs/11.x/queues

---

**Plano criado: 21 de maio de 2026**  
**Status: Pronto para execução**
