@extends('layouts.app')

@section('title', 'Setor: ' . $sector->name)

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">{{ $sector->name }}</h1>
            <p class="text-xs text-gray-600 mt-1">Opção {{ $sector->keyboard_option }} do pré-atendimento IVR</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.sectors.edit', $sector->id) }}" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
                <span class="material-symbols-outlined inline text-sm mr-1">edit</span> Editar
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="max-w-4xl space-y-6">
            <!-- Sector Info -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Keyboard Option -->
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-1">Opção do Teclado</p>
                        <p class="text-3xl font-bold text-secondary">{{ $sector->keyboard_option }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <p class="text-xs text-gray-600 font-semibold mb-1">Status</p>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium {{ $sector->is_active ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                            <span class="w-2 h-2 rounded-full {{ $sector->is_active ? 'bg-secondary' : 'bg-on-surface-variant' }}"></span>
                            {{ $sector->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                </div>

                <!-- Description -->
                @if($sector->description)
                <div class="mb-6">
                    <p class="text-xs text-gray-600 font-semibold mb-2">Descrição</p>
                    <p class="text-sm text-on-surface">{{ $sector->description }}</p>
                </div>
                @endif

                <!-- Greeting Message -->
                <div>
                    <p class="text-xs text-gray-600 font-semibold mb-2">Mensagem de Boas-vindas</p>
                    <div class="p-3 bg-gray-100 rounded-lg text-sm text-on-surface">
                        {{ $sector->getGreetingOrDefault() }}
                    </div>
                </div>
            </div>

            <!-- Agents in This Sector -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-on-surface">Atendentes do Setor</h2>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium bg-gray-100-low text-gray-600">
                        <span class="material-symbols-outlined text-sm">people</span>
                        {{ $sector->getActiveAgentCountAttribute() }}/{{ $sector->getAgentCountAttribute() }}
                    </span>
                </div>

                @if($sector->agents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100-low border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Nome</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Email</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Cargo</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Status Online</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Ativo</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Conversas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($sector->agents as $agent)
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
                                <td class="px-4 py-3 text-sm text-on-surface">{{ $agent->getRoleLabel() }}</td>
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
                                <td class="px-4 py-3 text-gray-600 text-sm">
                                    {{ $agent->agentCapacity?->activeConversationsCount() ?? 0 }}/{{ $agent->agentCapacity?->max_conversations ?? 10 }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <span class="material-symbols-outlined text-4xl text-gray-600 mb-2">people_outline</span>
                    <p class="text-gray-600 text-sm">Nenhum atendente neste setor</p>
                </div>
                @endif
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ route('admin.sectors.index') }}" class="text-secondary hover:text-secondary/80 font-semibold text-sm">
                    <span class="material-symbols-outlined inline text-sm mr-1">arrow_back</span> Voltar aos Setores
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
