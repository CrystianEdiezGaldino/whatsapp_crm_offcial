@extends('layouts.app', ['fullHeight' => true])

@section('title', 'Novo Fluxo')

@section('content')
<div class="flex flex-col h-full overflow-hidden">
    <header class="page-header shrink-0 !h-auto min-h-[66px] !py-3 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.flows.index') }}" class="w-9 h-9 rounded-[10px] flex items-center justify-center hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Voltar">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-secondary">account_tree</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-on-surface leading-tight">Novo Fluxo</h1>
                <p class="text-xs text-gray-600">Configure gatilho, mensagens e opções de menu</p>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto design-scrollbar p-6">
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

            <form action="{{ route('admin.flows.store') }}" method="POST">
                @csrf

                <!-- Basic Info -->
                <div class="card-primary mb-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informações Básicas</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome do Fluxo</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="input-primary" placeholder="Ex: Atendimento Geral" required>
                            @error('name')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tipo</label>
                                <select name="type" class="select-primary" required>
                                    <option value="primary" {{ old('type') === 'primary' ? 'selected' : '' }}>Principal</option>
                                    <option value="secondary" {{ old('type') === 'secondary' ? 'selected' : '' }}>Secundário</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Quando Ativar?</label>
                                <select name="trigger_type" class="select-primary" required>
                                    <option value="on_new_conversation" {{ old('trigger_type') === 'on_new_conversation' ? 'selected' : '' }}>Nova Conversa</option>
                                    <option value="on_command" {{ old('trigger_type') === 'on_command' ? 'selected' : '' }}>Comando</option>
                                    <option value="manual" {{ old('trigger_type') === 'manual' ? 'selected' : '' }}>Manual</option>
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
                                <textarea name="config[initial_message]" class="textarea-primary message-textarea" rows="4" placeholder="Use {nome}, {telefone}, {setor}, {agente} para variáveis..." required>{{ old('config.initial_message') }}</textarea>
                                <button type="button" class="btn-insert-variable absolute top-2 right-2 bg-[#F0F2F7] hover:bg-[#E8EAF0] px-2.5 py-1 rounded-[8px] text-[11px] font-bold text-gray-500 transition-colors">
                                    + Variável
                                </button>
                            </div>
                            @error('config.initial_message')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        @include('admin.flows.partials.final-message-toggle', [
                            'enabled' => (bool) old('config.final_message'),
                            'finalMessage' => old('config.final_message'),
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
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.flows.index') }}" class="btn-secondary text-sm px-5 py-2.5">Cancelar</a>
                    <button type="submit" class="btn-primary text-sm px-5 py-2.5">
                        <span class="material-symbols-outlined text-[16px] mr-1">save</span>
                        Salvar Fluxo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let optionCount = 1;

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

<script src="{{ asset('js/flow-variables.js') }}"></script>
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
