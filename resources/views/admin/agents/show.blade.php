@extends('layouts.app')

@section('title', 'Atendente: ' . $user->name)

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Topbar -->
    <div class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">{{ $user->name }}</h1>
            <p class="text-xs text-gray-600 mt-1">{{ $user->getRoleLabel() }} • {{ $user->getSectorName() }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.agents.edit', $user->id) }}" class="px-4 py-2 bg-secondary text-on-secondary rounded-lg font-semibold hover:bg-secondary/90 transition-all">
                <span class="material-symbols-outlined inline text-sm mr-1">edit</span> Editar
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="max-w-4xl space-y-6">
            <!-- Agent Info -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-on-surface mb-4">Informações de Contato</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-1">Email</p>
                                <p class="text-sm text-on-surface">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-1">Status Online</p>
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium {{ $user->status === 'online' ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                                    <span class="w-2 h-2 rounded-full {{ $user->status === 'online' ? 'bg-secondary' : 'bg-on-surface-variant' }}"></span>
                                    {{ $user->status === 'online' ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Sector -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-on-surface mb-4">Função</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-1">Cargo</p>
                                <p class="text-sm text-on-surface">{{ $user->getRoleLabel() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-1">Setor</p>
                                <p class="text-sm text-on-surface">{{ $user->getSectorName() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-1">Status</p>
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $user->is_active ? 'bg-secondary-100/20 text-secondary' : 'bg-outline-variant/20 text-gray-600' }}">
                                    {{ $user->is_active ? 'Ativado' : 'Desativado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Capacity -->
                    @if($user->agentCapacity)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-on-surface mb-4">Capacidade</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-600 font-semibold mb-2">Conversas Ativas</p>
                                <div class="flex items-end gap-3">
                                    <div class="text-3xl font-bold text-secondary">{{ $user->agentCapacity->activeConversationsCount() }}</div>
                                    <div class="text-gray-600 text-sm">de {{ $user->agentCapacity->max_conversations }}</div>
                                </div>
                                <div class="w-full bg-outline-variant rounded-full h-2 mt-3">
                                    <div class="bg-secondary h-2 rounded-full" style="width: {{ ($user->agentCapacity->activeConversationsCount() / $user->agentCapacity->max_conversations) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-xs text-gray-600 font-semibold mb-1">Máximo Permitido</p>
                                <p class="text-sm text-on-surface">{{ $user->agentCapacity->max_conversations }} conversas simultâneas</p>
                            </div>
                            @if(!$user->agentCapacity->is_active)
                            <div class="p-3 bg-outline-variant/20 rounded-lg">
                                <p class="text-xs text-gray-600">Não está recebendo novas conversas</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($user->notes)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-on-surface mb-4">Observações</h2>
                        <p class="text-sm text-on-surface">{{ $user->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Active Conversations -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">Conversas Ativas</h2>
                @if($user->conversations()->whereIn('status', ['new', 'in_attendance'])->count() > 0)
                <div class="space-y-3">
                    @foreach($user->conversations()->whereIn('status', ['new', 'in_attendance'])->latest()->get() as $conversation)
                    <div class="flex items-start justify-between p-3 rounded-lg bg-gray-100-low hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <p class="font-medium text-on-surface">{{ $conversation->contact->name ?? 'Contato Desconhecido' }}</p>
                            <p class="text-xs text-gray-600">{{ $conversation->contact->phone ?? '—' }}</p>
                            <p class="text-xs text-gray-600 mt-1">
                                @if($conversation->status === 'in_attendance')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-100/20 text-secondary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                        Em atendimento
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-outline-variant/20 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></span>
                                        Nova
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="text-right text-xs text-gray-600">
                            {{ $conversation->created_at->format('H:i') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <span class="material-symbols-outlined text-4xl text-gray-600 mb-2">chat_bubble_outline</span>
                    <p class="text-gray-600 text-sm">Nenhuma conversa ativa</p>
                </div>
                @endif
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ route('admin.agents.index') }}" class="text-secondary hover:text-secondary/80 font-semibold text-sm">
                    <span class="material-symbols-outlined inline text-sm mr-1">arrow_back</span> Voltar aos Atendentes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
