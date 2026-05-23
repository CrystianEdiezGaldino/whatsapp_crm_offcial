@extends('layouts.app')

@section('title', 'Editar Atendente')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-outline-variant flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Editar Atendente</h1>
            <p class="text-xs text-on-surface-variant mt-1">Atualize as informações de {{ $agent->name }}</p>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
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

                <form action="{{ route('admin.agents.update', $agent->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name', $agent->name) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('name') ? 'border-error' : '' }}" required>
                        @error('name')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $agent->email) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('email') ? 'border-error' : '' }}" required>
                        @error('email')<span class="text-error text-xs">{{ $message }}</span>@enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Status</label>
                        <select name="status" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                            <option value="offline" {{ old('status', $agent->status) === 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="online" {{ old('status', $agent->status) === 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>

                    <!-- Max Conversations -->
                    <div>
                        <label class="text-sm font-semibold text-on-surface block mb-2">Máximo de Conversas Simultâneas</label>
                        <input type="number" name="max_conversations" value="{{ old('max_conversations', $agent->agentCapacity?->max_conversations ?? 10) }}" min="1" max="100" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $errors->has('max_conversations') ? 'border-error' : '' }}" required>
                        @error('max_conversations')<span class="text-error text-xs">{{ $message }}</span>@enderror
                        <p class="text-xs text-on-surface-variant mt-1">Quantas conversas este atendente pode atender simultaneamente</p>
                    </div>

                    <!-- Current Load -->
                    <div class="p-4 bg-surface-container rounded-lg">
                        <p class="text-sm text-on-surface-variant mb-2">Carga Atual</p>
                        <p class="text-2xl font-bold text-on-surface">
                            {{ $agent->agentCapacity?->activeConversationsCount() ?? 0 }}/{{ $agent->agentCapacity?->max_conversations ?? 10 }} conversas
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between gap-2 pt-4 border-t border-outline-variant">
                        <div>
                            @if($agent->conversations()->whereIn('status', ['new', 'in_attendance'])->count() === 0)
                            <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este atendente?');" class="inline">
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
                        <div class="flex gap-2">
                            <a href="{{ route('admin.agents.index') }}" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 active:scale-95 transition-all">
                                <span class="material-symbols-outlined inline text-sm mr-1">save</span> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
