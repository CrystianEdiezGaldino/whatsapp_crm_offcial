@extends('layouts.app')

@section('title', 'Atendentes & Setores')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="h-[66px] shrink-0 flex items-center justify-between px-6 bg-white border-b border-[#E8EAF0]">
        <h1 class="text-lg font-extrabold text-gray-900">Atendentes & Setores</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.sectors.create') }}" class="bg-white border border-[#E2E5EE] text-gray-700 px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-gray-50 flex items-center gap-1.5 transition-all">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Novo setor
            </a>
            <a href="{{ route('admin.agents.create') }}" class="bg-primary text-white px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-primary-dark flex items-center gap-1.5 transition-all">
                <span class="material-symbols-outlined text-[16px]">person_add</span>
                Novo atendente
            </a>
        </div>
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

        <!-- Sector Cards -->
        @if($sectors->count() > 0)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            @foreach($sectors as $sector)
            @php
                $colors = ['#4353E8', '#1DA85A', '#D97706', '#D1383E', '#8B5CF6', '#06B6D4'];
                $sectorColor = $colors[$loop->index % count($colors)];
            @endphp
            <div class="kpi-card">
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background: {{ $sectorColor }}"></span>
                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $sector->name }}</h3>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[12px] text-gray-500 font-medium">{{ $sector->agents_count }} atendente{{ $sector->agents_count !== 1 ? 's' : '' }}</span>
                    @if($sector->sla_target_minutes)
                    <span class="sla-badge ok text-[10px]">SLA {{ $sector->sla_target_minutes }}min</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Agents Table -->
        <div class="card-primary !p-0 overflow-hidden">
            @if($agents->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#F2F4F8]">
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Atendente</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Setor</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Chats ativos</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Ativo</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                        <tr class="border-b border-[#F2F4F8] hover:bg-[#F7F8FB] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-[38px] h-[38px] rounded-full bg-[#EEF0FE] text-secondary flex items-center justify-center text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[13.5px] font-bold text-gray-900 truncate">{{ $agent->name }}</p>
                                        <p class="text-[11.5px] text-gray-400 font-medium">{{ $agent->getRoleLabel() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="sector-tag">{{ $agent->getSectorName() }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold {{ $agent->status === 'online' ? 'text-primary' : 'text-gray-400' }}">
                                    <span class="w-2 h-2 rounded-full {{ $agent->status === 'online' ? 'bg-primary' : 'bg-gray-300' }}"></span>
                                    {{ $agent->status === 'online' ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @php $activeChats = \App\Models\Conversation::where('claimed_by', $agent->id)->whereIn('status', ['new', 'in_attendance'])->count(); @endphp
                                <span class="text-[13px] font-bold text-gray-700">{{ $activeChats }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="toggle-switch {{ $agent->is_active ? 'active' : '' }}" title="{{ $agent->is_active ? 'Ativo' : 'Inativo' }}"></div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1 justify-end">
                                    <a href="{{ route('admin.agents.show', $agent) }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-[#F0F2F7] text-gray-400 hover:text-gray-700 transition-colors" title="Visualizar">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.agents.edit', $agent) }}" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-[#EEF0FE] text-gray-400 hover:text-secondary transition-colors" title="Editar">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    @if(\App\Models\Conversation::where('claimed_by', $agent->id)->whereIn('status', ['new', 'in_attendance'])->count() === 0 && !$agent->isAdmin())
                                    <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Remover este atendente?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center hover:bg-[#FEF1F2] text-gray-400 hover:text-error transition-colors" title="Remover">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($agents->hasPages())
            <div class="p-4 border-t border-[#F2F4F8]">
                {{ $agents->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-16">
                <span class="material-symbols-outlined text-5xl text-gray-200 block mb-3">people</span>
                <p class="text-sm text-gray-400 font-semibold mb-4">Nenhum atendente cadastrado</p>
                <a href="{{ route('admin.agents.create') }}" class="bg-primary text-white px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-primary-dark inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    Cadastrar primeiro atendente
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
