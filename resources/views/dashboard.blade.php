@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- Top Bar -->
<header class="flex justify-between items-center h-16 px-8 w-full sticky top-0 z-40 bg-white border-b border-gray-200">
    <div class="flex items-center gap-8">
        <h2 class="text-xl font-bold text-[#1DA85A]">SisZap Dashboard</h2>
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg">search</span>
            <input class="pl-10 pr-4 py-2 bg-gray-100 border-none rounded-xl focus:ring-2 focus:ring-green-500 transition-all text-sm w-80" placeholder="Buscar chats ou agentes..." type="text">
        </div>
    </div>
    <div class="flex items-center gap-6">
        <div class="hidden md:flex items-center gap-6 text-gray-600">
            <a class="text-[#1DA85A] font-bold border-b-2 border-[#1DA85A] pb-1" href="#">Visão Geral</a>
            <a class="hover:text-[#1DA85A] transition-colors" href="#">Relatórios</a>
        </div>
        <div class="h-6 w-px bg-gray-200"></div>
        <div class="flex items-center gap-4">
            <button class="p-2 hover:bg-gray-100 rounded-full transition-all text-gray-600">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <div class="flex items-center gap-2 pl-4">
                <div class="text-right">
                    <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Agente' }}</p>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Body -->
<div class="p-8 overflow-y-auto custom-scrollbar flex-1 space-y-8 max-w-[1600px]">
    <!-- Filtros -->
    <div class="card-nm">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="date" id="startDate" name="start_date" class="input-nm h-[42px]" placeholder="Data inicial">
            <input type="date" id="endDate" name="end_date" class="input-nm h-[42px]" placeholder="Data final">
            <select id="agentSelect" name="agent_id" class="select-nm h-[42px]">
                <option value="">Todos os agentes</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-nm-primary flex-1">Filtrar</button>
                <button type="reset" class="btn-nm-secondary">Limpar</button>
            </div>
        </form>
    </div>
    <!-- Metrics KPIs Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Mensagens -->
        <div class="card-nm">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-green-100 text-[#1DA85A] rounded-xl">
                    <span class="material-symbols-outlined">forum</span>
                </div>
                <span class="text-green-700 bg-green-50 px-2 py-1 rounded-full text-[10px] font-bold" id="msgBadge">+12%</span>
            </div>
            <p class="text-gray-500 uppercase tracking-widest text-[10px]">Total Mensagens</p>
            <p id="totalMessages" class="text-2xl font-bold mt-2">--</p>
            <div class="mt-4 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-[#1DA85A] w-3/4"></div>
            </div>
        </div>

        <!-- Card 2: Tempo Médio Resposta -->
        <div class="card-nm">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                    <span class="material-symbols-outlined">schedule</span>
                </div>
                <span class="text-red-700 bg-red-50 px-2 py-1 rounded-full text-[10px] font-bold" id="timeBadge">-5%</span>
            </div>
            <p class="text-gray-500 uppercase tracking-widest text-[10px]">Tempo Médio Resposta</p>
            <p id="avgResponseTime" class="text-2xl font-bold mt-2">--</p>
            <div class="mt-4 flex items-center space-x-1">
                <div class="h-4 w-1 bg-blue-400 rounded-full opacity-30"></div>
                <div class="h-6 w-1 bg-blue-400 rounded-full opacity-50"></div>
                <div class="h-8 w-1 bg-blue-400 rounded-full"></div>
                <div class="h-5 w-1 bg-blue-400 rounded-full opacity-60"></div>
                <div class="h-3 w-1 bg-blue-400 rounded-full opacity-20"></div>
            </div>
        </div>

        <!-- Card 3: Chats Abertos -->
        <div class="card-nm">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-purple-100 text-purple-600 rounded-xl">
                    <span class="material-symbols-outlined">chat</span>
                </div>
                <span class="text-purple-700 bg-purple-50 px-2 py-1 rounded-full text-[10px] font-bold">LIVE</span>
            </div>
            <p class="text-gray-500 uppercase tracking-widest text-[10px]">Chats Abertos</p>
            <p id="openConversations" class="text-2xl font-bold mt-2">{{ $stats['open_conversations'] }}</p>
            <div class="mt-4 flex -space-x-2">
                <div class="w-6 h-6 rounded-full border-2 border-white bg-green-200"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-blue-200"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-purple-200"></div>
                <div class="w-6 h-6 rounded-full border-2 border-white bg-gray-300 flex items-center justify-center text-[8px] font-bold">+8</div>
            </div>
        </div>

        <!-- Card 4: Taxa de Satisfação -->
        <div class="card-nm">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-yellow-100 text-yellow-600 rounded-xl">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <span class="text-green-700 bg-green-50 px-2 py-1 rounded-full text-[10px] font-bold">98%</span>
            </div>
            <p class="text-gray-500 uppercase tracking-widest text-[10px]">Satisfação</p>
            <p class="text-2xl font-bold mt-2">4.9/5.0</p>
            <div class="mt-4 flex space-x-1">
                <span class="material-symbols-outlined text-yellow-400 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-yellow-400 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-yellow-400 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-yellow-400 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="material-symbols-outlined text-yellow-400 text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Message Volume Chart -->
        <div class="lg:col-span-2 card-nm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-lg font-bold">Volume de Mensagens</h3>
                    <p class="text-gray-500 text-sm">Distribuição horária de mensagens recebidas</p>
                </div>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="px-3 py-1 bg-white rounded-md text-xs font-bold">24h</button>
                    <button class="px-3 py-1 text-xs hover:bg-white/50 rounded-md transition-all">7d</button>
                    <button class="px-3 py-1 text-xs hover:bg-white/50 rounded-md transition-all">30d</button>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="messagesByHourChart"></canvas>
            </div>
        </div>

        <!-- Channel Distribution -->
        <div class="card-nm">
            <h3 class="text-lg font-bold mb-8">Distribuição por Canal</h3>
            <div class="space-y-6">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-[#1DA85A]"></div>
                            <span class="font-medium">WhatsApp</span>
                        </div>
                        <span class="font-bold">64%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-[#1DA85A] w-[64%]"></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="font-medium">Outros</span>
                        </div>
                        <span class="font-bold">36%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 w-[36%]"></div>
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
        <div class="card-nm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-lg font-bold">Fluxo de Mensagens</h3>
                    <p class="text-gray-500 text-sm">Comparativo inbound vs outbound</p>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="directionChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Atividade por Agente -->
        <div class="card-nm">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-lg font-bold">Atividade por Agente</h3>
                    <p class="text-gray-500 text-sm">Ranking de conversas</p>
                </div>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="byAgentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Contatos -->
    <div class="card-nm overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold">Top 10 Contatos</h3>
                <p class="text-gray-500 text-sm">Contatos com mais interações</p>
            </div>
            <a href="{{ route('conversations.index') }}?view=reports" class="text-[#1DA85A] font-bold hover:underline flex items-center space-x-1">
                <span>Ver Todos</span>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-600 font-medium">
                    <tr>
                        <th class="px-8 py-4">Nome</th>
                        <th class="px-8 py-4">Telefone</th>
                        <th class="px-8 py-4 text-right">Mensagens</th>
                        <th class="px-8 py-4 text-right">Conversas</th>
                    </tr>
                </thead>
                <tbody id="topContactsTable" class="divide-y divide-gray-200">
                    <tr><td colspan="4" class="text-center p-4 text-gray-500">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- My Chats -->
        <div class="lg:col-span-2 card-nm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">Meus Atendimentos</h3>
                    <p class="text-gray-500 text-sm">{{ $myChats->count() }} chats ativos</p>
                </div>
                <a href="{{ route('conversations.index') }}" class="text-[#1DA85A] font-bold hover:underline flex items-center space-x-1">
                    <span>Ver Todos</span>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                @if($myChats->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 font-medium">
                        <tr>
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Última Mensagem</th>
                            <th class="px-8 py-4">Tempo</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($myChats->take(5) as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-gray-50 transition-all group">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-[#1DA85A] font-bold">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $chat->contact->name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-sm text-gray-600 truncate max-w-[250px]">
                                    {{ $chat->lastMessage?->content ?? 'Sem mensagens' }}
                                </p>
                            </td>
                            <td class="px-8 py-4 text-sm">{{ $chat->last_message_at?->diffForHumans() }}</td>
                            <td class="px-8 py-4 text-right">
                                <a href="{{ route('conversations.index', ['conversation' => $chat->id]) }}" class="btn-nm-primary text-xs">Abrir</a>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-8 text-center text-gray-500 text-sm">
                    Nenhum atendimento ativo no momento.
                </div>
                @endif
            </div>
        </div>

        <!-- Online Agents -->
        <div class="card-nm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">Agentes Online</h3>
                    <p class="text-gray-500 text-sm">{{ $onlineAgents->count() }} agentes conectados</p>
                </div>
            </div>
            <div class="p-4 flex flex-col gap-2 max-h-[400px] overflow-y-auto custom-scrollbar">
                @foreach($onlineAgents as $agent)
                <div class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center font-bold text-sm text-[#1DA85A]">
                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm truncate">{{ $agent->name }}</p>
                        <p class="text-[10px] text-green-600">Online</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ $agent->conversations_count }} chats</span>
                </div>
                @endforeach
                @if($onlineAgents->isEmpty())
                <p class="text-sm text-gray-500 text-center py-4">Nenhum agente online</p>
                @endif
            </div>
        </div>

        <!-- Pending Chats -->
        <div class="lg:col-span-3 card-nm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">Atendimentos Pendentes</h3>
                    <p class="text-gray-500 text-sm">Chats aguardando distribuição</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($pendingChats->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 font-medium">
                        <tr>
                            <th class="px-8 py-4">Cliente</th>
                            <th class="px-8 py-4">Última Mensagem</th>
                            <th class="px-8 py-4">Tempo de Espera</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pendingChats as $chat)
                        @if($chat->contact)
                        <tr class="hover:bg-gray-50 transition-all group">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-sm text-blue-600">
                                        {{ $chat->contact->initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $chat->contact->name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $chat->contact->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-sm text-gray-600 truncate max-w-[300px]">
                                    "{{ $chat->lastMessage?->content ?? 'Aguardando...' }}"
                                </p>
                            </td>
                            <td class="px-8 py-4 text-sm @if($chat->last_message_at?->diffInMinutes() > 10) text-red-600 font-bold @else text-gray-600 @endif">
                                {{ $chat->last_message_at?->diffForHumans() }}
                            </td>
                            <td class="px-8 py-4 text-right">
                                <form method="POST" action="{{ route('conversations.assign', $chat) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-nm-primary text-xs">Assumir</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-8 text-center text-gray-500 text-sm">
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
