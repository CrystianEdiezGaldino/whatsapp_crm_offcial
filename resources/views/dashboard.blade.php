@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Top Bar -->
<header class="flex justify-between items-center h-16 px-6 w-full sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-outline-variant">
    <div class="flex items-center gap-6">
        <h2 class="text-xl font-extrabold text-primary">WhatsApp ERP</h2>
        <div class="relative flex items-center">
            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-lg">search</span>
            <input class="pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-full text-sm w-64 focus:outline-none focus:ring-1 focus:ring-secondary" placeholder="Buscar chats ou agentes..." type="text">
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 px-2 py-1 bg-secondary-container/20 rounded-full border border-secondary-container/30">
            <span class="w-2 h-2 bg-secondary rounded-full"></span>
            <span class="text-xs font-semibold text-on-secondary-container">Online</span>
        </div>
        <div class="h-8 w-px bg-outline-variant"></div>
        <div class="flex items-center gap-2">
            <div class="text-right">
                <p class="text-xs font-semibold text-on-surface">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-on-surface-variant">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Agente' }}</p>
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Body -->
<div class="p-6 overflow-y-auto custom-scrollbar flex-1">
    <!-- Filtros -->
    <div class="mb-6 p-4 bg-white rounded-xl border border-outline-variant shadow-sm">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="date" id="startDate" name="start_date" class="px-3 py-2 border border-outline-variant rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-secondary" placeholder="Data inicial">
            <input type="date" id="endDate" name="end_date" class="px-3 py-2 border border-outline-variant rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-secondary" placeholder="Data final">
            <select id="agentSelect" name="agent_id" class="px-3 py-2 border border-outline-variant rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-secondary">
                <option value="">Todos os agentes</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold text-sm active:scale-95 transition-transform">Filtrar</button>
                <button type="reset" class="px-4 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm active:scale-95 transition-transform">Limpar</button>
            </div>
        </form>
    </div>
    <!-- Metrics KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-secondary transition-colors">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary p-2 bg-slate-50 rounded-lg">forum</span>
            </div>
            <p class="text-xs font-semibold text-on-surface-variant mt-4 uppercase">Total Mensagens</p>
            <p id="totalMessages" class="text-3xl font-bold text-on-surface">--</p>
        </div>
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-secondary transition-colors">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary p-2 bg-slate-50 rounded-lg">chat</span>
            </div>
            <p class="text-xs font-semibold text-on-surface-variant mt-4 uppercase">Chats Abertos</p>
            <p id="openConversations" class="text-3xl font-bold text-on-surface">{{ $stats['open_conversations'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-secondary transition-colors">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary p-2 bg-slate-50 rounded-lg">timer</span>
            </div>
            <p class="text-xs font-semibold text-on-surface-variant mt-4 uppercase">Tempo Médio Resposta</p>
            <p id="avgResponseTime" class="text-3xl font-bold text-on-surface">--</p>
        </div>
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-secondary transition-colors">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary p-2 bg-slate-50 rounded-lg">person_add</span>
            </div>
            <p class="text-xs font-semibold text-on-surface-variant mt-4 uppercase">Top Contato</p>
            <p id="topContact" class="text-lg font-bold text-on-surface">--</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Chart 1: Mensagens por Hora -->
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
            <h3 class="text-lg font-semibold text-on-surface mb-4">Mensagens por Hora</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="messagesByHourChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Tipo de Mensagem -->
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
            <h3 class="text-lg font-semibold text-on-surface mb-4">Distribuição por Tipo</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="messagesByTypeChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Inbound vs Outbound -->
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
            <h3 class="text-lg font-semibold text-on-surface mb-4">Inbound vs Outbound</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="directionChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Atividade por Agente -->
        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
            <h3 class="text-lg font-semibold text-on-surface mb-4">Atividade por Agente</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="byAgentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Contatos -->
    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-on-surface">Top 10 Contatos</h3>
            <a href="{{ route('conversations.index') }}?view=reports" class="text-sm font-semibold text-secondary hover:underline">Ver Relatório Completo</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-slate-50">
                    <tr>
                        <th class="text-left px-4 py-3">Nome</th>
                        <th class="text-left px-4 py-3">Telefone</th>
                        <th class="text-right px-4 py-3">Mensagens</th>
                        <th class="text-right px-4 py-3">Conversas</th>
                    </tr>
                </thead>
                <tbody id="topContactsTable">
                    <tr><td colspan="4" class="text-center p-4 text-on-surface-variant">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- My Chats -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-sm flex flex-col">
            <div class="p-6 border-b border-outline-variant flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-on-surface">Meus Atendimentos</h3>
                    <p class="text-xs text-on-surface-variant">{{ $myChats->count() }} chats ativos</p>
                </div>
                <a href="{{ route('conversations.index') }}" class="text-sm font-semibold text-secondary hover:underline">Ver Todos</a>
            </div>
            <div class="overflow-x-auto">
                @if($myChats->count() > 0)
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-outline-variant">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Cliente</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Ultima Mensagem</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Tempo</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($myChats->take(5) as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-sm text-on-primary-fixed">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface">{{ $chat->contact->name }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-on-surface-variant truncate max-w-[250px]">
                                    {{ $chat->lastMessage?->content ?? 'Sem mensagens' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                {{ $chat->last_message_at?->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('conversations.index', ['conversation' => $chat->id]) }}" class="bg-primary text-on-primary text-xs font-semibold px-4 py-1.5 rounded-lg active:scale-95 transition-transform">Abrir</a>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-8 text-center text-on-surface-variant text-sm">
                    Nenhum atendimento ativo no momento.
                </div>
                @endif
            </div>
        </div>

        <!-- Online Agents -->
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm flex flex-col">
            <div class="p-6 border-b border-outline-variant flex justify-between items-center">
                <h3 class="text-lg font-semibold text-on-surface">Agentes Online</h3>
                <span class="text-xs font-semibold text-on-surface-variant">{{ $onlineAgents->count() }}</span>
            </div>
            <div class="p-4 flex flex-col gap-2 max-h-[400px] overflow-y-auto custom-scrollbar">
                @foreach($onlineAgents as $agent)
                <div class="flex items-center gap-4 p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-sm text-on-primary-fixed">
                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-secondary rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-on-surface truncate">{{ $agent->name }}</p>
                        <p class="text-xs text-secondary">Ativo agora</p>
                    </div>
                    <span class="text-xs text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $agent->conversations_count }} chats</span>
                </div>
                @endforeach
                @if($onlineAgents->isEmpty())
                <p class="text-sm text-on-surface-variant text-center py-4">Nenhum agente online</p>
                @endif
            </div>
        </div>

        <!-- Pending Chats -->
        <div class="lg:col-span-3 bg-white rounded-xl border border-outline-variant shadow-sm flex flex-col">
            <div class="p-6 border-b border-outline-variant flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-on-surface">Atendimentos Pendentes</h3>
                    <p class="text-xs text-on-surface-variant">Chats aguardando distribuicao</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($pendingChats->count() > 0)
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-outline-variant">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Cliente</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Ultima Mensagem</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase">Tempo de Espera</th>
                            <th class="px-6 py-3 text-xs font-semibold text-on-surface-variant uppercase text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($pendingChats as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center font-bold text-sm text-on-secondary-fixed">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface">{{ $chat->contact->name }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-on-surface-variant truncate max-w-[300px]">
                                    "{{ $chat->lastMessage?->content ?? 'Aguardando...' }}"
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm @if($chat->last_message_at?->diffInMinutes() > 10) text-error font-semibold @else text-on-surface-variant @endif">
                                {{ $chat->last_message_at?->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('conversations.assign', $chat) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="bg-primary text-on-primary text-xs font-semibold px-4 py-1.5 rounded-lg active:scale-95 transition-transform">Assumir</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-8 text-center text-on-surface-variant text-sm">
                    Nenhum atendimento pendente.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
let charts = {};

async function loadDashboardData() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);

    try {
        const response = await fetch(`/reports/dashboard-data?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        const text = await response.text();
        const data = JSON.parse(text);

        // Atualizar KPIs
        const totalMessages = data.by_direction.reduce((sum, d) => sum + d.count, 0);
        document.getElementById('totalMessages').textContent = totalMessages.toLocaleString('pt-BR');
        document.getElementById('avgResponseTime').textContent = formatSeconds(data.avg_response_time_seconds);
        document.getElementById('topContact').textContent = (data.top_contacts[0]?.name || '--');

        // Renderizar charts
        renderMessagesByHourChart(data.by_hour);
        renderMessagesByTypeChart(data.by_type);
        renderDirectionChart(data.by_direction);
        renderByAgentChart(data.by_agent);
        renderTopContactsTable(data.top_contacts);
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    }
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
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderMessagesByTypeChart(data) {
    const ctx = document.getElementById('messagesByTypeChart').getContext('2d');

    if (charts.byType) charts.byType.destroy();

    const typeLabels = {
        'text': 'Texto',
        'image': 'Imagem',
        'audio': 'Áudio',
        'video': 'Vídeo',
        'document': 'Documento',
        'sticker': 'Sticker'
    };

    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4'];

    charts.byType = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => typeLabels[d.type] || d.type),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: colors.slice(0, data.length),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
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
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
}

function renderTopContactsTable(topContacts) {
    const tbody = document.getElementById('topContactsTable');
    tbody.innerHTML = topContacts.map(contact => `
        <tr class="border-b hover:bg-slate-50">
            <td class="px-4 py-3">${contact.name}</td>
            <td class="px-4 py-3 text-gray-600 font-mono text-xs">${contact.phone}</td>
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

// Reset form
document.getElementById('filterForm').addEventListener('reset', () => {
    setTimeout(() => loadDashboardData(), 0);
});

// Carregar ao abrir página
document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endpush
