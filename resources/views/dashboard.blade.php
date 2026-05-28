@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(225, 227, 232, 0.6);
    }
    .glass-card:hover {
        transform: translateY(-4px);
        transition: all 0.3s ease-out;
    }
</style>

<!-- Top Bar -->
<header class="flex justify-between items-center h-16 px-8 w-full sticky top-0 z-40 bg-surface border-b border-surface-container-highest">
    <div class="flex items-center gap-8">
        <h2 class="text-xl font-bold text-primary font-headline">SisChat Dashboard</h2>
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
            <input class="pl-10 pr-4 py-2 bg-surface-container border-none rounded-xl focus:ring-2 focus:ring-primary-container transition-all text-sm w-80" placeholder="Buscar chats ou agentes..." type="text">
        </div>
    </div>
    <div class="flex items-center gap-6">
        <div class="hidden md:flex items-center gap-6 text-on-surface-variant font-label-lg">
            <a class="text-primary font-bold border-b-2 border-primary pb-1" href="#">Visão Geral</a>
            <a class="hover:text-primary transition-colors" href="#">Relatórios</a>
        </div>
        <div class="h-6 w-px bg-surface-container-highest"></div>
        <div class="flex items-center gap-4">
            <button class="p-2 hover:bg-surface-container-low rounded-full transition-all text-on-surface-variant">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <div class="flex items-center gap-2 pl-4">
                <div class="text-right">
                    <p class="font-label-lg text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-outline uppercase tracking-wider">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Agente' }}</p>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Body -->
