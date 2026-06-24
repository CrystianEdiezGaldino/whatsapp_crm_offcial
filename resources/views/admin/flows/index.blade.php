@extends('layouts.app', ['fullHeight' => true])

@section('title', 'Fluxos')

@section('content')
<div class="master-detail">
    <!-- Left Panel: Flow List -->
    <div class="master-panel" style="width: 400px;">
        <div class="master-panel-header">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-secondary text-xl">account_tree</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Fluxos</h2>
                        <p class="text-[11px] text-gray-400 font-medium mt-0.5">{{ $flows->total() }} fluxo{{ $flows->total() !== 1 ? 's' : '' }} configurado{{ $flows->total() !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.flows.create') }}" class="h-9 px-3.5 rounded-[10px] bg-secondary text-white flex items-center gap-1.5 text-[12.5px] font-bold hover:opacity-90 transition-opacity shadow-sm" title="Novo Fluxo">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Novo fluxo
                </a>
            </div>
            <!-- Search -->
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[16px] text-gray-400">search</span>
                <input type="text" id="flowSearch" placeholder="Buscar fluxo..." class="w-full h-[36px] pl-9 pr-3 bg-[#F7F8FB] border border-[#E2E5EE] rounded-[9px] text-[12.5px] text-gray-700 placeholder-gray-400 focus:outline-none focus:border-secondary/40 transition-colors">
            </div>
        </div>

        <div class="master-panel-list design-scrollbar">
            @if(session('success'))
            <div class="m-3 p-2.5 bg-[#E8F8EF] border border-[#C8EDD8] rounded-[10px] text-primary text-xs font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="m-3 p-2.5 bg-[#FEF1F2] border border-error/20 rounded-[10px] text-error text-xs font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">error</span>
                {{ session('error') }}
            </div>
            @endif

            @forelse($flows as $flow)
            <div class="master-panel-item flow-item {{ isset($selectedFlow) && $selectedFlow && $selectedFlow->id == $flow->id ? 'active' : '' }}" data-name="{{ strtolower($flow->name) }}">
                <a href="{{ route('admin.flows.index', ['flow' => $flow->id]) }}" class="flex items-start gap-3 flex-1 min-w-0 no-underline text-inherit">
                    <!-- Flow Icon -->
                    <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center shrink-0 {{ $flow->is_active ? 'bg-[#E8F8EF] text-primary' : 'bg-[#F0F2F7] text-gray-400' }}">
                        <span class="material-symbols-outlined text-[18px]">
                            @if($flow->trigger_type === 'on_new_conversation')
                                forum
                            @elseif($flow->trigger_type === 'on_command')
                                terminal
                            @else
                                touch_app
                            @endif
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-[13px] font-bold text-gray-900 truncate">{{ $flow->name }}</h3>
                        </div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $flow->type === 'primary' ? 'bg-[#EEF0FE] text-secondary' : 'bg-[#F0F2F7] text-gray-500' }}">
                                {{ $flow->type === 'primary' ? 'Principal' : 'Secundário' }}
                            </span>
                            <span class="text-[10px] text-gray-400">·</span>
                            <span class="text-[11px] text-gray-400 font-medium">
                                @if($flow->trigger_type === 'on_new_conversation')
                                    Nova Conversa
                                @elseif($flow->trigger_type === 'on_command')
                                    Comando
                                @else
                                    Manual
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 font-medium">
                                <span class="material-symbols-outlined text-[13px]">linear_scale</span>
                                {{ $flow->nodes->count() }} etapa{{ $flow->nodes->count() !== 1 ? 's' : '' }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $flow->is_active ? 'text-primary' : 'text-gray-400' }}">
                                <span class="w-[6px] h-[6px] rounded-full {{ $flow->is_active ? 'bg-primary' : 'bg-gray-300' }}"></span>
                                {{ $flow->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </a>
                <form action="{{ route('admin.flows.toggle', $flow) }}" method="POST" class="shrink-0" onclick="event.stopPropagation()">
                    @csrf
                    <button
                        type="submit"
                        class="toggle-switch {{ $flow->is_active ? 'active' : '' }}"
                        title="{{ $flow->is_active ? 'Desativar fluxo' : 'Ativar fluxo' }}"
                        aria-label="{{ $flow->is_active ? 'Desativar fluxo' : 'Ativar fluxo' }}"
                    ></button>
                </form>
            </div>
            @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-[#F0F2F7] flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-gray-300">account_tree</span>
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1">Nenhum fluxo criado</p>
                <p class="text-xs text-gray-400 mb-4">Crie seu primeiro fluxo de atendimento</p>
                <a href="{{ route('admin.flows.create') }}" class="btn-primary text-xs px-4 py-2 inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">add</span>
                    Criar primeiro fluxo
                </a>
            </div>
            @endforelse

            @if($flows->hasPages())
            <div class="p-3 border-t border-[#F2F4F8]">
                {{ $flows->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Right Panel: Flow Detail -->
    <div class="detail-panel design-scrollbar">
        @if(isset($selectedFlow) && $selectedFlow)
        <div class="max-w-2xl mx-auto">
            <!-- Flow Header Card -->
            <div class="bg-white rounded-[16px] border border-[#E8EAF0] shadow-sm p-6 mb-5">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-start gap-4">
                        <div class="w-[48px] h-[48px] rounded-[12px] flex items-center justify-center shrink-0 {{ $selectedFlow->is_active ? 'bg-[#E8F8EF] text-primary' : 'bg-[#F0F2F7] text-gray-400' }}">
                            <span class="material-symbols-outlined text-[24px]">
                                @if($selectedFlow->trigger_type === 'on_new_conversation')
                                    forum
                                @elseif($selectedFlow->trigger_type === 'on_command')
                                    terminal
                                @else
                                    touch_app
                                @endif
                            </span>
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold text-gray-900 mb-1">{{ $selectedFlow->name }}</h2>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase {{ $selectedFlow->type === 'primary' ? 'bg-[#EEF0FE] text-secondary' : 'bg-[#F0F2F7] text-gray-500' }}">
                                    {{ $selectedFlow->type === 'primary' ? 'Principal' : 'Secundário' }}
                                </span>
                                <span class="text-sm text-gray-400">·</span>
                                <span class="text-sm text-gray-500 font-medium">
                                    @if($selectedFlow->trigger_type === 'on_new_conversation')
                                        Ativado em nova conversa
                                    @elseif($selectedFlow->trigger_type === 'on_command')
                                        Ativado por comando
                                    @else
                                        Ativação manual
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-[11px] font-bold flex items-center gap-1.5 {{ $selectedFlow->is_active ? 'bg-[#E8F8EF] text-primary' : 'bg-[#F0F2F7] text-gray-500' }}">
                        <span class="w-[7px] h-[7px] rounded-full {{ $selectedFlow->is_active ? 'bg-primary' : 'bg-gray-400' }}"></span>
                        {{ $selectedFlow->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('admin.flows.edit', $selectedFlow) }}" class="bg-secondary text-white px-4 py-2.5 rounded-[11px] text-xs font-bold hover:opacity-90 flex items-center gap-1.5 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">edit</span>
                        Editar fluxo
                    </a>
                    <form action="{{ route('admin.flows.toggle', $selectedFlow) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-white border border-[#E2E5EE] text-gray-700 px-4 py-2.5 rounded-[11px] text-xs font-bold hover:bg-gray-50 flex items-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">{{ $selectedFlow->is_active ? 'pause_circle' : 'play_circle' }}</span>
                            {{ $selectedFlow->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.flows.executions', $selectedFlow) }}" class="bg-white border border-[#E2E5EE] text-gray-700 px-4 py-2.5 rounded-[11px] text-xs font-bold hover:bg-gray-50 flex items-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">history</span>
                        Histórico
                    </a>
                </div>
            </div>

            <!-- KPI Stats -->
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="kpi-card">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Etapas</p>
                    @php $nodeCount = $selectedFlow->nodes()->count(); @endphp
                    <p class="text-2xl font-extrabold text-gray-900">{{ $nodeCount }}</p>
                </div>
                <div class="kpi-card">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Execuções</p>
                    @php $execCount = $selectedFlow->executions()->count(); @endphp
                    <p class="text-2xl font-extrabold text-gray-900">{{ $execCount }}</p>
                </div>
                <div class="kpi-card">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Criado em</p>
                    <p class="text-sm font-bold text-gray-900">{{ $selectedFlow->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <!-- Config Messages -->
            @if($selectedFlow->config && (!empty($selectedFlow->config['initial_message']) || !empty($selectedFlow->config['final_message'])))
            <div class="card-primary mb-5">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-gray-400">sms</span>
                    Mensagens do Fluxo
                </h3>

                @if(!empty($selectedFlow->config['initial_message']))
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Mensagem Inicial</label>
                    <div class="bg-[#F7F8FB] border border-[#E8EAF0] rounded-[12px] p-4">
                        <p class="text-[13px] text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $selectedFlow->config['initial_message'] }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($selectedFlow->config['final_message']))
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Mensagem Final</label>
                    <div class="bg-[#F7F8FB] border border-[#E8EAF0] rounded-[12px] p-4">
                        <p class="text-[13px] text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $selectedFlow->config['final_message'] }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Flow Timeline -->
            <div class="card-primary mb-5">
                <h3 class="text-sm font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-gray-400">account_tree</span>
                    Etapas do Fluxo
                </h3>

                @php $nodes = $selectedFlow->nodes()->orderBy('position')->get(); @endphp

                <div class="flow-timeline">
                    <!-- Trigger -->
                    <div class="flow-timeline-node trigger">
                        <div class="bg-[#E8F8EF] border border-[#C8EDD8] rounded-[12px] p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-outlined text-primary text-[16px]">play_circle</span>
                                <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Gatilho</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                @if($selectedFlow->trigger_type === 'on_new_conversation')
                                    Nova conversa iniciada
                                @elseif($selectedFlow->trigger_type === 'on_command')
                                    Comando executado
                                @else
                                    Ativação manual
                                @endif
                            </p>
                        </div>
                    </div>

                    @forelse($nodes as $node)
                    <div class="flow-timeline-node">
                        <div class="bg-white border border-[#E8EAF0] rounded-[12px] p-4 hover:shadow-sm transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#EEF0FE] text-secondary flex items-center justify-center text-[11px] font-bold">{{ $loop->iteration }}</span>
                                    <span class="text-[11px] font-bold text-gray-400 uppercase">{{ $node->node_type ?? $node->type ?? 'menu' }}</span>
                                </div>
                                <span class="material-symbols-outlined text-[16px] text-gray-300">more_vert</span>
                            </div>
                            @php
                                $nodeConfig = is_array($node->config) ? $node->config : [];
                                $label = $nodeConfig['label'] ?? $node->name ?? ('Opção ' . $loop->iteration);
                                $optionNum = $nodeConfig['option_number'] ?? $loop->iteration;
                            @endphp
                            <p class="text-[13.5px] font-semibold text-gray-900 flex items-center gap-2">
                                <span class="shortcut-badge text-[10px]">{{ $optionNum }}</span>
                                {{ $label }}
                            </p>
                            @if($node->target_sector_id)
                                @php $targetSector = \App\Models\Sector::find($node->target_sector_id); @endphp
                                @if($targetSector)
                                <div class="mt-2.5 pt-2.5 border-t border-[#F2F4F8]">
                                    <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[14px] text-secondary">arrow_forward</span>
                                        Encaminha para: <span class="sector-tag ml-1">{{ $targetSector->name }}</span>
                                    </p>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="flow-timeline-node">
                        <div class="bg-[#FFFCF0] border border-dashed border-[#E5CE8F] rounded-[12px] p-4 text-center">
                            <span class="material-symbols-outlined text-[20px] text-[#D4A843] mb-1">info</span>
                            <p class="text-sm text-gray-500 font-medium">Nenhuma etapa configurada</p>
                            <a href="{{ route('admin.flows.edit', $selectedFlow) }}" class="text-xs text-secondary font-bold hover:underline mt-1 inline-block">Adicionar etapas</a>
                        </div>
                    </div>
                    @endforelse

                    <!-- End -->
                    <div class="flow-timeline-node end">
                        <div class="bg-[#F7F8FB] border border-[#E8EAF0] rounded-[12px] p-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-gray-400">stop_circle</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fim do fluxo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="flex items-center justify-between">
                @if($selectedFlow->createdBy)
                <div class="text-xs text-gray-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">person</span>
                    Criado por <span class="font-semibold text-gray-500">{{ $selectedFlow->createdBy->name }}</span>
                    em {{ $selectedFlow->created_at->format('d/m/Y H:i') }}
                </div>
                @else
                <div></div>
                @endif

                <form action="{{ route('admin.flows.destroy', $selectedFlow) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este fluxo? Esta ação não pode ser desfeita.')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-white border border-error/30 text-error px-3.5 py-2 rounded-[11px] text-[11px] font-bold hover:bg-error/5 flex items-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[14px]">delete</span>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
        @else
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center h-full text-center">
            <div class="w-20 h-20 rounded-full bg-[#EEF0FE] flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-4xl text-secondary/50">account_tree</span>
            </div>
            <h3 class="text-lg font-extrabold text-gray-700 mb-1.5">Selecione um fluxo</h3>
            <p class="text-sm text-gray-400 max-w-xs">Escolha um fluxo na lista ao lado para visualizar seus detalhes, etapas e configurações</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('flowSearch')?.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    document.querySelectorAll('.flow-item').forEach(item => {
        const name = item.dataset.name || '';
        item.style.display = name.includes(query) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
