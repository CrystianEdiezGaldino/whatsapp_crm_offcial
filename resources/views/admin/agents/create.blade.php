@extends('layouts.app')

@section('title', 'Cadastrar Atendente')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Cadastrar Atendente</h1>
            <p class="text-xs text-gray-600 mt-1">Crie uma nova conta de atendente</p>
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

                <form action="{{ route('admin.agents.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('name') ? 'border-error' : '' }}" placeholder="Digite o nome completo" required>
                        @error('name')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('email') ? 'border-error' : '' }}" placeholder="Digite o email" required>
                        @error('email')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Senha</label>
                        <input type="password" name="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('password') ? 'border-error' : '' }}" placeholder="Mínimo 8 caracteres" required>
                        @error('password')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Confirmar Senha</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Repita a senha" required>
                        @error('password_confirmation')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Cargo</label>
                        <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('role') ? 'border-error' : '' }}" required onchange="this.form.dataset.role = this.value">
                            <option value="">Selecione um cargo</option>
                            @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Sector (required for agent/supervisor) -->
                    <div id="sector-field">
                        <label class="text-sm font-semibold text-on-surface block mb-2">Setor</label>
                        <select name="sector_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('sector_id') ? 'border-error' : '' }}">
                            <option value="">Selecione um setor</option>
                            @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                                {{ $sector->keyboard_option }}. {{ $sector->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('sector_id')<span class="text-error text-xs">{{ $message }}</span>@enderror
                        <p class="text-xs text-gray-600 mt-1">Obrigatório para Atendentes e Supervisores</p>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Status</label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200">
                            <span class="text-sm text-on-surface">Ativado</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-1">Atendentes inativos não recebem novas conversas</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Observações</label>
                        <textarea name="notes" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('notes') ? 'border-error' : '' }}" placeholder="Anotações sobre este atendente" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.agents.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                            <span class="material-symbols-outlined inline text-sm mr-1">save</span> Cadastrar Atendente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
