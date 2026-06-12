@extends('layouts.app')

@section('title', 'Gerenciar Atendentes')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Atendentes</h1>
            <p class="text-xs text-gray-600 mt-1">Gerencie os atendentes do sistema</p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
            <span class="material-symbols-outlined inline text-sm mr-1">add</span> Novo Atendente
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="mx-6 mt-4 p-4 bg-secondary-100/20 border border-secondary text-on-surface rounded-lg flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary mt-1">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mx-6 mt-4 p-4 bg-error/10 border border-error text-error rounded-lg flex items-start gap-3">
        <span class="material-symbols-outlined mt-1">error</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="max-w-7xl">
            <!-- Agents Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                @if($agents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100-low border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Atendente</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Email</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Cargo</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Setor</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ativo</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($agents as $agent)
                            <tr class="hover:bg-gray-100-low">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center text-xs font-bold text-on-primary-fixed">
                                            {{ $agent->name[0] }}
                                        </div>
                                        <span class="font-medium text-on-surface">{{ $agent->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $agent->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-on-surface">{{ $agent->getRoleLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-on-surface">{{ $agent->getSectorName() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $agent->status === 'online' ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                                        <span class="w-2 h-2 rounded-full {{ $agent->status === 'online' ? 'bg-secondary' : 'bg-on-surface-variant' }}"></span>
                                        {{ $agent->status === 'online' ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $agent->is_active ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                                        {{ $agent->is_active ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.agents.edit', $agent->id) }}" class="text-secondary hover:text-secondary/80 font-semibold text-xs">
                                            <span class="material-symbols-outlined inline text-sm">edit</span> Editar
                                        </a>
                                        @if($agent->conversations()->whereIn('status', ['new', 'in_attendance'])->count() === 0 && !$agent->isAdmin())
                                        <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este atendente?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error hover:text-error/80 font-semibold text-xs">
                                                <span class="material-symbols-outlined inline text-sm">delete</span> Deletar
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

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $agents->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-600 mb-4">person_add</span>
                    <p class="text-gray-600 text-lg mb-4">Nenhum atendente cadastrado</p>
                    <a href="{{ route('admin.agents.create') }}" class="inline-block px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
                        <span class="material-symbols-outlined inline text-sm mr-1">add</span> Cadastrar Primeiro Atendente
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