<div class="p-8 overflow-y-auto custom-scrollbar flex-1 space-y-8 max-w-[1600px]">
    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6 shadow-sm">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="date" id="startDate" name="start_date" class="px-4 py-2 border border-outline-variant rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-container" placeholder="Data inicial">
            <input type="date" id="endDate" name="end_date" class="px-4 py-2 border border-outline-variant rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-container" placeholder="Data final">
            <select id="agentSelect" name="agent_id" class="px-4 py-2 border border-outline-variant rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-container">
                <option value="">Todos os agentes</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-on-primary rounded-xl font-semibold text-sm active:scale-95 transition-transform">Filtrar</button>
                <button type="reset" class="px-4 py-2 bg-surface-container border border-outline-variant rounded-xl text-sm active:scale-95 transition-transform">Limpar</button>
            </div>
        </form>
    </div>
    <!-- Metrics KPIs Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Mensagens -->
        <div class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-primary-fixed text-primary rounded-xl">
                    <span class="material-symbols-outlined">forum</span>
                </div>
                <span class="text-on-tertiary-container bg-tertiary-fixed-dim/30 px-2 py-1 rounded-full text-[10px] font-bold" id="msgBadge">+12%</span>
            </div>
            <p class="text-outline font-label-md uppercase tracking-widest text-[10px]">Total Mensagens</p>
            <p id="totalMessages" class="font-headline-lg text-headline-lg mt-2">--</p>
            <div class="mt-4 h-1 w-full bg-surface-container rounded-full overflow-hidden">
                <div class="h-full bg-primary w-3/4"></div>
            </div>
        </div>

        <!-- Card 2: Tempo Médio Resposta -->
        <div class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-secondary-fixed text-secondary rounded-xl">
                    <span class="material-symbols-outlined">schedule</span>
                </div>
                <span class="text-error bg-error-container px-2 py-1 rounded-full text-[10px] font-bold" id="timeBadge">-5%</span>
            </div>
            <p class="text-outline font-label-md uppercase tracking-widest text-[10px]">Tempo Médio Resposta</p>
            <p id="avgResponseTime" class="font-headline-lg text-headline-lg mt-2">--</p>
            <div class="mt-4 flex items-center space-x-1">
                <div class="h-4 w-1 bg-secondary rounded-full opacity-30"></div>
                <div class="h-6 w-1 bg-secondary rounded-full opacity-50"></div>
                <div class="h-8 w-1 bg-secondary rounded-full"></div>
                <div class="h-5 w-1 bg-secondary rounded-full opacity-60"></div>
                <div class="h-3 w-1 bg-secondary rounded-full opacity-20"></div>
            </div>
        </div>

        <!-- Card 3: Chats Abertos -->
        <div class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-tertiary-fixed-dim text-on-tertiary-fixed-variant rounded-xl">
                    <span class="material-symbols-outlined">chat</span>
                </div>
                <span class="text-on-tertiary-container bg-tertiary-fixed-dim/30 px-2 py-1 rounded-full text-[10px] font-bold">LIVE</span>
            </div>
            <p class="text-outline font-label-md uppercase tracking-widest text-[10px]">Chats Abertos</p>
            <p id="openConversations" class="font-headline-lg text-headline-lg mt-2">{{ $stats['open_conversations'] }}</p>
            <div class="mt-4 flex -space-x-2">
                <div class="w-6 h-6 rounded-full border-2 border-white bg-primary-fixed"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-secondary-fixed"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-tertiary-fixed"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-gray-300 flex items-center justify-center text-[8px] font-bold">+8</div>
            </div>
        </div>

        <!-- Card 4: Taxa de Satisfação -->
        <div class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-surface-container-highest text-on-surface-variant rounded-xl">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <span class="text-on-tertiary-container bg-tertiary-fixed-dim/30 px-2 py-1 rounded-full text-[10px] font-bold">98%</span>
            </div>
            <p class="text-outline font-label-md uppercase tracking-widest text-[10px]">Satisfação</p>
            <p class="font-headline-lg text-headline-lg mt-2">4.9/5.0</p>
            <div class="mt-4 flex space-x-1">
                <span class="material-symbols-outlined text-tertiary-container text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-tertiary-container text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-tertiary-container text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-tertiary-container text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-tertiary-container text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Message Volume Chart -->
        <div class="lg:col-span-2 glass-card rounded-xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="font-headline-md text-headline-md">Volume de Mensagens</h3>
                    <p class="text-outline text-sm">Distribuição horária de mensagens recebidas</p>
                </div>
                <div class="flex bg-surface-container rounded-lg p-1">
                    <button class="px-3 py-1 bg-white shadow-sm rounded-md text-xs font-bold">24h</button>
                    <button class="px-3 py-1 text-xs hover:bg-white/50 rounded-md transition-all">7d</button>
                    <button class="px-3 py-1 text-xs hover:bg-white/50 rounded-md transition-all">30d</button>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="messagesByHourChart"></canvas>
            </div>
        </div>

        <!-- Channel Distribution -->
        <div class="glass-card rounded-xl p-8 shadow-sm">
            <h3 class="font-headline-md text-headline-md mb-8">Distribuição por Canal</h3>
            <div class="space-y-6">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-primary-container"></div>
                            <span class="font-label-lg">WhatsApp</span>
                        </div>
                        <span class="font-bold">64%</span>
                    </div>
                    <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-primary-container w-[64%]"></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-on-tertiary-container"></div>
                            <span class="font-label-lg">Outros</span>
                        </div>
                        <span class="font-bold">36%</span>
                    </div>
                    <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-on-tertiary-container w-[36%]"></div>
                    </div>
                </div>
                <div class="mt-12 flex justify-center">
                    <div class="relative w-40 h-40">
                        <canvas id="messagesByTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Chart 3: Inbound vs Outbound -->
        <div class="glass-card rounded-xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="font-headline-md text-headline-md">Fluxo de Mensagens</h3>
                    <p class="text-outline text-sm">Comparativo inbound vs outbound</p>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="directionChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Atividade por Agente -->
        <div class="glass-card rounded-xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="font-headline-md text-headline-md">Atividade por Agente</h3>
                    <p class="text-outline text-sm">Ranking de conversas</p>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="byAgentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Contatos -->
    <div class="glass-card rounded-xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-surface-container-highest flex justify-between items-center">
            <div>
                <h3 class="font-headline-md text-headline-md">Top 10 Contatos</h3>
                <p class="text-outline text-sm">Contatos com mais interações</p>
            </div>
            <a href="{{ route('conversations.index') }}?view=reports" class="text-primary font-bold hover:underline flex items-center space-x-1">
                <span>Ver Todos</span>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low text-on-surface-variant font-label-lg">
                    <tr>
                        <th class="px-8 py-4">Nome</th>
                        <th class="px-8 py-4">Telefone</th>
                        <th class="px-8 py-4 text-right">Mensagens</th>
                        <th class="px-8 py-4 text-right">Conversas</th>
                    </tr>
                </thead>
                <tbody id="topContactsTable" class="divide-y divide-surface-container-highest">
                    <tr><td colspan="4" class="text-center p-4 text-on-surface-variant">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- My Chats -->
        <div class="lg:col-span-2 glass-card rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container-highest flex justify-between items-center">
                <div>
                    <h3 class="font-headline-md text-headline-md">Meus Atendimentos</h3>
                    <p class="text-outline text-sm">{{ $myChats->count() }} chats ativos</p>
                </div>
                <a href="{{ route('conversations.index') }}" class="text-primary font-bold hover:underline flex items-center space-x-1">
                    <span>Ver Todos</span>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                @if($myChats->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-low text-on-surface-variant font-label-lg">
                        <tr>
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Última Mensagem</th>
                            <th class="px-8 py-4">Tempo</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest">
                        @foreach($myChats->take(5) as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-surface-container-lowest transition-all group">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-primary font-bold">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $chat->contact->name }}</p>
                                        <p class="text-[10px] text-outline">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-sm text-on-surface-variant truncate max-w-[250px]">
                                    {{ $chat->lastMessage?->content ?? 'Sem mensagens' }}
                                </p>
                            </td>
                            <td class="px-8 py-4 text-sm">{{ $chat->last_message_at?->diffForHumans() }}</td>
                            <td class="px-8 py-4 text-right">
                                <a href="{{ route('conversations.index', ['conversation' => $chat->id]) }}" class="bg-primary text-on-primary text-xs font-semibold px-4 py-1.5 rounded-xl active:scale-95 transition-transform">Abrir</a>
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
        <div class="glass-card rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container-highest flex justify-between items-center">
                <div>
                    <h3 class="font-headline-md text-headline-md">Agentes Online</h3>
                    <p class="text-outline text-sm">{{ $onlineAgents->count() }} agentes conectados</p>
                </div>
            </div>
            <div class="p-4 flex flex-col gap-2 max-h-[400px] overflow-y-auto custom-scrollbar">
                @foreach($onlineAgents as $agent)
                <div class="flex items-center gap-4 p-3 hover:bg-surface-container-low rounded-lg transition-colors">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-sm text-primary">
                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-tertiary-container rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm truncate">{{ $agent->name }}</p>
                        <p class="text-[10px] text-tertiary">Online</p>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-1 rounded">{{ $agent->conversations_count }} chats</span>
                </div>
                @endforeach
                @if($onlineAgents->isEmpty())
                <p class="text-sm text-on-surface-variant text-center py-4">Nenhum agente online</p>
                @endif
            </div>
        </div>

        <!-- Pending Chats -->
        <div class="lg:col-span-3 glass-card rounded-xl shadow-sm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container-highest flex justify-between items-center">
                <div>
                    <h3 class="font-headline-md text-headline-md">Atendimentos Pendentes</h3>
                    <p class="text-outline text-sm">Chats aguardando distribuição</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($pendingChats->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-low text-on-surface-variant font-label-lg">
                        <tr>
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Última Mensagem</th>
                            <th class="px-8 py-4">Tempo de Espera</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest">
                        @foreach($pendingChats as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-surface-container-lowest transition-all group">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center font-bold text-sm text-secondary">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $chat->contact->name }}</p>
                                        <p class="text-[10px] text-outline">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-sm text-on-surface-variant truncate max-w-[300px]">
                                    "{{ $chat->lastMessage?->content ?? 'Aguardando...' }}"
                                </p>
                            </td>
                            <td class="px-8 py-4 text-sm @if($chat->last_message_at?->diffInMinutes() > 10) text-error font-bold @else text-on-surface-variant @endif">
                                {{ $chat->last_message_at?->diffForHumans() }}
                            </td>
                            <td class="px-8 py-4 text-right">
                                <form method="POST" action="{{ route('conversations.assign', $chat) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="bg-primary text-on-primary text-xs font-semibold px-4 py-1.5 rounded-xl active:scale-95 transition-transform">Assumir</button>
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
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        // Validar dados
        if (!data || !data.by_direction) {
            console.warn('Dados inválidos retornados:', data);
            return;
        }

        // Atualizar KPIs
        const totalMessages = data.by_direction.reduce((sum, d) => sum + d.count, 0);
        document.getElementById('totalMessages').textContent = totalMessages.toLocaleString('pt-BR');
        document.getElementById('avgResponseTime').textContent = formatSeconds(data.avg_response_time_seconds);
        const topContactEl = document.getElementById('topContact');
        if (topContactEl) {
            topContactEl.textContent = data.top_contacts?.[0]?.name || '--';
        }

        // Renderizar charts apenas se tiver dados
        if (data.by_hour && data.by_hour.length > 0) {
            renderMessagesByHourChart(data.by_hour);
        } else {
            console.warn('Sem dados de mensagens por hora');
        }

        if (data.by_type && data.by_type.length > 0) {
            renderMessagesByTypeChart(data.by_type);
        }

        if (data.by_direction && data.by_direction.length > 0) {
            renderDirectionChart(data.by_direction);
        }

        if (data.by_agent && data.by_agent.length > 0) {
            renderByAgentChart(data.by_agent);
        }

        if (data.top_contacts && data.top_contacts.length > 0) {
            renderTopContactsTable(data.top_contacts);
        }

    } catch (error) {
        console.error('Erro ao carregar dados do dashboard:', error);
        console.error('Detalhes:', error.message);
    }
}

