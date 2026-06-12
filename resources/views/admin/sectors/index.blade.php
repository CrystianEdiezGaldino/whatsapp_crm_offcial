@extends('layouts.app')

@section('title', 'Gerenciar Setores')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Setores</h1>
            <p class="text-xs text-gray-600 mt-1">Gerencie os setores do pré-atendimento</p>
        </div>
        <a href="{{ route('admin.sectors.create') }}" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
            <span class="material-symbols-outlined inline text-sm mr-1">add</span> Novo Setor
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
            <!-- Sectors Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                @if($sectors->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100-low border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Opção</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Setor</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Descrição</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Atendentes</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($sectors as $sector)
                            <tr class="hover:bg-gray-100-low">
                                <td class="px-4 py-3">
                                    <div class="w-10 h-10 bg-primary-fixed rounded flex items-center justify-center font-bold text-on-primary-fixed text-lg">
                                        {{ $sector->keyboard_option }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-on-surface">{{ $sector->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">
                                    {{ $sector->description ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full text-xs font-medium bg-gray-100-low text-gray-600">
                                        <span class="material-symbols-outlined text-sm">people</span>
                                        {{ $sector->getActiveAgentCountAttribute() }}/{{ $sector->getAgentCountAttribute() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $sector->is_active ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                                        {{ $sector->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.sectors.show', $sector->id) }}" class="text-primary hover:text-primary/80 font-semibold text-xs">
                                            <span class="material-symbols-outlined inline text-sm">visibility</span> Visualizar
                                        </a>
                                        <a href="{{ route('admin.sectors.edit', $sector->id) }}" class="text-secondary hover:text-secondary/80 font-semibold text-xs">
                                            <span class="material-symbols-outlined inline text-sm">edit</span> Editar
                                        </a>
                                        @if($sector->getAgentCountAttribute() === 0)
                                        <form action="{{ route('admin.sectors.destroy', $sector->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este setor?');" class="inline">
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
                    {{ $sectors->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-600 mb-4">category</span>
                    <p class="text-gray-600 text-lg mb-4">Nenhum setor cadastrado</p>
                    <a href="{{ route('admin.sectors.create') }}" class="inline-block px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
                        <span class="material-symbols-outlined inline text-sm mr-1">add</span> Criar Primeiro Setor
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
