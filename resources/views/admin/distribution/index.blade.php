@extends('layouts.app')

@section('title', 'Distribuição de Leads')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-outline-variant flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Distribuição de Leads</h1>
            <p class="text-xs text-on-surface-variant mt-1">Gerencie o modo de distribuição e capacidade dos atendentes</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $settings->isAutomatic() ? 'bg-secondary text-on-secondary' : 'bg-surface-container text-on-surface' }}">
                <span class="material-symbols-outlined inline text-sm mr-1">{{ $settings->isAutomatic() ? 'smart_toy' : 'person' }}</span>
                {{ $settings->isAutomatic() ? 'Automático' : 'Manual' }}
            </span>
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
    <div class="mx-6 mt-4 p-4 bg-error/10 border border-error text-error rounded-lg">
        <strong>Erro:</strong>
        <ul class="list-disc list-inside mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="mx-6 mt-4 p-4 bg-secondary-container/20 border border-secondary text-on-surface rounded-lg flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary mt-1">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mx-6 mt-4 p-4 bg-error/10 border border-error text-error rounded-lg flex items-start gap-3">
        <span class="material-symbols-outlined mt-1">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="grid grid-cols-1 gap-6 max-w-7xl">
            <!-- Modo de Distribuição Card -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">⚙️ Modo de Distribuição</h2>

                <form action="{{ route('admin.distribution.settings') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Mode Toggle -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-on-surface">Modo</label>
                        <div class="flex gap-2">
                            <label class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer {{ $settings->isManual() ? 'border-secondary bg-secondary/10' : 'border-outline-variant' }}">
                                <input type="radio" name="mode" value="manual" {{ $settings->isManual() ? 'checked' : '' }} class="w-4 h-4">
                                <span class="text-sm font-medium">
                                    <span class="material-symbols-outlined inline text-base">person</span> Manual
                                </span>
                                <span class="text-xs text-on-surface-variant">(Agentes clamam conversas)</span>
                            </label>

                            <label class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer {{ $settings->isAutomatic() ? 'border-secondary bg-secondary/10' : 'border-outline-variant' }}">
                                <input type="radio" name="mode" value="automatic" {{ $settings->isAutomatic() ? 'checked' : '' }} class="w-4 h-4">
                                <span class="text-sm font-medium">
                                    <span class="material-symbols-outlined inline text-base">smart_toy</span> Automático
                                </span>
                                <span class="text-xs text-on-surface-variant">(Sistema distribui)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Overflow Action (só mostrar se automático) -->
                    @if($settings->isAutomatic())
                    <div class="space-y-2 pt-4 border-t border-outline-variant">
                        <label class="text-sm font-semibold text-on-surface">Quando todos agentes estão cheios</label>
                        <select name="overflow_action" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                            <option value="next_agent" {{ $settings->isNextAgentOverflow() ? 'selected' : '' }}>
                                Atribuir ao agente com menor carga (ignora máximo)
                            </option>
                            <option value="queue" {{ $settings->isQueueOverflow() ? 'selected' : '' }}>
                                Deixar na fila aguardando disponibilidade
                            </option>
                        </select>
                        <p class="text-xs text-on-surface-variant">
                            {{ $settings->isNextAgentOverflow()
                                ? '→ Novos leads serão atribuídos ao agente menos ocupado'
                                : '→ Novos leads ficarão na fila até haver espaço' }}
                        </p>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                            <span class="material-symbols-outlined inline text-sm mr-1">save</span> Salvar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Capacidade dos Agentes -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">👥 Capacidade dos Atendentes</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Atendente</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Conversas Ativas</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Capacidade</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Disponível</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ativo</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse($agents as $agent)
                            @php
                                $capacity = $agent->agentCapacity ?? \App\Models\AgentCapacity::where('user_id', $agent->id)->first();
                                $activeCount = $capacity?->activeConversationsCount() ?? 0;
                                $maxCapacity = $capacity?->max_conversations ?? 10;
                                $isFull = $activeCount >= $maxCapacity;
                            @endphp
                            <tr class="hover:bg-surface-container-low">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center text-xs font-bold text-on-primary-fixed">
                                            {{ $agent->name[0] }}
                                        </div>
                                        <span class="font-medium text-on-surface">{{ $agent->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $agent->status === 'online' ? 'bg-secondary-container/20 text-secondary' : 'bg-outline-variant/20 text-on-surface-variant' }}">
                                        <span class="w-2 h-2 rounded-full {{ $agent->status === 'online' ? 'bg-secondary' : 'bg-on-surface-variant' }}"></span>
                                        {{ $agent->status === 'online' ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-on-surface">{{ $activeCount }}</td>
                                <td class="px-4 py-3 text-on-surface">{{ $maxCapacity }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-24">
                                        <div class="relative h-2 bg-outline-variant rounded-full overflow-hidden">
                                            <div class="absolute h-full transition-all {{ $isFull ? 'bg-error' : 'bg-secondary' }}" style="width: {{ ($activeCount / $maxCapacity) * 100 }}%"></div>
                                        </div>
                                        <p class="text-xs text-on-surface-variant mt-1">{{ max(0, $maxCapacity - $activeCount) }} vagas</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="w-4 h-4 rounded toggle-agent-active" data-user-id="{{ $agent->id }}" {{ $capacity?->is_active ? 'checked' : '' }}>
                                </td>
                                <td class="px-4 py-3">
                                    <button type="button" class="text-secondary hover:text-secondary/80 font-semibold text-xs edit-capacity" data-user-id="{{ $agent->id }}" data-max="{{ $maxCapacity }}">
                                        <span class="material-symbols-outlined inline text-sm">edit</span> Editar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">
                                    Nenhum atendente encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fila de Espera (se overflow=queue) -->
            @if($settings->isQueueOverflow() && $queuedLeads->count() > 0)
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">⏳ Fila de Espera ({{ $queuedLeads->count() }} conversas)</h2>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($queuedLeads as $lead)
                    <div class="flex items-center justify-between p-3 border border-outline-variant rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-on-surface">{{ $lead['contact_name'] }}</p>
                            <p class="text-xs text-on-surface-variant truncate">{{ $lead['last_message'] }}</p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <p class="text-xs font-semibold text-error">{{ $lead['wait_time'] }}min</p>
                            <p class="text-xs text-on-surface-variant">aguardando</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Distribuições Recentes -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">📊 Distribuições Recentes</h2>

                @if($recentAssignments->count() > 0)
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($recentAssignments as $assignment)
                    <div class="flex items-center justify-between p-3 border border-outline-variant rounded-lg hover:bg-surface-container-low">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-on-surface">Conversation #{{ $assignment['conversation_id'] }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $assignment['agent_name'] }}</p>
                        </div>
                        <span class="text-xs text-on-surface-variant whitespace-nowrap ml-4">{{ $assignment['time_ago'] }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-on-surface-variant text-sm">
                    Nenhuma distribuição automática registrada ainda
                </div>
                @endif
            </div>

            <!-- Dashboard de Métricas -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-on-surface-variant uppercase">Total de Agentes</p>
                            <p class="text-3xl font-bold text-on-surface mt-1">{{ $agents->count() }}</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">groups</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-on-surface-variant uppercase">Carga Total</p>
                            <p class="text-3xl font-bold text-on-surface mt-1">
                                {{ $metrics->sum(fn($m) => $m['active_conversations']) }}/{{ $metrics->sum(fn($m) => $m['max_conversations']) }}
                            </p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">trending_up</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-on-surface-variant uppercase">Agentes Cheios</p>
                            <p class="text-3xl font-bold text-error mt-1">{{ $metrics->where('is_full', true)->count() }}</p>
                        </div>
                        <span class="material-symbols-outlined text-error">warning</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-on-surface-variant uppercase">Na Fila</p>
                            <p class="text-3xl font-bold text-on-surface mt-1">{{ $queuedLeads->count() }}</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">schedule</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Editar Capacidade -->
<div id="capacityModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6">
        <h3 class="text-lg font-bold text-on-surface mb-4">Editar Capacidade</h3>

        <form id="capacityForm" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" id="userId" name="user_id">

            <div>
                <label class="text-sm font-semibold text-on-surface block mb-1">Máximo de Conversas Ativas</label>
                <input type="number" id="maxConversations" name="max_conversations" min="1" max="100" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('capacityModal').classList.add('hidden')" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Edit Capacity
    document.querySelectorAll('.edit-capacity').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const maxConv = this.dataset.max;

            document.getElementById('userId').value = userId;
            document.getElementById('maxConversations').value = maxConv;
            document.getElementById('capacityForm').action = `/admin/distribution/agents/${userId}/capacity`;
            document.getElementById('capacityModal').classList.remove('hidden');
        });
    });

    // Form submission
    document.getElementById('capacityForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const userId = document.getElementById('userId').value;
        const maxConv = document.getElementById('maxConversations').value;

        try {
            const response = await fetch(`/admin/distribution/agents/${userId}/capacity`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    max_conversations: maxConv,
                }),
            });

            if (response.ok) {
                location.reload();
            } else {
                alert('Erro ao atualizar capacidade');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao atualizar capacidade');
        }
    });

    // Toggle agent active
    document.querySelectorAll('.toggle-agent-active').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const userId = this.dataset.userId;
            const isActive = this.checked;

            try {
                const response = await fetch(`/admin/distribution/agents/${userId}/capacity`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        is_active: isActive,
                    }),
                });

                if (!response.ok) {
                    alert('Erro ao atualizar status do agente');
                    this.checked = !isActive;
                }
            } catch (error) {
                console.error(error);
                alert('Erro ao atualizar status do agente');
                this.checked = !isActive;
            }
        });
    });

    // Close modal on outside click
    document.getElementById('capacityModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
