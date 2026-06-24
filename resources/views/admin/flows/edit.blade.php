@extends('layouts.app')

@section('title', 'Editar Fluxo')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="h-[66px] shrink-0 flex items-center gap-3 px-6 bg-white border-b border-[#E8EAF0]">
        <a href="{{ route('admin.flows.index', ['flow' => $flow->id]) }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-gray-50 text-gray-400 hover:text-gray-700 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </a>
        <h1 class="text-lg font-extrabold text-gray-900">Editar Fluxo</h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $flow->is_active ? 'bg-[#E8F8EF] text-primary' : 'bg-gray-100 text-gray-500' }}">
            {{ $flow->is_active ? 'Ativo' : 'Inativo' }}
        </span>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto design-scrollbar p-6 bg-app-bg">
        <div class="max-w-2xl mx-auto">
            @if($errors->any())
            <div class="mb-5 p-3 bg-[#FEF1F2] border border-error/20 rounded-[11px] text-error text-sm">
                <p class="font-bold mb-1">Erros encontrados:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-5 p-3 bg-[#FEF1F2] border border-error/20 rounded-[11px] text-error text-sm font-medium">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('admin.flows.update', $flow) }}" method="POST">
                @csrf @method('PUT')

                <!-- Basic Info -->
                <div class="card-primary mb-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informações Básicas</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome do Fluxo</label>
                            <input type="text" name="name" value="{{ old('name', $flow->name) }}" class="input-primary" placeholder="Ex: Atendimento Geral" required>
                            @error('name')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tipo</label>
                                <select name="type" class="select-primary" required>
                                    <option value="primary" {{ old('type', $flow->type) === 'primary' ? 'selected' : '' }}>Principal</option>
                                    <option value="secondary" {{ old('type', $flow->type) === 'secondary' ? 'selected' : '' }}>Secundário</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Quando Ativar?</label>
                                <select name="trigger_type" class="select-primary" required>
                                    <option value="on_new_conversation" {{ old('trigger_type', $flow->trigger_type) === 'on_new_conversation' ? 'selected' : '' }}>Nova Conversa</option>
                                    <option value="on_command" {{ old('trigger_type', $flow->trigger_type) === 'on_command' ? 'selected' : '' }}>Comando</option>
                                    <option value="manual" {{ old('trigger_type', $flow->trigger_type) === 'manual' ? 'selected' : '' }}>Manual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card-primary mb-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Mensagens</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Mensagem Inicial</label>
                            <div class="relative">
                                <textarea name="config[initial_message]" class="textarea-primary message-textarea" rows="4" placeholder="Use {nome}, {telefone}, {setor}, {agente} para variáveis..." required>{{ old('config.initial_message', $flow->config['initial_message'] ?? '') }}</textarea>
                                <button type="button" class="btn-insert-variable absolute top-2 right-2 bg-[#F0F2F7] hover:bg-[#E8EAF0] px-2.5 py-1 rounded-[8px] text-[11px] font-bold text-gray-500 transition-colors">
                                    + Variável
                                </button>
                            </div>
                            @error('config.initial_message')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        @include('admin.flows.partials.final-message-toggle', [
                            'enabled' => (bool) old('has_final_message', !empty($flow->config['final_message'] ?? '')),
                            'finalMessage' => old('config.final_message', $flow->config['final_message'] ?? ''),
                        ])

                        <div class="bg-[#EEF0FE] border border-secondary/20 rounded-[11px] p-3 flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-secondary text-[18px] mt-0.5">info</span>
                            <p class="text-[12px] text-secondary font-medium leading-relaxed">
                                Variáveis disponíveis: <code class="shortcut-badge text-[10px] mx-0.5">{nome}</code> <code class="shortcut-badge text-[10px] mx-0.5">{telefone}</code> <code class="shortcut-badge text-[10px] mx-0.5">{setor}</code> <code class="shortcut-badge text-[10px] mx-0.5">{agente}</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Menu Options -->
                <div class="card-primary mb-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900">Opções de Menu</h3>
                        <button type="button" onclick="addOption()" class="bg-white border border-[#E2E5EE] text-gray-700 px-3 py-1.5 rounded-[9px] text-[11px] font-bold hover:bg-gray-50 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-[14px]">add</span>
                            Adicionar
                        </button>
                    </div>

                    <div id="optionsContainer" class="space-y-3">
                        @php $existingNodes = $flow->nodes()->orderBy('position')->get(); @endphp
                        @forelse($existingNodes as $i => $node)
                        @php $nodeConfig = is_array($node->config) ? $node->config : []; @endphp
                        <div class="flow-option bg-[#F7F8FB] border border-[#E2E5EE] rounded-[11px] p-4">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-bold text-gray-500">Opção {{ $i + 1 }}</span>
                                <button type="button" onclick="removeOption(this)" class="text-error text-[11px] font-semibold hover:underline">Remover</button>
                            </div>
                            <input type="hidden" name="nodes[{{ $i }}][node_type]" value="{{ $node->node_type ?? 'menu' }}">
                            <input type="hidden" name="nodes[{{ $i }}][position]" value="{{ $i }}">
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Número</label>
                                    <input type="number" name="nodes[{{ $i }}][config][option_number]" value="{{ $nodeConfig['option_number'] ?? $i + 1 }}" class="input-primary !text-center" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Label</label>
                                    <input type="text" name="nodes[{{ $i }}][config][label]" value="{{ $nodeConfig['label'] ?? '' }}" class="input-primary" placeholder="Ex: Suporte" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Setor</label>
                                    <select name="nodes[{{ $i }}][target_sector_id]" class="select-primary">
                                        <option value="">Selecione</option>
                                        @foreach($sectors as $sector)
                                        <option value="{{ $sector->id }}" {{ $node->target_sector_id == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="flow-option bg-[#F7F8FB] border border-[#E2E5EE] rounded-[11px] p-4">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-bold text-gray-500">Opção 1</span>
                                <button type="button" onclick="removeOption(this)" class="text-error text-[11px] font-semibold hover:underline">Remover</button>
                            </div>
                            <input type="hidden" name="nodes[0][node_type]" value="menu">
                            <input type="hidden" name="nodes[0][position]" value="0">
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Número</label>
                                    <input type="number" name="nodes[0][config][option_number]" value="1" class="input-primary !text-center" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Label</label>
                                    <input type="text" name="nodes[0][config][label]" class="input-primary" placeholder="Ex: Suporte" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Setor</label>
                                    <select name="nodes[0][target_sector_id]" class="select-primary">
                                        <option value="">Selecione</option>
                                        @foreach($sectors as $sector)
                                        <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <div></div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.flows.index', ['flow' => $flow->id]) }}" class="btn-secondary text-sm px-5 py-2.5">Cancelar</a>
                        <button type="submit" class="btn-primary text-sm px-5 py-2.5">
                            <span class="material-symbols-outlined text-[16px] mr-1">save</span>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>

            <!-- Delete form OUTSIDE the update form -->
            <div class="mt-6 pt-5 border-t border-[#F2F4F8]">
                <form action="{{ route('admin.flows.destroy', $flow) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este fluxo? Esta ação não pode ser desfeita.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-white border border-error/30 text-error px-4 py-2.5 rounded-[11px] text-xs font-bold hover:bg-error/5 flex items-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                        Excluir fluxo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let optionCount = {{ $existingNodes->count() ?: 1 }};

function addOption() {
    optionCount++;
    const container = document.getElementById('optionsContainer');
    const html = `
        <div class="flow-option bg-[#F7F8FB] border border-[#E2E5EE] rounded-[11px] p-4">
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold text-gray-500">Opção ${optionCount}</span>
                <button type="button" onclick="removeOption(this)" class="text-error text-[11px] font-semibold hover:underline">Remover</button>
            </div>
            <input type="hidden" name="nodes[${optionCount - 1}][node_type]" value="menu">
            <input type="hidden" name="nodes[${optionCount - 1}][position]" value="${optionCount - 1}">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Número</label>
                    <input type="number" name="nodes[${optionCount - 1}][config][option_number]" value="${optionCount}" class="input-primary !text-center" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Label</label>
                    <input type="text" name="nodes[${optionCount - 1}][config][label]" class="input-primary" placeholder="Ex: Suporte" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Setor</label>
                    <select name="nodes[${optionCount - 1}][target_sector_id]" class="select-primary">
                        <option value="">Selecione</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeOption(btn) {
    btn.closest('.flow-option').remove();
}

function toggleFinalMessage() {
    const toggle = document.getElementById('finalMsgToggle');
    const btn = document.getElementById('finalMsgToggleBtn');
    const container = document.getElementById('finalMsgContainer');
    const textarea = document.getElementById('finalMsgTextarea');

    toggle.classList.toggle('active');
    const isActive = toggle.classList.contains('active');

    btn?.setAttribute('aria-pressed', isActive ? 'true' : 'false');

    if (isActive) {
        container.classList.remove('hidden');
        textarea?.focus();
    } else {
        container.classList.add('hidden');
        if (textarea) textarea.value = '';
    }
}
</script>

<script>
document.querySelectorAll('.btn-insert-variable').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        showVariableDropdown(this);
    });
});

function showVariableDropdown(button) {
    const existingDropdown = document.querySelector('.variable-dropdown');
    if (existingDropdown) { existingDropdown.remove(); return; }

    const dropdown = document.createElement('div');
    dropdown.className = 'variable-dropdown bg-white border border-[#E8EAF0] rounded-[11px] shadow-lg z-50 min-w-max overflow-hidden';

    const variables = { 'nome': 'Nome do contato', 'telefone': 'Telefone', 'setor': 'Setor', 'agente': 'Nome do bot' };

    Object.entries(variables).forEach(([key, desc]) => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2.5 cursor-pointer hover:bg-[#F7F8FB] text-sm flex items-center gap-2 border-b border-[#F2F4F8] last:border-0';
        item.innerHTML = '<span class="shortcut-badge text-[10px]">{' + key + '}</span> <span class="text-gray-500 text-xs">' + desc + '</span>';
        item.addEventListener('click', () => insertVariableAtButton(key, button));
        dropdown.appendChild(item);
    });

    const rect = button.getBoundingClientRect();
    dropdown.style.position = 'fixed';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.top = (rect.bottom + 5) + 'px';
    document.body.appendChild(dropdown);

    document.addEventListener('click', function closeDropdown(e) {
        if (e.target !== button && !dropdown.contains(e.target)) {
            dropdown.remove();
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function insertVariableAtButton(varName, button) {
    const textarea = button.closest('.relative').querySelector('textarea');
    if (!textarea) return;

    const varText = '{' + varName + '}';
    const start = textarea.selectionStart, end = textarea.selectionEnd, text = textarea.value;
    textarea.value = text.substring(0, start) + varText + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + varText.length;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));

    const dropdown = document.querySelector('.variable-dropdown');
    if (dropdown) dropdown.remove();
}
</script>
@endpush
@endsection