function renderMessagesByHourChart(data) {
    const canvas = document.getElementById('messagesByHourChart');
    const ctx = canvas.getContext('2d');

    if (charts.byHour) charts.byHour.destroy();

    // Criar gradiente para o fill
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0, 'rgba(20, 44, 142, 0.15)');
    gradient.addColorStop(1, 'rgba(20, 44, 142, 0)');

    charts.byHour = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.hour.substring(11, 16)),
            datasets: [{
                label: 'Mensagens',
                data: data.map(d => d.count),
                borderColor: '#142c8e',
                backgroundColor: gradient,
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#001769',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#142c8e',
                pointHoverBorderWidth: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                filler: { propagate: true },
                tooltip: {
                    backgroundColor: 'rgba(0, 23, 105, 0.9)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    borderColor: '#142c8e',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' mensagens';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                }
            }
        }
    });
}

function renderMessagesByTypeChart(data) {
    const canvas = document.getElementById('messagesByTypeChart');
    const ctx = canvas.getContext('2d');

    if (charts.byType) charts.byType.destroy();

    const typeLabels = {
        'text': 'Texto',
        'image': 'Imagem',
        'audio': 'Áudio',
        'video': 'Vídeo',
        'document': 'Documento',
        'sticker': 'Sticker'
    };

    const colors = ['#142c8e', '#46b575', '#4d5e83', '#8bf9b2', '#879aff', '#dee1ff'];

    // Criar gradientes para cada cor
    const gradients = colors.map((color, i) => {
        const grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
        grad.addColorStop(0, color);
        grad.addColorStop(1, color + '99');
        return grad;
    });

    charts.byType = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => typeLabels[d.type] || d.type),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: colors.slice(0, data.length),
                borderColor: '#ffffff',
                borderWidth: 3,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: 600 },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        pointRadius: 6
                    }
                }
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
                backgroundColor: data.map(d => d.direction === 'inbound' ? '#46b575' : '#142c8e'),
                borderColor: data.map(d => d.direction === 'inbound' ? '#004122' : '#001769'),
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 50,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 23, 105, 0.9)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' mensagens';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                }
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
                backgroundColor: '#142c8e',
                borderColor: '#001769',
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 35,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 23, 105, 0.9)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' conversas';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                },
                y: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { size: 11, weight: 500 } }
                }
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
