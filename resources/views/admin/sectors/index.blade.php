@extends('layouts.app')

@section('title', 'Setores')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="h-[66px] shrink-0 flex items-center justify-between px-6 bg-white border-b border-[#E8EAF0]">
        <h1 class="text-lg font-extrabold text-gray-900">Setores</h1>
        <a href="{{ route('admin.sectors.create') }}" class="bg-primary text-white px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-primary-dark flex items-center gap-1.5 transition-all">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Novo setor
        </a>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto design-scrollbar p-6 bg-app-bg">
        @if(session('success'))
        <div class="mb-4 p-3 bg-[#E8F8EF] border border-[#C8EDD8] rounded-[11px] text-primary text-sm flex items-center gap-2 font-medium">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 p-3 bg-[#FEF1F2] border border-error/20 rounded-[11px] text-error text-sm flex items-center gap-2 font-medium">
            <span class="material-symbols-outlined text-lg">error</span>
            {{ session('error') }}
        </div>
        @endif

        <!-- Sector Cards Grid -->
        @if($sectors->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($sectors as $sector)
            @php
                $colors = ['#4353E8', '#1DA85A', '#D97706', '#D1383E', '#8B5CF6', '#06B6D4', '#EC4899', '#F97316'];
                $sectorColor = $colors[$loop->index % count($colors)];
            @endphp
            <div class="card-primary hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background: {{ $sectorColor }}"></span>
                        <h3 class="text-[14px] font-bold text-gray-900">{{ $sector->name }}</h3>
                    </div>
                    <div class="toggle-switch {{ $sector->is_active ? 'active' : '' }}" title="{{ $sector->is_active ? 'Ativo' : 'Inativo' }}"></div>
                </div>

                @if($sector->description)
                <p class="text-[12px] text-gray-500 mb-3 line-clamp-2">{{ $sector->description }}</p>
                @endif

                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center gap-1.5 text-[12px] text-gray-500 font-medium">
                        <span class="material-symbols-outlined text-[15px]">people</span>
                        {{ $sector->getActiveAgentCountAttribute() }}/{{ $sector->getAgentCountAttribute() }} atendentes
                    </div>
                    @if($sector->keyboard_option)
                    <span class="shortcut-badge text-[10px]">{{ $sector->keyboard_option }}</span>
                    @endif
                </div>

                @if($sector->sla_target_minutes)
                <div class="mb-3">
                    <span class="sla-badge ok text-[10px]">
                        <span class="material-symbols-outlined text-[12px]">schedule</span>
                        SLA {{ $sector->sla_target_minutes }}min
                    </span>
                </div>
                @endif

                <div class="flex items-center gap-1 pt-3 border-t border-[#F2F4F8]">
                    <a href="{{ route('admin.sectors.show', $sector->id) }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-gray-50 text-gray-400 hover:text-gray-700 transition-colors" title="Visualizar">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </a>
                    <a href="{{ route('admin.sectors.edit', $sector->id) }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-[#EEF0FE] text-gray-400 hover:text-secondary transition-colors" title="Editar">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </a>
                    @if($sector->getAgentCountAttribute() === 0)
                    <form action="{{ route('admin.sectors.destroy', $sector->id) }}" method="POST" onsubmit="return confirm('Remover este setor?');" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-[#FEF1F2] text-gray-400 hover:text-error transition-colors" title="Remover">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($sectors->hasPages())
        <div class="mt-6">
            {{ $sectors->links() }}
        </div>
        @endif
        @else
        <div class="card-primary text-center py-16">
            <span class="material-symbols-outlined text-5xl text-gray-200 block mb-3">category</span>
            <p class="text-sm text-gray-400 font-semibold mb-4">Nenhum setor cadastrado</p>
            <a href="{{ route('admin.sectors.create') }}" class="bg-primary text-white px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-primary-dark inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Criar primeiro setor
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
