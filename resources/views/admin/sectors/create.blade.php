@extends('layouts.app')

@section('title', 'Criar Setor')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Criar Setor</h1>
            <p class="text-xs text-gray-600 mt-1">Crie um novo setor para o pré-atendimento</p>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                @if($errors->any())
                <div class="mb-6 p-4 bg-error/10 border border-error text-error rounded-lg">
                    <strong>Erros encontrados:</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.sectors.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Keyboard Option -->
                    <div class="p-4 bg-gray-100 rounded-lg border border-gray-200">
                        <label class="text-sm font-semibold text-on-surface block mb-2">Opção do Teclado (0-9)</label>
                        <div class="text-3xl font-bold text-secondary mb-2">{{ $nextOption }}</div>
                        <input type="hidden" name="keyboard_option" value="{{ $nextOption }}">
                        <p class="text-xs text-gray-600">Esta opção será usada no pré-atendimento IVR. O cliente digita {{ $nextOption }} para acessar este setor.</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Nome do Setor</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('name') ? 'border-error' : '' }}" placeholder="Ex: Financeiro, Secretaria, Vendas" required>
                        @error('name')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Descrição</label>
                        <textarea name="description" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('description') ? 'border-error' : '' }}" placeholder="Descrição do setor (opcional)" rows="2">{{ old('description') }}</textarea>
                        @error('description')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Greeting Message -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Mensagem de Boas-vindas</label>
                        <textarea name="greeting_message" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('greeting_message') ? 'border-error' : '' }}" placeholder="Mensagem enviada quando o cliente seleciona este setor (opcional)" rows="3">{{ old('greeting_message') }}</textarea>
                        @error('greeting_message')<span class="text-error text-xs">{{ $message }}</span>@enderror
                        <p class="text-xs text-gray-600 mt-1">Se deixar em branco, usará: &quot;Você foi redirecionado para [Nome do Setor]. Um atendente irá responder em breve.&quot;</p>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Status</label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200">
                            <span class="text-sm text-on-surface">Ativado</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-1">Setores inativos não aparecem no pré-atendimento IVR</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.sectors.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                            <span class="material-symbols-outlined inline text-sm mr-1">save</span> Criar Setor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
