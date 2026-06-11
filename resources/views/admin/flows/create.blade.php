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
            <textarea id="initial_message" name="config[initial_message]" required class="textarea-nm" rows="4" placeholder="Digite a mensagem de boas-vindas...">{{ old('config.initial_message') }}</textarea>
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
            <textarea id="final_message" name="config[final_message]" required class="textarea-nm" rows="3" placeholder="Mensagem de confirmação...">{{ old('config.final_message') }}</textarea>
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
    btn.closest('.option-block').remove();
}
</script>
@endsection
