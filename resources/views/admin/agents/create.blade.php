@extends('layouts.app')

@section('title', 'Cadastrar Atendente')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="h-[66px] shrink-0 flex items-center gap-3 px-6 bg-white border-b border-[#E8EAF0]">
        <a href="{{ route('admin.agents.index') }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-gray-50 text-gray-400 hover:text-gray-700 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </a>
        <h1 class="text-lg font-extrabold text-gray-900">Novo Atendente</h1>
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

                <form action="{{ route('admin.agents.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome Completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="input-primary" placeholder="Digite o nome completo" required>
                        @error('name')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-primary" placeholder="email@exemplo.com" required>
                        @error('email')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Senha</label>
                            <input type="password" name="password" class="input-primary" placeholder="Mínimo 8 caracteres" required>
                            @error('password')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Confirmar Senha</label>
                            <input type="password" name="password_confirmation" class="input-primary" placeholder="Repita a senha" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Cargo</label>
                            <select name="role" class="select-primary" required>
                                <option value="">Selecione</option>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Setor</label>
                            <select name="sector_id" class="select-primary">
                                <option value="">Selecione</option>
                                @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                @endforeach
                            </select>
                            @error('sector_id')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Observações</label>
                        <textarea name="notes" rows="3" class="textarea-primary" placeholder="Anotações sobre este atendente">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="toggle-switch {{ old('is_active', true) ? 'active' : '' }}" onclick="this.classList.toggle('active'); this.nextElementSibling.value = this.classList.contains('active') ? '1' : '0'"></div>
                        <input type="hidden" name="is_active" value="{{ old('is_active', true) ? '1' : '0' }}">
                        <span class="text-sm text-gray-700 font-medium">Ativado</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-[#F2F4F8]">
                        <a href="{{ route('admin.agents.index') }}" class="btn-secondary text-sm px-4 py-2">Cancelar</a>
                        <button type="submit" class="btn-primary text-sm px-4 py-2">
                            <span class="material-symbols-outlined text-[16px] mr-1">save</span>
                            Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
