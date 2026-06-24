@extends('layouts.app')

@section('title', 'Editar Atendente')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="h-[66px] shrink-0 flex items-center gap-3 px-6 bg-white border-b border-[#E8EAF0]">
        <a href="{{ route('admin.agents.index') }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-gray-50 text-gray-400 hover:text-gray-700 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </a>
        <h1 class="text-lg font-extrabold text-gray-900">Editar Atendente</h1>
        <span class="text-sm text-gray-400 font-medium">{{ $user->name }}</span>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto design-scrollbar p-6 bg-app-bg">
        <div class="max-w-xl mx-auto">
            <div class="card-primary">
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

                <form action="{{ route('admin.agents.update', $user) }}" method="POST" class="space-y-5">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-primary" required>
                        @error('name')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-primary" required>
                        @error('email')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Cargo</label>
                            <select name="role" class="select-primary" required>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Setor</label>
                            <select name="sector_id" class="select-primary">
                                <option value="">Selecione</option>
                                @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id', $user->sector_id) == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                @endforeach
                            </select>
                            @error('sector_id')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Observações</label>
                        <textarea name="notes" rows="3" class="textarea-primary" placeholder="Anotações sobre este atendente">{{ old('notes', $user->notes) }}</textarea>
                    </div>

                    <!-- Current Load KPI -->
                    <div class="kpi-card !bg-[#F7F8FB]">
                        <p class="kpi-label">Carga Atual</p>
                        <p class="kpi-value text-2xl mt-1">{{ $user->agentCapacity?->activeConversationsCount() ?? 0 }}<span class="text-sm font-semibold text-gray-400">/{{ $user->agentCapacity?->max_conversations ?? 10 }}</span></p>
                        <p class="text-xs text-gray-400 mt-1">conversas ativas</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="toggle-switch {{ old('is_active', $user->is_active) ? 'active' : '' }}" onclick="this.classList.toggle('active'); this.nextElementSibling.value = this.classList.contains('active') ? '1' : '0'"></div>
                        <input type="hidden" name="is_active" value="{{ old('is_active', $user->is_active) ? '1' : '0' }}">
                        <span class="text-sm text-gray-700 font-medium">Ativado</span>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-[#F2F4F8]">
                        <div>
                            @if(\App\Models\Conversation::where('claimed_by', $user->id)->whereIn('status', ['new', 'in_attendance'])->count() === 0 && !$user->isAdmin())
                            <button type="button" onclick="document.getElementById('deleteAgentForm').submit()" class="bg-white border border-error/30 text-error px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-error/5 flex items-center gap-1.5 transition-all">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                Excluir
                            </button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.agents.index') }}" class="btn-secondary text-sm px-4 py-2">Cancelar</a>
                            <button type="submit" class="btn-primary text-sm px-4 py-2">
                                <span class="material-symbols-outlined text-[16px] mr-1">save</span>
                                Salvar
                            </button>
                        </div>
                    </div>
                </form>
                @if(\App\Models\Conversation::where('claimed_by', $user->id)->whereIn('status', ['new', 'in_attendance'])->count() === 0 && !$user->isAdmin())
                <form id="deleteAgentForm" action="{{ route('admin.agents.destroy', $user) }}" method="POST" class="hidden" onsubmit="return confirm('Remover este atendente?');">
                    @csrf @method('DELETE')
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
