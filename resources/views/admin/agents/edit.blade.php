@extends('layouts.app')

@section('title', 'Editar Atendente')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="page-header sticky top-0 z-40">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Editar Atendente</h1>
            <p class="text-xs text-gray-600 mt-1">Atualize as informações de {{ $user->name }}</p>
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

                <form action="{{ route('admin.agents.update', $user) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('name') ? 'border-error' : '' }}" required>
                        @error('name')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('email') ? 'border-error' : '' }}" required>
                        @error('email')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Cargo</label>
                        <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('role') ? 'border-error' : '' }}" required>
                            @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Setor</label>
                        <select name="sector_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('sector_id') ? 'border-error' : '' }}">
                            <option value="">Selecione um setor</option>
                            @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id', $user->sector_id) == $sector->id ? 'selected' : '' }}>
                                {{ $sector->keyboard_option }}. {{ $sector->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('sector_id')<span class="text-error text-xs">{{ $message }}</span>@enderror
                        <p class="text-xs text-gray-600 mt-1">Obrigatório para Atendentes e Supervisores</p>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Status do Atendente</label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200">
                            <span class="text-sm text-on-surface">Ativado</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-1">Atendentes inativos não recebem novas conversas</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Observações</label>
                        <textarea name="notes" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('notes') ? 'border-error' : '' }}" placeholder="Anotações sobre este atendente" rows="3">{{ old('notes', $user->notes) }}</textarea>
                        @error('notes')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Current Load -->
                    <div class="p-4 bg-gray-100 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Carga Atual</p>
                        <p class="text-2xl font-bold text-on-surface">
                            {{ $user->agentCapacity?->activeConversationsCount() ?? 0 }}/{{ $user->agentCapacity?->max_conversations ?? 10 }} conversas
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.agents.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                            <span class="material-symbols-outlined inline text-sm mr-1">save</span> Salvar Alterações
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    @if($user->conversations()->whereIn('status', ['new', 'in_attendance'])->count() === 0)
                    <form action="{{ route('admin.agents.destroy', $user) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este atendente?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-error text-on-error rounded-lg font-semibold hover:bg-error/90 active:scale-95 transition-all">
                            <span class="material-symbols-outlined inline text-sm mr-1">delete</span> Deletar Atendente
                        </button>
                    </form>
                    @else
                    <p class="text-xs text-error">Não é possível deletar atendente com conversas ativas</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
