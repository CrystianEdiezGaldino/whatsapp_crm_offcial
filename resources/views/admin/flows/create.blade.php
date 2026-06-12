@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Criar Novo Fluxo</h1>

    @if ($errors->any())
        <div class="alert-neumorphic error mb-4">
            <p class="font-semibold mb-2">Erros encontrados:</p>
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-neumorphic error mb-4">
            <p class="font-semibold">{{ session('error') }}</p>
        </div>
    @endif

    <form action="{{ route('admin.flows.store') }}" method="POST" class="card-nm">
        @csrf

        <div class="mb-6">
            <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Nome do Fluxo</label>
            <input type="text" id="name" name="name" required value="{{ old('name') }}" class="input-nm">
            @error('name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="type" class="block text-sm font-semibold text-gray-900 mb-2">Tipo</label>
                <select id="type" name="type" required class="select-nm">
                    <option value="primary">Principal</option>
                    <option value="secondary">Secundário</option>
                </select>
            </div>

            <div>
                <label for="trigger_type" class="block text-sm font-semibold text-gray-900 mb-2">Quando Ativar?</label>
                <select id="trigger_type" name="trigger_type" required class="select-nm">
                    <option value="on_new_conversation">Nova Conversa</option>
                    <option value="on_command">Comando</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label for="initial_message" class="block text-sm font-semibold text-gray-900 mb-2">Mensagem Inicial</label>
            <div class="relative">
                <textarea id="initial_message" name="config[initial_message]" required class="textarea-nm message-textarea" rows="4" placeholder="Use @{{nome}}, @{{telefone}}, @{{setor}} para variáveis dinâmicas...">{{ old('config.initial_message') }}</textarea>
                <button type="button" class="btn-insert-variable absolute top-2 right-2 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm font-medium transition-colors">
                    + Inserir Variável
                </button>
            </div>
            <div id="validation-warnings" class="mt-2 text-red-600 text-sm hidden"></div>
            <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                <p class="text-xs text-gray-600 font-semibold mb-1">Preview:</p>
                <p id="message-preview" class="text-gray-800 text-sm">{{ old('config.initial_message') }}</p>
            </div>
            @error('config.initial_message') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-900 mb-4">Opções de Menu</label>
            <div id="optionsContainer" class="space-y-4">
                <div class="option-block-neumorphic">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-semibold text-gray-900">Opção 1</span>
                        <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors">Remover</button>
                    </div>
                    <input type="hidden" name="nodes[0][node_type]" value="menu">
                    <input type="hidden" name="nodes[0][position]" value="0">
                    <input type="number" name="nodes[0][config][option_number]" value="1" class="input-nm mb-3" placeholder="Número" required>
                    <input type="text" name="nodes[0][config][label]" value="" class="input-nm mb-3" placeholder="Label (ex: Suporte)" required>
                    <select name="nodes[0][target_sector_id]" class="select-nm">
                        <option value="">-- Selecione um Setor --</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="button" onclick="addOption()" class="btn-nm-secondary mt-4">+ Adicionar Opção</button>
        </div>

        <div class="mb-8">
            <label for="final_message" class="block text-sm font-semibold text-gray-900 mb-2">Mensagem Final</label>
            <div class="relative">
                <textarea id="final_message" name="config[final_message]" required class="textarea-nm message-textarea" rows="3" placeholder="Use @{{nome}}, @{{telefone}}, @{{setor}} para variáveis dinâmicas...">{{ old('config.final_message') }}</textarea>
                <button type="button" class="btn-insert-variable absolute top-2 right-2 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm font-medium transition-colors">
                    + Inserir Variável
                </button>
            </div>
            <div id="validation-warnings" class="mt-2 text-red-600 text-sm hidden"></div>
            <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                <p class="text-xs text-gray-600 font-semibold mb-1">Preview:</p>
                <p id="message-preview" class="text-gray-800 text-sm">{{ old('config.final_message') }}</p>
            </div>
            @error('config.final_message') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-4">
            <a href="{{ route('admin.flows.index') }}" class="btn-nm-secondary">Cancelar</a>
            <button type="submit" class="btn-nm-primary">Salvar Fluxo</button>
        </div>
    </form>
</div>

<script>
let optionCount = 1;

function addOption() {
    optionCount++;
    const container = document.getElementById('optionsContainer');
    const html = `
        <div class="option-block-neumorphic">
            <div class="flex justify-between items-center mb-3">
                <span class="font-semibold text-gray-900">Opção ${optionCount}</span>
                <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors">Remover</button>
            </div>
            <input type="hidden" name="nodes[${optionCount - 1}][node_type]" value="menu">
            <input type="hidden" name="nodes[${optionCount - 1}][position]" value="${optionCount - 1}">
            <input type="number" name="nodes[${optionCount - 1}][config][option_number]" value="${optionCount}" class="input-nm mb-3" placeholder="Número" required>
            <input type="text" name="nodes[${optionCount - 1}][config][label]" value="" class="input-nm mb-3" placeholder="Label" required>
            <select name="nodes[${optionCount - 1}][target_sector_id]" class="select-nm">
                <option value="">-- Selecione um Setor --</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeOption(btn) {
    btn.closest('.option-block-neumorphic').remove();
}
</script>

<script src="{{ asset('js/flow-variables.js') }}"></script>
<script>
    // Initialize FlowVariables for each message textarea
    document.querySelectorAll('.message-textarea').forEach((textarea, index) => {
        const id = `flow-vars-${index}`;
        textarea.id = id;

        const config = {
            textareaSelector: `#${id}`,
            warningsSelector: `#${id}-warnings`,
            previewSelector: `#${id}-preview`,
            insertButtonSelector: `button.btn-insert-variable[data-target="${id}"]`
        };

        // Create wrapper container for warnings and preview
        const wrapper = document.createElement('div');
        const warningsDiv = document.createElement('div');
        warningsDiv.id = `${id}-warnings`;
        warningsDiv.className = 'mt-2 text-red-600 text-sm hidden';

        const previewDiv = document.createElement('div');
        previewDiv.id = `${id}-preview`;
        previewDiv.className = 'mt-3 p-3 bg-gray-50 rounded border border-gray-200';
        previewDiv.innerHTML = '<p class="text-xs text-gray-600 font-semibold mb-1">Preview:</p><p id="' + id + '-preview-text" class="text-gray-800 text-sm">' + textarea.value + '</p>';

        // Update config to use proper selectors
        config.warningsSelector = `#${id}-warnings`;
        config.previewSelector = `#${id}-preview-text`;

        // Initialize FlowVariables for this textarea
        new FlowVariables(config);
    });

    // Setup insert variable buttons
    document.querySelectorAll('.btn-insert-variable').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showVariableDropdown(this);
        });
    });

    function showVariableDropdown(button) {
        const existingDropdown = document.querySelector('.variable-dropdown');
        if (existingDropdown) {
            existingDropdown.remove();
            return;
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'variable-dropdown absolute bg-white border border-gray-300 rounded shadow-lg z-10 mt-2 min-w-max';

        const variables = {
            'nome': 'Nome do contato',
            'telefone': 'Telefone do contato',
            'setor': 'Setor de atendimento'
        };

        const varOpen = '{' + '{';
        const varClose = '}' + '}';

        Object.entries(variables).forEach(([key, description]) => {
            const item = document.createElement('div');
            item.className = 'variable-item px-4 py-2 cursor-pointer hover:bg-gray-100 text-sm';
            item.innerHTML = '<strong>' + varOpen + key + varClose + '</strong> - ' + description;
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
        const varOpen = '{' + '{';
        const varClose = '}' + '}';

        // Find the closest textarea
        const textarea = button.closest('.relative').querySelector('textarea');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        const before = text.substring(0, start);
        const after = text.substring(end);
        const newText = before + varOpen + varName + varClose + after;

        textarea.value = newText;
        textarea.selectionStart = textarea.selectionEnd = start + varName.length + 4;

        textarea.dispatchEvent(new Event('input', { bubbles: true }));

        const dropdown = document.querySelector('.variable-dropdown');
        if (dropdown) {
            dropdown.remove();
        }
    }
</script>
@endsection
