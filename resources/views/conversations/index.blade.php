@extends('layouts.app', ['hideSidebar' => true])

@section('title', 'Chats')

@section('content')
<div class="flex h-[calc(100vh)] w-full">
    <!-- Mini Sidebar -->
    <aside class="w-[68px] h-screen bg-primary-container flex flex-col items-center py-4 border-r border-outline-variant shrink-0 z-50">
        <div class="w-10 h-10 bg-secondary-fixed rounded flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-on-secondary-fixed text-lg">hub</span>
        </div>
        <nav class="flex-1 flex flex-col items-center gap-2">
            @php $current = request()->route()->getName() ?? ''; @endphp
            <a href="{{ route('dashboard') }}" title="Dashboard" class="w-10 h-10 rounded-lg flex items-center justify-center {{ $current === 'dashboard' ? 'bg-surface-container-highest/10 text-secondary-container' : 'text-on-primary-container/70 hover:bg-primary/50 hover:text-on-primary' }} transition-colors">
                <span class="material-symbols-outlined text-xl">dashboard</span>
            </a>
            <a href="{{ route('conversations.index') }}" title="Chats" class="w-10 h-10 rounded-lg flex items-center justify-center {{ str_starts_with($current, 'conversations') ? 'bg-surface-container-highest/10 text-secondary-container' : 'text-on-primary-container/70 hover:bg-primary/50 hover:text-on-primary' }} transition-colors">
                <span class="material-symbols-outlined text-xl">chat</span>
            </a>
            <a href="{{ route('contacts.index') }}" title="Contatos" class="w-10 h-10 rounded-lg flex items-center justify-center text-on-primary-container/70 hover:bg-primary/50 hover:text-on-primary transition-colors">
                <span class="material-symbols-outlined text-xl">person_book</span>
            </a>
            <a href="{{ route('macros.index') }}" title="Macros" class="w-10 h-10 rounded-lg flex items-center justify-center text-on-primary-container/70 hover:bg-primary/50 hover:text-on-primary transition-colors">
                <span class="material-symbols-outlined text-xl">bolt</span>
            </a>
        </nav>
        <div class="mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sair" class="w-10 h-10 rounded-lg flex items-center justify-center text-on-primary-container/70 hover:text-on-primary transition-colors">
                    <span class="material-symbols-outlined text-xl">logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Left: Conversation List -->
    <section class="w-[360px] border-r border-outline-variant bg-gradient-to-b from-white to-slate-50/50 flex flex-col overflow-hidden shrink-0">
        <div class="p-4 border-b border-outline-variant/50 bg-white/70 backdrop-blur-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-on-surface">💬 Chats Ativos</h2>
                @if($pendingCount > 0)
                <span class="bg-error text-on-error text-xs font-bold px-3 py-1 rounded-full animate-pulse shadow-md">
                    {{ $pendingCount }} pendente{{ $pendingCount !== 1 ? 's' : '' }}
                </span>
                @endif
            </div>
            <div class="flex gap-2 flex-wrap">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('conversations.index') }}" class="px-3 py-1.5 text-xs rounded-lg font-medium transition-all {{ !request('assigned') && !request('status') ? 'bg-primary text-on-primary shadow-md' : 'text-on-surface-variant hover:bg-surface-container border border-outline-variant/50' }}">
                    Todos <span class="text-[10px] ml-1 opacity-75">({{ $totalCount }})</span>
                </a>
                @endif
                <a href="{{ route('conversations.index', ['status' => 'pending']) }}" class="px-3 py-1.5 text-xs rounded-lg font-medium transition-all flex items-center gap-1 {{ request('status') === 'pending' ? 'bg-error text-on-error shadow-md' : 'text-on-surface-variant hover:bg-surface-container border border-outline-variant/50' }}">
                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                    Pendentes
                    @if($pendingCount > 0)
                    <span class="text-[10px] ml-1 font-bold">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('conversations.index', ['assigned' => 'mine']) }}" class="px-3 py-1.5 text-xs rounded-lg font-medium transition-all {{ request('assigned') === 'mine' ? 'bg-primary text-on-primary shadow-md' : 'text-on-surface-variant hover:bg-surface-container border border-outline-variant/50' }}">Meus</a>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto custom-scrollbar space-y-2 p-2">
            @forelse($conversations as $conv)
            @php
                $convPending = !$conv->getActiveClaim();
                $convResolved = $conv->status === 'resolved' || $conv->status === 'closed';
                $shouldShow = !request('status') || (request('status') === 'pending' && $convPending);
            @endphp
            @if($shouldShow && $conv->contact)
            <a href="{{ route('conversations.index', ['conversation' => $conv->id] + request()->all()) }}" class="group flex gap-3 p-3 cursor-pointer transition-all rounded-lg backdrop-blur-sm border {{ ($activeConversation?->id === $conv->id) ? 'bg-primary/10 border-primary/30 shadow-md' : ($convPending ? 'bg-error/10 border-error/30 hover:bg-error/20 hover:shadow-sm' : ($convResolved ? 'bg-surface-container border-surface-container opacity-60 hover:opacity-80 hover:shadow-sm' : 'bg-white/60 border-outline-variant/30 hover:bg-white/80 hover:shadow-sm hover:border-outline-variant/50')) }}">
                <div class="relative shrink-0">
                    <div class="w-12 h-12 rounded-full {{ $convPending ? 'bg-gradient-to-br from-error to-error/80' : 'bg-gradient-to-br from-primary-fixed to-primary-fixed/80' }} flex items-center justify-center font-bold text-sm {{ $convPending ? 'text-on-error' : 'text-on-primary-fixed' }} shadow-sm">
                        {{ $conv->contact->initials }}
                    </div>
                    @if($convPending)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-error text-on-error rounded-full flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-md animate-bounce">!</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2 mb-1">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold {{ $convPending ? 'text-error' : 'text-on-surface' }} truncate">{{ $conv->contact->name }}</h3>
                            @if($convPending)
                            <span class="text-[10px] text-error font-semibold">⏱️ Pendente de atendimento</span>
                            @elseif($convResolved)
                            <span class="text-[10px] text-gray-500 font-semibold">✓ Encerrada</span>
                            @else
                            <span class="text-[10px] text-secondary font-semibold">🔒 {{ $conv->getActiveClaim()?->user->name ?? 'Sem atribuição' }}</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-on-surface-variant shrink-0 font-medium">{{ $conv->last_message_at?->diffForHumans(short: true) ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-on-surface-variant truncate mt-1 line-clamp-1">{{ $conv->lastMessage?->content ?? '(sem mensagens)' }}</p>
                </div>
            </a>
            @endif
            @empty
            <div class="p-12 text-center text-on-surface-variant text-sm">
                <span class="text-3xl block mb-2">💭</span>
                @if(request('status') === 'pending')
                    ✓ Nenhum atendimento pendente!
                @else
                    Nenhuma conversa ativa.
                @endif
            </div>
            @endforelse
        </div>
    </section>

    <!-- Center: Chat -->
    <section class="flex-1 flex flex-col bg-gradient-to-b from-white/50 to-slate-50/50 relative overflow-hidden">
        <!-- Toast de Notificação -->
        <div id="notificationToast" class="fixed bottom-6 right-6 bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg shadow-lg hidden transition-all duration-300 z-50 max-w-xs animate-slideInUp">
            <div class="flex items-center gap-3">
                <span class="text-lg" id="toastIcon">📩</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold" id="toastSender">Nova mensagem</p>
                    <p class="text-xs opacity-90" id="toastMessage">Você tem uma mensagem</p>
                </div>
                <button onclick="document.getElementById('notificationToast').classList.add('hidden')" class="text-white hover:opacity-75 flex-shrink-0">✕</button>
            </div>
        </div>

        <!-- Notification Badge for Pending Chats -->
        <div id="pendingBadge" class="fixed top-20 right-6 bg-gradient-to-r from-error to-error/80 text-on-error p-4 rounded-xl shadow-lg hidden transition-all duration-300 z-50 max-w-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl">schedule</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Novo Pendente</p>
                    <p class="text-xs opacity-90" id="pendingName">Aguardando sua ação</p>
                </div>
                <button onclick="document.getElementById('pendingBadge').classList.add('hidden')" class="text-on-error hover:opacity-75 flex-shrink-0">✕</button>
            </div>
        </div>

        @if($activeConversation?->contact)
        <!-- Chat Header -->
        <div class="p-5 bg-white/70 backdrop-blur-md border-b border-outline-variant/50 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-fixed to-primary-fixed/80 flex items-center justify-center font-bold text-sm text-on-primary-fixed shadow-md shrink-0">
                    {{ $activeConversation->contact->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-sm font-bold text-on-surface">{{ $activeConversation->contact->name }}</h2>
                        @php
                            $activeClaim = $activeConversation->getActiveClaim();
                            $isAdmin = Auth::user()->isAdmin();
                            $hasMyClaim = $activeClaim && $activeClaim->user_id === Auth::id();
                        @endphp
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-xs text-secondary font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">phone</span>
                            {{ $activeConversation->contact->phone }}
                        </span>
                        @if(!$activeClaim)
                            <span class="text-[11px] bg-error/20 text-error px-2.5 py-0.5 rounded-full flex items-center gap-1 font-semibold">
                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                Aguardando
                            </span>
                        @else
                            <span class="text-[11px] bg-secondary/20 text-secondary px-2.5 py-0.5 rounded-full flex items-center gap-1 font-semibold">
                                <span class="material-symbols-outlined text-[12px]">person</span>
                                {{ $activeClaim && $activeClaim->user ? $activeClaim->user->name : 'Outro agente' }}
                                @if($hasMyClaim)
                                <span class="ml-1 font-bold opacity-75">(Você)</span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2 ml-4 shrink-0 flex-wrap justify-end">
                @if(!$activeClaim)
                    @if($isAdmin)
                        <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-tertiary/90 text-on-tertiary px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-tertiary shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-sm">assignment</span>
                            <span class="hidden sm:inline">Para Mim</span>
                        </button>
                        <button onclick="openReassignModal({{ $activeConversation->id }})" class="bg-tertiary/90 text-on-tertiary px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-tertiary shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-sm">person_add</span>
                            <span class="hidden sm:inline">Transferir</span>
                        </button>
                    @else
                        <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-secondary/90 text-on-secondary px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-secondary shadow-md flex items-center gap-1.5 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-sm">done</span>
                            <span class="hidden sm:inline">Clamar</span>
                        </button>
                    @endif
                @elseif($hasMyClaim)
                    @if($activeConversation->status !== 'resolved' && $activeConversation->status !== 'closed')
                    <button onclick="releaseConversation({{ $activeConversation->id }})" class="bg-warning/90 text-on-warning px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-warning shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">lock_open</span>
                        <span class="hidden sm:inline">Liberar</span>
                    </button>
                    @endif
                @endif
                @if($isAdmin && $activeClaim && !$hasMyClaim)
                    @if($activeConversation->status !== 'resolved' && $activeConversation->status !== 'closed')
                    <button onclick="openReassignModal({{ $activeConversation->id }})" class="bg-tertiary/90 text-on-tertiary px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-tertiary shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">person_add</span>
                        <span class="hidden sm:inline">Reatribuir</span>
                    </button>
                    @endif
                @endif
                @if($activeConversation->status !== 'resolved' && $activeConversation->status !== 'closed')
                <button type="button" onclick="openResolutionModal({{ $activeConversation->id }})" class="bg-error/90 text-on-error px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-error shadow-md flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">done_all</span>
                    <span class="hidden sm:inline">Encerrar</span>
                </button>
                @else
                <button type="button" onclick="openReopenRequestModal({{ $activeConversation->id }})" class="bg-info/90 text-on-info px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-info shadow-md flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">lock_clock</span>
                    <span class="hidden sm:inline">Reabrir</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Previous Conversations History -->
        @if($previousConversations->count() > 0)
        <div class="border-b border-outline-variant bg-surface-container-low">
            <details class="group cursor-pointer">
                <summary class="p-3 flex items-center gap-2 text-sm font-semibold text-on-surface hover:bg-surface-container transition-colors list-none">
                    <span class="material-symbols-outlined text-lg group-open:hidden">expand_more</span>
                    <span class="material-symbols-outlined text-lg hidden group-open:inline">expand_less</span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">history</span>
                        Histórico de Atendimentos
                        <span class="bg-secondary-container text-on-secondary-container text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $previousConversations->count() }}</span>
                    </span>
                </summary>
                <div class="space-y-2 p-3 border-t border-outline-variant bg-white">
                    @foreach($previousConversations as $prev)
                    <button onclick="openHistoryModal({{ $prev->id }})" class="w-full text-left p-3 border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer active:scale-95">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <div>
                                <p class="text-xs font-bold text-on-surface">
                                    {{ $prev->created_at->format('d/m/Y \à\s H:i') }}
                                </p>
                                @php
                                    $lastClaim = $prev->claims()->latest('claimed_at')->first();
                                @endphp
                                @if($lastClaim)
                                <p class="text-[11px] text-on-surface-variant mt-0.5">
                                    👤 {{ $lastClaim->user->name ?? 'Agente desconhecido' }}
                                </p>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold text-white bg-green-600 px-2 py-1 rounded">✓ Resolvido</span>
                        </div>
                        @if($prev->lastMessage)
                        <p class="text-xs text-on-surface-variant mt-1 line-clamp-2">
                            {{ $prev->lastMessage->content ?? '(Sem mensagens de texto)' }}
                        </p>
                        @endif
                    </button>
                    @endforeach
                </div>
            </details>
        </div>
        @endif

        <!-- Messages -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
            <div class="flex justify-center sticky top-0">
                <span class="text-[10px] uppercase text-on-surface-variant bg-white/60 backdrop-blur-sm py-1.5 px-4 rounded-full tracking-wider border border-outline-variant/30 shadow-sm">
                    📅 {{ $activeConversation->created_at->format('d \d\e M \d\e Y') }}
                </span>
            </div>
            @foreach($activeConversation->messages as $msg)
                @if($msg->direction === 'inbound')
                <!-- Customer Message -->
                <div class="flex items-end gap-3 max-w-[80%]">
                    <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-[10px] text-on-primary-fixed shrink-0">
                        {{ $activeConversation->contact->initials }}
                    </div>
                    <div>
                        @if($msg->media_url)
                        <div class="mb-1 rounded-lg overflow-hidden">
                            @if(str_starts_with($msg->mime_type ?? '', 'image/'))
                            <a href="{{ Storage::url($msg->media_url) }}" target="_blank">
                                <img src="{{ Storage::url($msg->media_url) }}" alt="{{ $msg->media_filename ?? 'Imagem' }}" class="max-w-[280px] max-h-[240px] rounded-lg object-cover border border-outline-variant cursor-pointer hover:opacity-90 transition-opacity">
                            </a>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'audio/'))
                            <div class="bg-white border border-outline-variant rounded-lg p-3 min-w-[260px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-primary text-lg">mic</span>
                                    <p class="text-xs font-bold text-on-surface truncate flex-1">{{ $msg->media_filename ?? 'Audio' }}</p>
                                    <a href="{{ Storage::url($msg->media_url) }}" download class="material-symbols-outlined text-on-surface-variant text-base hover:text-primary">download</a>
                                </div>
                                <audio controls class="w-full h-8" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'audio/mpeg' }}">
                                </audio>
                            </div>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'video/'))
                            <div class="bg-white border border-outline-variant rounded-lg overflow-hidden max-w-[300px]">
                                <video controls class="w-full max-h-[200px]" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'video/mp4' }}">
                                </video>
                            </div>
                            @else
                            <div class="bg-white border border-outline-variant rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $msg->media_filename ?? 'Arquivo' }}</p>
                                    <p class="text-[10px] text-on-surface-variant">{{ $msg->mime_type ?? '' }}</p>
                                </div>
                                <a href="{{ Storage::url($msg->media_url) }}" download class="material-symbols-outlined text-on-surface-variant text-lg hover:text-primary">download</a>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($msg->content)
                        <div class="bg-white p-4 rounded-xl rounded-bl-none border border-outline-variant shadow-sm">
                            <p class="text-sm text-on-surface leading-relaxed whitespace-pre-wrap">{{ $msg->content }}</p>
                        </div>
                        @endif
                        <span class="text-[10px] text-on-surface-variant mt-1 block">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                </div>
                @else
                <!-- Agent Message -->
                <div class="flex flex-col items-end">
                    <div class="max-w-[80%]">
                        @if($msg->media_url)
                        <div class="mb-1 rounded-lg overflow-hidden">
                            @if(str_starts_with($msg->mime_type ?? '', 'image/'))
                            <a href="{{ Storage::url($msg->media_url) }}" target="_blank">
                                <img src="{{ Storage::url($msg->media_url) }}" alt="{{ $msg->media_filename ?? 'Imagem' }}" class="max-w-[280px] max-h-[240px] rounded-lg object-cover cursor-pointer hover:opacity-90 transition-opacity">
                            </a>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'audio/'))
                            <div class="bg-white border border-outline-variant rounded-lg p-3 min-w-[260px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-primary text-lg">mic</span>
                                    <p class="text-xs font-bold text-on-surface truncate flex-1">{{ $msg->media_filename ?? 'Audio' }}</p>
                                </div>
                                <audio controls class="w-full h-8" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'audio/mpeg' }}">
                                </audio>
                            </div>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'video/'))
                            <div class="bg-white border border-outline-variant rounded-lg overflow-hidden max-w-[300px]">
                                <video controls class="w-full max-h-[200px]" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'video/mp4' }}">
                                </video>
                            </div>
                            @else
                            <div class="bg-white border border-outline-variant rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $msg->media_filename ?? 'Arquivo' }}</p>
                                    <p class="text-[10px] text-on-surface-variant">{{ $msg->mime_type ?? '' }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($msg->content)
                        <div class="bg-primary-container text-on-primary p-4 rounded-xl rounded-br-none shadow-md">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->content }}</p>
                        </div>
                        @endif
                        <div class="flex justify-end items-center gap-1 mt-1">
                            <span class="text-[10px] text-on-surface-variant">{{ $msg->created_at->format('H:i') }}</span>
                            @if($msg->status === 'read')
                            <span class="material-symbols-outlined text-[14px] text-blue-500">done_all</span>
                            @elseif($msg->status === 'delivered')
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant">done_all</span>
                            @elseif($msg->status === 'failed')
                            <span class="material-symbols-outlined text-[14px] text-error">error</span>
                            @else
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant">check</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Macros Quick Bar -->
        @if($macros->count() > 0)
        <div class="px-4 py-3 bg-white/60 backdrop-blur-sm border-t border-outline-variant/50">
            <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-1">
                <span class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-wider whitespace-nowrap mr-2 flex items-center">⚡ Rápido:</span>
                @foreach($macros as $macro)
                <button onclick="applyMacro('{{ addslashes($macro->content) }}')" class="whitespace-nowrap bg-white/80 border border-outline-variant/50 px-3 py-1.5 rounded-full text-xs text-on-surface hover:bg-white hover:border-outline-variant/80 transition-all shadow-sm hover:shadow-md shrink-0">
                    {{ $macro->name }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Chat Input -->
        @if($hasMyClaim || $isAdmin)
        <div class="p-4 bg-white/70 backdrop-blur-sm border-t border-outline-variant/50 space-y-3">
            <!-- File Preview -->
            <div id="filePreview" class="hidden bg-white/80 backdrop-blur-sm border border-outline-variant/50 rounded-lg p-3 flex items-center gap-3 shadow-sm">
                <div id="filePreviewThumb" class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center overflow-hidden shrink-0">
                    <span id="filePreviewIcon" class="material-symbols-outlined text-on-surface-variant text-xl">description</span>
                    <img id="filePreviewImg" class="w-full h-full object-cover hidden">
                    <audio id="filePreviewAudio" class="hidden" preload="metadata"></audio>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="filePreviewName" class="text-xs font-bold text-on-surface truncate"></p>
                    <p id="filePreviewSize" class="text-[10px] text-on-surface-variant"></p>
                </div>
                <button type="button" onclick="clearAttachment()" class="material-symbols-outlined text-on-surface-variant hover:text-error transition-colors text-lg">close</button>
            </div>
            <form id="chatForm" method="POST" action="{{ route('conversations.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                <div class="relative">
                    <textarea id="messageInput" name="content" rows="2" class="w-full bg-white/80 backdrop-blur-sm border border-outline-variant/50 rounded-xl p-4 pr-48 focus:ring-2 focus:ring-secondary-container/50 focus:border-secondary transition-all resize-none text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm" placeholder="Escreva uma mensagem... (/ para macros)" @if(!$hasMyClaim || $activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') title="Esta conversa foi encerrada" @elseif($isAdmin) title="Clique em 'Para Mim' para reivindicar" @else title="Clamado por {{ $activeClaim && $activeClaim->user ? $activeClaim->user->name : 'outro agente' }}" @endif @endif></textarea>

                    <!-- Macros Menu -->
                    <div id="macrosMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white/95 backdrop-blur-sm border border-outline-variant/50 rounded-xl shadow-lg z-50 max-h-64 overflow-y-auto custom-scrollbar">
                        <div id="macrosMenuItems" class="space-y-1 p-2"></div>
                    </div>

                    <div class="absolute right-4 bottom-4 flex items-center gap-2 text-on-surface-variant" id="chatActions">
                        <button type="button" id="emojiBtn" class="hover:text-secondary transition-colors relative text-lg {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Emoji" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            😊
                        </button>
                        <label class="cursor-pointer hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Arquivo">
                            <span class="material-symbols-outlined">attach_file</span>
                            <input type="file" name="attachment" id="fileInput" class="hidden" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                        </label>
                        <button type="button" id="audioRecordBtn" class="hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Áudio" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined">mic</span>
                        </button>
                        <button type="submit" class="bg-secondary text-on-secondary w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:shadow-lg active:scale-95 transition-all {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </div>

                    <!-- Emoji Picker -->
                    <div id="emojiPicker" class="hidden absolute bottom-full right-4 mb-2 bg-white/95 backdrop-blur-md border border-outline-variant/50 rounded-xl shadow-lg z-50 w-96 h-96 flex flex-col">
                        <div class="flex gap-1 p-2 border-b border-outline-variant/50 overflow-x-auto custom-scrollbar flex-shrink-0" id="emojiCategories"></div>
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
                            <div class="grid grid-cols-7 gap-2" id="emojiGrid"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @else
        <!-- Chat Bloqueado - Precisa Clamar -->
        <div class="p-8 bg-gradient-to-r from-error/10 to-error/5 border-t border-error/20 flex flex-col items-center justify-center gap-4">
            <div class="text-center">
                <span class="material-symbols-outlined text-5xl text-error block mb-3">lock</span>
                <h3 class="text-base font-semibold text-on-surface mb-2">Atendimento Indisponível</h3>
                @if($activeClaim && $activeClaim->user)
                    <p class="text-sm text-on-surface-variant mb-4">Reivindicado por <strong>{{ $activeClaim->user->name }}</strong></p>
                @else
                    <p class="text-sm text-on-surface-variant mb-4">Reivindicado por outro agente</p>
                @endif
                <p class="text-xs text-on-surface-variant/80">Você precisa reivindicar este atendimento para responder</p>
            </div>
            <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-secondary text-on-secondary px-5 py-2.5 rounded-lg text-sm font-semibold hover:shadow-md shadow-sm flex items-center gap-2 transition-all active:scale-95">
                <span class="material-symbols-outlined text-base">done</span>
                Reivindicar Agora
            </button>
        </div>
        @endif
        @else
        <!-- No conversation selected -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <span class="material-symbols-outlined text-6xl text-outline-variant">chat</span>
                <p class="text-on-surface-variant mt-4">Selecione uma conversa para comecar</p>
            </div>
        </div>
        @endif
    </section>

    <!-- Resolution Modal -->
    <div id="resolutionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <style>
            .glass-modal { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); }
        </style>
        <div class="glass-modal rounded-xl shadow-2xl max-w-md w-full p-6 border border-white/30">
            <h3 class="text-lg font-bold text-on-surface mb-2">✔️ Encerrar Conversa</h3>
            <p class="text-sm text-on-surface-variant mb-6">Registre o motivo do encerramento para gerar relatórios precisos</p>

            <form id="resolutionForm" class="space-y-4">
                @csrf
                <input type="hidden" id="conversationId" name="conversation_id">

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Motivo do Encerramento</label>
                    <select name="resolution_reason" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                        <option value="">Selecione um motivo...</option>
                        <option value="problem_solved">✓ Problema Resolvido</option>
                        <option value="customer_satisfied">😊 Cliente Satisfeito</option>
                        <option value="follow_up_needed">→ Acompanhamento Necessário</option>
                        <option value="transferred">↗️ Transferido</option>
                        <option value="duplicate">📋 Conversa Duplicada</option>
                        <option value="spam">⚠️ Spam/Abuso</option>
                        <option value="no_response">⏱️ Sem Resposta do Cliente</option>
                        <option value="other">❓ Outro</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">O que foi feito?</label>
                    <textarea name="resolution_notes" rows="3" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Descreva as ações tomadas..." required></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Comentários Internos (Opcional)</label>
                    <textarea name="internal_comments" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Notas para a equipe..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeResolutionModal()" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-error text-on-error rounded-lg font-semibold hover:opacity-90 transition-all">
                        Encerrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reopen Request Modal -->
    <div id="reopenRequestModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="glass-modal rounded-xl shadow-2xl max-w-md w-full p-6 border border-white/30">
            <h3 class="text-lg font-bold text-on-surface mb-2">🔓 Pedir Reabertura</h3>
            <p class="text-sm text-on-surface-variant mb-6">Explique por que esta conversa precisa ser reaberта</p>

            <form id="reopenRequestForm" class="space-y-4">
                @csrf
                <input type="hidden" id="reopenConversationId" name="conversation_id">

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Motivo da Reabertura</label>
                    <textarea name="reason" rows="4" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Descreva por que a conversa precisa ser reaberта..." required minlength="10"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeReopenRequestModal()" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-info text-on-info rounded-lg font-semibold hover:opacity-90 transition-all">
                        Enviar Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Contact Details -->
    @if($activeConversation?->contact)
    <section class="w-[300px] border-l border-outline-variant/50 bg-gradient-to-b from-white to-slate-50/50 flex flex-col overflow-y-auto custom-scrollbar shrink-0">
        <div class="p-6 space-y-6">
            <!-- Contact Identity -->
            <div class="text-center p-4 bg-white/70 backdrop-blur-sm rounded-xl border border-outline-variant/30 shadow-sm">
                <div class="w-20 h-20 rounded-2xl mx-auto mb-4 bg-gradient-to-br from-primary-fixed to-primary-fixed/80 flex items-center justify-center font-bold text-2xl text-on-primary-fixed shadow-md">
                    {{ $activeConversation->contact->initials }}
                </div>
                <h2 class="text-base font-bold text-on-surface mb-1">{{ $activeConversation->contact->name }}</h2>
                <p class="text-xs text-on-surface-variant">{{ $activeConversation->contact->email ?? '(sem email)' }}</p>
                @if($activeConversation->contact->tags && count($activeConversation->contact->tags) > 0)
                <div class="flex justify-center gap-1 mt-3 flex-wrap">
                    @foreach($activeConversation->contact->tags ?? [] as $tag)
                    <span class="bg-secondary-container/40 text-on-secondary-container px-2.5 py-0.5 rounded-full text-xs font-semibold backdrop-blur-sm">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Contact Details -->
            <div class="space-y-2.5">
                <div class="flex items-center gap-3 p-3 bg-white/60 backdrop-blur-sm rounded-lg border border-outline-variant/20 hover:bg-white/80 transition-all">
                    <span class="material-symbols-outlined text-secondary text-lg">call</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-on-surface-variant font-medium">WhatsApp</p>
                        <p class="text-sm font-semibold text-on-surface truncate">{{ $activeConversation->contact->phone }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/60 backdrop-blur-sm rounded-lg border border-outline-variant/20 hover:bg-white/80 transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant text-lg">person</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-on-surface-variant font-medium">Agente Ativo</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $activeConversation->assignedUser?->name ?? '(nenhum)' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/60 backdrop-blur-sm rounded-lg border border-outline-variant/20 hover:bg-white/80 transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant text-lg">schedule</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-on-surface-variant font-medium">Última Mensagem</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $activeConversation->last_message_at?->diffForHumans(short: true) ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">📝 Notas</h3>
                <div class="bg-white/60 backdrop-blur-sm p-3 rounded-lg text-xs text-on-surface-variant border border-outline-variant/20 leading-relaxed">
                    {{ $activeConversation->contact->notes ?? '(sem notas)' }}
                </div>
            </div>

            <!-- Conversation Tags -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">🏷️ Atendimento</h3>
                    <button onclick="openTagsModal({{ $activeConversation->id }})" class="text-xs text-secondary hover:text-secondary/80 transition-colors font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                    </button>
                </div>
                <div id="conversationTags" class="flex flex-wrap gap-2">
                    @forelse($activeConversation->tags as $tag)
                    <div class="group relative">
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold text-white transition-all shadow-sm" style="background-color: {{ $tag->color }}; opacity: 0.9;">
                            {{ $tag->name }}
                        </span>
                        <button onclick="removeTag({{ $activeConversation->id }}, {{ $tag->id }})" class="absolute -top-2 -right-2 hidden group-hover:flex w-5 h-5 rounded-full bg-error text-on-error items-center justify-center text-[10px] font-bold transition-all hover:scale-110 shadow-md">
                            ✕
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-on-surface-variant italic">(nenhuma)</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Tags Modal -->
    <div id="tagsModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="glass-modal rounded-xl shadow-2xl max-w-md w-full p-6 max-h-96 flex flex-col border border-white/30">
            <h3 class="text-lg font-bold text-on-surface mb-4">🏷️ Tipo de Atendimento</h3>
            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-2 mb-4" id="tagsContainer">
                <!-- Tags will be loaded here -->
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
                <button type="button" onclick="closeTagsModal()" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/helpers/chat-inbox.js') }}"></script>
<script>
    const chatEl = document.getElementById('chatMessages');
    if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;

    const APP_URL = '{{ config("app.url") }}';

    // Resolution Modal
    function openResolutionModal(conversationId) {
        document.getElementById('conversationId').value = conversationId;
        document.getElementById('resolutionForm').reset();
        document.getElementById('resolutionModal').classList.remove('hidden');
    }

    function closeResolutionModal() {
        document.getElementById('resolutionModal').classList.add('hidden');
    }

    document.getElementById('resolutionForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('{{ route("conversations.resolve-with-reason") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData)),
            });

            const data = await response.json();

            if (data.success) {
                alert('Conversa encerrada com sucesso!');
                closeResolutionModal();
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Erro ao encerrar conversa'));
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    });

    // Close modal on outside click
    document.getElementById('resolutionModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeResolutionModal();
    });

    // ===== Reopen Request Modal =====
    function openReopenRequestModal(conversationId) {
        document.getElementById('reopenConversationId').value = conversationId;
        document.getElementById('reopenRequestForm').reset();
        document.getElementById('reopenRequestModal').classList.remove('hidden');
    }

    function closeReopenRequestModal() {
        document.getElementById('reopenRequestModal').classList.add('hidden');
    }

    document.getElementById('reopenRequestForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('{{ route("conversations.reopen.request") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData)),
            });

            const data = await response.json();

            if (data.success) {
                alert('Pedido de reabertura enviado! O administrador será notificado.');
                closeReopenRequestModal();
            } else {
                alert('Erro: ' + (data.message || 'Erro ao enviar pedido'));
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    });

    // Close modal on outside click
    document.getElementById('reopenRequestModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeReopenRequestModal();
    });

    function applyMacro(content) {
        const input = document.getElementById('messageInput');
        if (input) { input.value = content; input.focus(); }
    }

    // === File Preview ===
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (!this.files[0]) { clearAttachment(); return; }
            const file = this.files[0];
            const previewName = document.getElementById('filePreviewName');
            const previewSize = document.getElementById('filePreviewSize');
            const previewIcon = document.getElementById('filePreviewIcon');
            const previewImg = document.getElementById('filePreviewImg');

            previewName.textContent = file.name;
            previewSize.textContent = formatFileSize(file.size);
            previewIcon.classList.remove('hidden');
            previewImg.classList.add('hidden');

            if (file.type.startsWith('image/')) {
                previewIcon.textContent = 'image';
                const url = URL.createObjectURL(file);
                previewImg.src = url;
                previewImg.classList.remove('hidden');
                previewIcon.classList.add('hidden');
            } else if (file.type.startsWith('audio/')) {
                previewIcon.textContent = 'audio_file';
            } else if (file.type.startsWith('video/')) {
                previewIcon.textContent = 'videocam';
            } else {
                previewIcon.textContent = 'description';
            }

            filePreview.classList.remove('hidden');
        });
    }

    function clearAttachment() {
        if (fileInput) fileInput.value = '';
        if (filePreview) filePreview.classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // === Audio Recording ===
    const audioRecordBtn = document.getElementById('audioRecordBtn');
    let mediaRecorder = null;
    let audioChunks = [];
    let isRecording = false;

    if (audioRecordBtn) {
        audioRecordBtn.addEventListener('click', async function() {
            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                    mediaRecorder.onstop = () => {
                        const blob = new Blob(audioChunks, { type: 'audio/webm' });
                        const file = new File([blob], `audio_${Date.now()}.webm`, { type: 'audio/webm' });
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        fileInput.files = dt.files;
                        fileInput.dispatchEvent(new Event('change'));
                        stream.getTracks().forEach(t => t.stop());
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    audioRecordBtn.classList.add('text-error');
                    audioRecordBtn.querySelector('.material-symbols-outlined').textContent = 'stop';
                    audioRecordBtn.title = 'Parar gravacao';
                } catch(e) {
                    alert('Nao foi possivel acessar o microfone: ' + e.message);
                }
            } else {
                mediaRecorder.stop();
                isRecording = false;
                audioRecordBtn.classList.remove('text-error');
                audioRecordBtn.querySelector('.material-symbols-outlined').textContent = 'mic';
                audioRecordBtn.title = 'Gravar audio';
            }
        });
    }

    @if($activeConversation?->contact)
    const chatInbox = new ChatInboxHelper({
        chatEl: chatEl,
        contactName: @json($activeConversation->contact->name),
        pollUrl: @json(route('conversations.poll', $activeConversation)),
        initialMessageIds: @json($activeConversation->messages->pluck('id')),
        initialKeys: @json($activeConversation->messages->map(fn ($m) => \App\Helpers\ChatInboxHelper::dedupeKey($m))),
        lastMessageId: {{ $activeConversation->messages->last()->id ?? 0 }},
    });

    chatInbox.bindSendForm(document.getElementById('chatForm'), function () {
        const input = document.getElementById('messageInput');
        if (input) input.value = '';
        clearAttachment();
    });

    chatInbox.startPolling();
    @endif

    const chatForm = document.getElementById('chatForm');
    const msgInput = document.getElementById('messageInput');
    if (msgInput && chatForm) {
        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                chatForm.requestSubmit();
            }
        });
    }

    // ===== Notificações em tempo real =====
    // Polling will handle message notifications

    if (typeof window.Echo !== 'undefined' && @if($activeConversation) true @else false @endif) {
        const conversationId = {{ $activeConversation->id ?? 'null' }};

        function playNotificationSound() {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.5;
            audio.play().catch(err => console.log('Autoplay bloqueado ou arquivo não encontrado:', err));
        }

        function showDesktopNotification(sender, message) {
            if (Notification.permission === 'granted') {
                const notification = new Notification(sender, {
                    body: message.substring(0, 100),
                    icon: '/images/whatsapp-icon.png',
                    badge: '/images/badge.png',
                    tag: 'whatsapp-' + conversationId,
                });

                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };
            }
        }

        function showNotificationToast(sender, message) {
            const toast = document.getElementById('notificationToast');
            if (!toast) return;

            document.getElementById('toastSender').textContent = sender;
            document.getElementById('toastMessage').textContent = message.substring(0, 50) + (message.length > 50 ? '...' : '');

            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        // Polling will handle message notifications

        // Solicitar permissão de notificação desktop
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    }

    // ===== Funções de Claim/Release =====
    function apiJsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        };
    }

    async function parseJsonResponse(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (_) {
            throw new Error('Resposta inválida do servidor (HTTP ' + response.status + ')');
        }
    }

    async function claimConversation(conversationId) {
        try {
            const response = await fetch(`/conversations/${conversationId}/claim`, {
                method: 'POST',
                headers: apiJsonHeaders(),
            });
            const data = await parseJsonResponse(response);
            if (data.success) {
                window.Feedback?.success(data.message) || alert(data.message);
                location.reload();
            } else {
                window.Feedback?.error(data.message) || alert(data.message);
            }
        } catch (e) {
            window.Feedback?.error('Erro: ' + e.message) || alert('Erro: ' + e.message);
        }
    }

    async function releaseConversation(conversationId) {
        if (!confirm('Tem certeza que deseja liberar este atendimento?')) return;

        try {
            const response = await fetch(`/conversations/${conversationId}/claim`, {
                method: 'DELETE',
                headers: apiJsonHeaders(),
            });
            const data = await parseJsonResponse(response);
            if (data.success) {
                window.Feedback?.success(data.message) || alert(data.message);
                location.reload();
            } else {
                window.Feedback?.error(data.message) || alert(data.message);
            }
        } catch (e) {
            window.Feedback?.error('Erro: ' + e.message) || alert('Erro: ' + e.message);
        }
    }

    async function openReassignModal(conversationId) {
        try {
            // Fetch lista de agentes
            const usersResponse = await fetch('/api/agents', {
                headers: apiJsonHeaders(),
                credentials: 'same-origin',
            });

            if (!usersResponse.ok) {
                throw new Error(`HTTP ${usersResponse.status}: ${usersResponse.statusText}`);
            }

            const usersData = await parseJsonResponse(usersResponse);

            if (!usersData.success || !usersData.agents || usersData.agents.length === 0) {
                throw new Error('Nenhum agente disponível');
            }

            // Criar um modal simples com seletor
            const selectedUserId = await new Promise((resolve) => {
                const agents = usersData.agents;
                let html = '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">';

                agents.forEach(agent => {
                    html += `<div style="padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; hover: background-color: #f5f5f5;" onclick="document.reassignSelectedId='${agent.id}'; this.closest('#reassignModal').style.display='none';">
                        <strong>${agent.name}</strong> ${agent.email ? '(' + agent.email + ')' : ''}
                    </div>`;
                });
                html += '</div>';

                const modal = document.createElement('div');
                modal.id = 'reassignModal';
                modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;';

                modal.innerHTML = `
                    <div style="background:white;padding:24px;border-radius:12px;max-width:500px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                        <h2 style="margin-top:0;margin-bottom:16px;font-size:18px;font-weight:bold;">Transferir Para</h2>
                        ${html}
                        <div style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end;">
                            <button onclick="this.closest('#reassignModal').remove();document.reassignSelectedId=null;" style="padding:8px 16px;border:1px solid #ddd;border-radius:6px;background:white;cursor:pointer;">Cancelar</button>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                document.reassignSelectedId = null;

                // Aguardar clique
                const checkInterval = setInterval(() => {
                    if (!document.getElementById('reassignModal')) {
                        clearInterval(checkInterval);
                        resolve(document.reassignSelectedId);
                    }
                }, 100);
            });

            if (!selectedUserId) return;

            // Executar reatribuição
            const response = await fetch(`/conversations/${conversationId}/reassign`, {
                method: 'PATCH',
                headers: apiJsonHeaders(),
                body: JSON.stringify({
                    user_id: parseInt(selectedUserId, 10),
                    reason: 'Admin transferiu via interface',
                }),
            });
            const data = await parseJsonResponse(response);
            if (data.success) {
                window.Feedback?.success(data.message) || alert(data.message);
                location.reload();
            } else {
                window.Feedback?.error(data.message) || alert(data.message);
            }
        } catch (e) {
            window.Feedback?.error('Erro: ' + e.message) || alert('Erro: ' + e.message);
        }
    }

    // ===== Macros Menu com Slash Command =====
    const macrosData = @json($macros ?? []);
    const messageInput = document.getElementById('messageInput');
    const macrosMenu = document.getElementById('macrosMenu');
    const macrosMenuItems = document.getElementById('macrosMenuItems');

    // Only initialize macros menu if the elements exist in the DOM
    if (messageInput && macrosMenu) {
    let allMacros = [];
    let selectedMacroIndex = -1;

    // Handle both array and grouped structure
    if (Array.isArray(macrosData)) {
        allMacros = macrosData;
    } else {
        // Flatten macros from grouped structure
        Object.values(macrosData).forEach(categoryMacros => {
            if (Array.isArray(categoryMacros)) {
                allMacros.push(...categoryMacros);
            }
        });
    }

    function getMacrosQuery() {
        const text = messageInput.value;
        const slashIndex = text.lastIndexOf('/');

        if (slashIndex === -1) return null;

        const afterSlash = text.substring(slashIndex + 1);
        const beforeSlash = text.substring(0, slashIndex);

        // Se tem espaço antes do /, não é comando
        if (beforeSlash && !beforeSlash.match(/[\s\n]$/)) return null;

        return { query: afterSlash.toLowerCase(), slashPos: slashIndex };
    }

    function filterMacros(query) {
        if (query === '') {
            return allMacros;
        }

        return allMacros.filter(macro =>
            macro.name.toLowerCase().includes(query) ||
            macro.shortcut?.toLowerCase().includes(query) ||
            macro.content.toLowerCase().substring(0, 50).includes(query)
        );
    }

    function renderMacrosMenu(query) {
        const filtered = filterMacros(query);

        if (filtered.length === 0) {
            macrosMenuItems.innerHTML = '<div class="px-3 py-2 text-xs text-on-surface-variant text-center">Nenhuma macro encontrada</div>';
            selectedMacroIndex = -1;
            return;
        }

        selectedMacroIndex = -1;
        macrosMenuItems.innerHTML = filtered.map((macro, index) => `
            <button type="button"
                class="macro-menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container transition-colors text-sm"
                data-index="${index}"
                data-macro-id="${macro.id}"
                data-content="${macro.content.replace(/"/g, '&quot;')}">
                <div class="font-semibold text-on-surface text-sm">${macro.name}</div>
                <div class="text-xs text-on-surface-variant line-clamp-1">${macro.content.substring(0, 60)}...</div>
                ${macro.shortcut ? `<div class="text-[10px] text-secondary font-mono mt-0.5">/${macro.shortcut}</div>` : ''}
            </button>
        `).join('');

        // Add click handlers
        document.querySelectorAll('.macro-menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                selectMacro(parseInt(item.dataset.index));
            });
        });
    }

    function showMacrosMenu() {
        const query = getMacrosQuery();

        if (!query) {
            macrosMenu.classList.add('hidden');
            return;
        }

        renderMacrosMenu(query.query);
        macrosMenu.classList.remove('hidden');
    }

    function selectMacro(index) {
        const query = getMacrosQuery();
        if (!query) return;

        const filtered = filterMacros(query.query);
        if (index < 0 || index >= filtered.length) return;

        const macro = filtered[index];
        const text = messageInput.value;
        const beforeSlash = text.substring(0, query.slashPos);

        messageInput.value = beforeSlash + macro.content;
        messageInput.focus();

        macrosMenu.classList.add('hidden');

        // Auto-scroll to bottom
        messageInput.scrollTop = messageInput.scrollHeight;
    }

    function updateMenuSelection(direction) {
        const query = getMacrosQuery();
        if (!query) return;

        const filtered = filterMacros(query.query);

        if (direction === 'down') {
            selectedMacroIndex = (selectedMacroIndex + 1) % filtered.length;
        } else if (direction === 'up') {
            selectedMacroIndex = selectedMacroIndex === -1 ? filtered.length - 1 : selectedMacroIndex - 1;
        }

        updateMenuVisuals(filtered);
    }

    function updateMenuVisuals(filtered) {
        document.querySelectorAll('.macro-menu-item').forEach((item, idx) => {
            item.classList.toggle('bg-surface-container', idx === selectedMacroIndex);
        });
    }

    // Event listeners
    messageInput.addEventListener('input', showMacrosMenu);

    messageInput.addEventListener('keydown', (e) => {
        const query = getMacrosQuery();
        if (!query || macrosMenu.classList.contains('hidden')) return;

        const filtered = filterMacros(query.query);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            updateMenuSelection('down');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            updateMenuSelection('up');
        } else if (e.key === 'Enter' && selectedMacroIndex >= 0) {
            e.preventDefault();
            selectMacro(selectedMacroIndex);
        } else if (e.key === 'Escape') {
            macrosMenu.classList.add('hidden');
        }
    });

    // Close menu on outside click
    document.addEventListener('click', (e) => {
        if (!messageInput.contains(e.target) && !macrosMenu.contains(e.target)) {
            macrosMenu.classList.add('hidden');
        }
    });
    } // End of macros menu initialization

    // ===== Notificação de Novo Atendimento Pendente =====
    function showPendingNotification(contactName) {
        const badge = document.getElementById('pendingBadge');
        const toast = document.getElementById('notificationToast');

        if (!badge) return;

        // Se há conversa ativa e é sobre outra conversa, mostrar badge de pendente
        if (@if($activeConversation) true @else false @endif && contactName !== @json($activeConversation->contact->name ?? null)) {
            // Nova conversa pendente, não é a atual
            document.getElementById('pendingName').textContent = contactName + ' aguardando resposta';
            badge.classList.remove('hidden');

            // Auto-hide after 7 seconds
            setTimeout(() => badge.classList.add('hidden'), 7000);
        } else if (!toast || toast.classList.contains('hidden')) {
            // Mostrar toast normal
            document.getElementById('toastIcon').textContent = '🔔';
            document.getElementById('toastSender').textContent = contactName || 'Novo Contato';
            document.getElementById('toastMessage').textContent = 'Novo atendimento aguardando';

            if (toast) {
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 6000);
            }
        }
    }

    // Interceptar evento de nova conversa criada
    const originalShowNotificationToast = window.showNotificationToast;
    if (@if($activeConversation) false @else true @endif) {
        // Se não há conversa ativa, toda nova mensagem é um novo atendimento
        window.showNotificationToast = function(sender, message) {
            showPendingNotification(sender);
            if (originalShowNotificationToast) originalShowNotificationToast(sender, message);
        };
    }

    // ===== Emoji Picker =====
    const emojiBtn = document.getElementById('emojiBtn');
    const emojiPicker = document.getElementById('emojiPicker');
    const emojiGrid = document.getElementById('emojiGrid');
    const emojiCategories = document.getElementById('emojiCategories');

    const emojisByCategory = {
        'Reações': ['👍', '👎', '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '👏', '🙌', '👏', '🎉', '🎊', '😍', '🥰', '😘', '💋'],
        'Sentimentos': ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😌', '😔', '😑', '😐', '😶', '🤐', '🤨', '🤔', '🤫', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤮', '🤧', '🤬', '🤯', '😳', '��', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖'],
        'Gestos': ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🫰', '🤟', '🤘', '🤙', '👍', '👎', '👊', '👊', '👊', '✊', '👋', '👏', '🙌', '👐', '🤲', '🤝', '🤜', '🤛', '🦵', '🦶', '👂', '👃', '🧠', '🦷', '🦴'],
        'Objetos': ['⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💽', '💾', '💿', '📀', '📧', '📨', '📩', '📤', '📥', '📦', '🏷️', '📪', '📫', '📬', '📭', '📮', '📯', '📜', '📞', '☎️', '📟', '📠', '🔋', '🔌', '💡', '🔦', '🕯️', '📔', '📕', '📖', '📗', '📘', '📙', '📚', '📓', '📒', '📑', '🧷', '🧹', '🧺', '🧻', '🔒', '🔓', '🔏', '🔐', '🔑', '🗝️', '🚪', '🪑', '🚽', '🚿', '🛁', '🛒', '🚬', '⚰️', '⚱️', '🏺', '🔮', '📿', '💈', '⚗️', '⚖️', '🔧', '🔨', '⚒️', '🛠️', '⛏️', '🔩', '⌛', '⏳', '⏱️', '⏲️', '🧿', '🎞️', '🎬', '📺', '📷', '📸', '📹', '🎥', '📽️', '🎞️', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️', '🎛️', '🧭', '⏱️', '⏲️', '⏰'],
        'Natureza': ['🌍', '🌎', '🌏', '🌐', '🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘', '🌙', '🌚', '🌝', '🌛', '🌜', '⭐', '🌟', '✨', '⚡', '☄️', '💥', '🔥', '🌪️', '🌈', '☀️', '🌤️', '⛅', '🌥️', '☁️', '🌦️', '🌧️', '⛈️', '🌩️', '🌨️', '❄️', '☃️', '⛄', '🌬️', '💨', '💧', '💦', '☔', '🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥑', '🍆', '🍅', '🌶️', '🌽', '🥒', '🥬', '🥦', '🧄', '🧅', '🍄', '🥜', '🌰', '🍞', '🥐', '🥖', '🥨', '🥯', '🥞', '🧇', '🥚', '🍳', '🧈', '🥞', '🥓', '🍗', '🍖', '🌭', '🍔', '🍟', '🍕', '🥪', '🥙', '🧆', '🌮', '🌯', '🥗', '🥘', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦', '🍰', '🎂', '🧁', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🍯', '🥛', '🍼', '☕', '🍵', '🍶', '🍾', '🍷', '🍸', '🍹', '🍺', '🍻', '🥂', '🥃', '🥤', '🧋', '🧃', '🧉'],
        'Atividades': ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎳', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🥅', '⛳', '⛸️', '🎣', '🎽', '🎿', '⛷️', '🏂', '🪂', '🛼', '🛹', '🛷', '🥌', '🎯', '🪀', '🪁', '🎪', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🎷', '🎺', '🎸', '🪕', '🎻', '🎲', '♟️', '🎮', '🎰', '🧩'],
        'Viagem': ['✈️', '🛫', '🛬', '🛩️', '💺', '🛰️', '🚁', '🛶', '⛵', '🚤', '🛳️', '⛴️', '🛥️', '🚢', '🚧', '⚓', '⛽', '🚨', '🚥', '🚦', '🛑', '🚒', '🚓', '🚑', '🚐', '🛻', '🚚', '🚕', '🚙', '🚗', '🚌', '🚎', '🏎️', '🏍️', '🛵', '🦯', '🦽', '🦼', '🛺', '🚲', '🛴', '🌍', '🌎', '🌏', '🗺️', '🗿', '🗽', '🗼', '🏔️', '⛰️', '🌋', '⛰️', '🏕️', '⛺', '⛲', '⛺', '🏠', '🏡', '🏘️', '🏚️', '🏗️', '🏭', '🏢', '🏬', '🏣', '🏤', '🏥', '🏦', '🏧', '🏨', '🏪', '🏫', '🏩', '💒', '🏛️', '⛪', '🕌', '🕍', '🛕', '🕋', '⛩️', '🛤️', '🛣️', '🗾', '🎑', '🏞️', '🌅', '🌄', '🌠', '🎇', '🎆', '🌇', '🌆', '🏙️', '🌃', '🌌', '🌉', '🌁'],
        'Símbolos': ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❤️‍🔥', '❤️‍🩹', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🫰', '🤟', '🤘', '🤙', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🤜', '🤛', '✊', '👊', '✔️', '❌', '❎', '✅', '❌', '❓', '❔', '❕', '❗', '⁉️', '🔰', '🔱', '⚜️', '🔯', '💠', '🔷', '🔶', '🔹', '🔸', '🔺', '🔻', '💎', '💠', '🔘', '🔳', '🔲'],
        'Bandeiras': ['🇧🇷', '🇺🇸', '🇪🇸', '🇮🇹', '🇫🇷', '🇩🇪', '🇯🇵', '🇨🇳', '🇷🇺', '🇰🇷', '🇮🇳', '🇲🇽', '🇨🇦', '🇦🇺', '🇿🇦', '🇬🇧'],
    };

    function initEmojiPicker() {
        // Create category buttons
        const categoryBtns = Object.keys(emojisByCategory).map((category, index) =>
            `<button type="button" data-category="${category}"
             class="emoji-category-btn px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors ${index === 0 ? 'bg-primary text-on-primary' : 'hover:bg-surface-container text-on-surface-variant'}">${category}</button>`
        ).join('');

        emojiCategories.innerHTML = categoryBtns;

        // Display first category
        showEmojiCategory(Object.keys(emojisByCategory)[0]);

        // Add category click handlers
        document.querySelectorAll('.emoji-category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.emoji-category-btn').forEach(b => {
                    b.classList.remove('bg-primary', 'text-on-primary');
                    b.classList.add('hover:bg-surface-container', 'text-on-surface-variant');
                });
                btn.classList.add('bg-primary', 'text-on-primary');
                btn.classList.remove('hover:bg-surface-container', 'text-on-surface-variant');
                showEmojiCategory(btn.dataset.category);
            });
        });
    }

    function showEmojiCategory(category) {
        const emojis = emojisByCategory[category] || [];
        emojiGrid.innerHTML = emojis.map(emoji =>
            `<button type="button" onclick="insertEmoji('${emoji}')" class="text-2xl p-2 rounded-lg hover:bg-surface-container transition-colors">${emoji}</button>`
        ).join('');
    }

    function insertEmoji(emoji) {
        if (messageInput) {
            messageInput.value += emoji;
            messageInput.focus();
            emojiPicker.classList.add('hidden');
        }
    }

    if (emojiBtn) {
        initEmojiPicker();
        emojiBtn.addEventListener('click', () => {
            emojiPicker.classList.toggle('hidden');
        });

        // Close emoji picker when clicking outside
        document.addEventListener('click', (e) => {
            if (!emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
                emojiPicker.classList.add('hidden');
            }
        });
    }

    // ===== History Modal =====
    async function openHistoryModal(conversationId) {
        try {
            const response = await fetch(`/conversations/${conversationId}/history-view`, {
                headers: apiJsonHeaders(),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await parseJsonResponse(response);

            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar histórico');
            }

            const conv = data.conversation;
            const events = data.events || [];
            const messages = data.messages || [];

            // Create modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
            modal.id = 'historyModal';

            // Render events timeline
            let eventsHtml = '';
            events.forEach((event, idx) => {
                const icon = event.icon || 'info';
                const colorClass = `text-${event.color}`;
                eventsHtml += `
                    <div class="flex gap-4 mb-6">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-${event.color}/20 flex items-center justify-center mb-2">
                                <span class="material-symbols-outlined text-sm text-${event.color}">${icon}</span>
                            </div>
                            ${idx < events.length - 1 ? '<div class="w-1 h-12 bg-surface-container"></div>' : ''}
                        </div>
                        <div class="pt-1">
                            <h4 class="font-semibold text-on-surface">${event.title}</h4>
                            <p class="text-xs text-on-surface-variant mt-0.5">${event.description}</p>
                            <p class="text-[10px] text-outline mt-1">${new Date(event.timestamp).toLocaleString('pt-BR')}</p>
                        </div>
                    </div>
                `;
            });

            let messagesHtml = '';
            messages.forEach(msg => {
                if (msg.direction === 'inbound') {
                    messagesHtml += `
                        <div class="flex items-end gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-[10px] text-on-primary-fixed shrink-0">
                                ${conv.contact_initials}
                            </div>
                            <div>
                                <div class="bg-white p-3 rounded-lg rounded-bl-none border border-outline-variant max-w-xs">
                                    <p class="text-sm text-on-surface">${msg.content}</p>
                                </div>
                                <span class="text-[10px] text-on-surface-variant mt-0.5 block ml-8">${msg.created_at}</span>
                            </div>
                        </div>
                    `;
                } else {
                    messagesHtml += `
                        <div class="flex flex-col items-end mb-3">
                            <div class="bg-primary-container text-on-primary p-3 rounded-lg rounded-br-none max-w-xs">
                                <p class="text-sm">${msg.content}</p>
                            </div>
                            <span class="text-[10px] text-on-surface-variant mt-0.5 mr-8">${msg.created_at}</span>
                        </div>
                    `;
                }
            });

            modal.innerHTML = `
                <style>
                    .glass-modal { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); }
                </style>
                <div class="glass-modal rounded-xl shadow-2xl flex flex-col w-full max-w-4xl max-h-[85vh] border border-white/30">
                    <div class="p-6 border-b border-surface-container-highest flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-on-surface">📋 Histórico: ${conv.contact_name}</h2>
                            <div class="flex gap-6 mt-3 text-sm text-on-surface-variant">
                                <span>📞 ${conv.contact_phone}</span>
                                <span>⏱️ Duração: ${conv.duration}</span>
                                <span>💬 ${conv.message_count} mensagens</span>
                                <span>👤 Atendido por: ${conv.claimed_by}</span>
                            </div>
                        </div>
                        <button onclick="document.getElementById('historyModal').remove()" class="text-on-surface-variant hover:text-on-surface text-2xl">✕</button>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar flex gap-4">
                        <div class="w-1/3 p-6 border-r border-surface-container-highest bg-surface-bright">
                            <h3 class="font-bold text-on-surface mb-4">Timeline de Eventos</h3>
                            <div class="space-y-2">
                                ${eventsHtml || '<p class="text-sm text-on-surface-variant">Nenhum evento registrado</p>'}
                            </div>
                        </div>
                        <div class="w-2/3 p-6 space-y-2">
                            <h3 class="font-bold text-on-surface mb-4">Mensagens (${messages.length})</h3>
                            ${messagesHtml || '<div class="text-center text-on-surface-variant text-sm py-8">Nenhuma mensagem neste atendimento</div>'}
                        </div>
                    </div>
                    <div class="p-6 border-t border-surface-container-highest flex justify-end">
                        <button onclick="document.getElementById('historyModal').remove()" class="px-6 py-2 bg-primary text-on-primary rounded-lg font-semibold hover:opacity-90 transition-all">
                            Fechar
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });

        } catch (e) {
            alert('Erro ao carregar histórico: ' + e.message);
        }
    }

    // ===== Tags Management =====
    async function openTagsModal(conversationId) {
        const modal = document.getElementById('tagsModal');
        const container = document.getElementById('tagsContainer');

        try {
            const response = await fetch('{{ route("tags.index") }}');
            const data = await response.json();

            if (!data.success) throw new Error('Failed to load tags');

            // Get current conversation tags
            const conversationResponse = await fetch(`/conversations/${conversationId}`);

            let currentTagIds = [];
            @if($activeConversation)
            currentTagIds = @json($activeConversation->tags->pluck('id')->toArray());
            @endif

            // Group tags by category
            const grouped = {};
            data.tags.forEach(tag => {
                if (!grouped[tag.category]) grouped[tag.category] = [];
                grouped[tag.category].push(tag);
            });

            let html = '';
            for (const [category, tags] of Object.entries(grouped)) {
                html += `<div class="mb-4"><h4 class="text-xs font-bold text-on-surface-variant uppercase mb-2">${getCategoryLabel(category)}</h4>`;
                tags.forEach(tag => {
                    const isSelected = currentTagIds.includes(tag.id);
                    html += `
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-surface-container-low cursor-pointer transition-colors">
                            <input type="checkbox" value="${tag.id}" ${isSelected ? 'checked' : ''} onchange="updateConversationTags(${conversationId})">
                            <span class="w-3 h-3 rounded-full" style="background-color: ${tag.color}"></span>
                            <span class="flex-1 text-sm text-on-surface">${tag.name}</span>
                        </label>
                    `;
                });
                html += '</div>';
            }

            container.innerHTML = html;
            modal.classList.remove('hidden');
        } catch (error) {
            alert('Erro ao carregar tags: ' + error.message);
        }
    }

    function closeTagsModal() {
        document.getElementById('tagsModal').classList.add('hidden');
    }

    async function updateConversationTags(conversationId) {
        const checked = Array.from(document.querySelectorAll('#tagsContainer input[type="checkbox"]:checked')).map(el => parseInt(el.value));

        try {
            const response = await fetch(`/conversations/${conversationId}/tags`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ tag_ids: checked }),
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao atualizar tags: ' + data.message);
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }

    async function removeTag(conversationId, tagId) {
        if (!confirm('Remover esta tag?')) return;

        try {
            const response = await fetch(`/conversations/${conversationId}/tags/${tagId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao remover tag: ' + data.message);
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }

    function getCategoryLabel(category) {
        const labels = {
            'priority': 'Prioridade',
            'status': 'Status',
            'outcome': 'Resultado',
            'custom': 'Tipo de Atendimento',
        };
        return labels[category] || category;
    }

    // Close tags modal on outside click
    document.getElementById('tagsModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeTagsModal();
    });

    // Initialize SSE connections
    document.addEventListener('DOMContentLoaded', function() {
        const conversationId = new URLSearchParams(window.location.search).get('conversation');

        if (conversationId) {
            console.log('[Init] Starting SSE connection for conversation', conversationId);
            window.SSEManager.connectToConversation(conversationId);
            window.SSEManager.connectToMessages();
        }

        // Also connect to global conversations channel
        window.SSEManager.connectToConversations();

        // Listen to custom events
        window.addEventListener('message-status-changed', (e) => {
            console.log('[Event] Message status changed:', e.detail);
        });

        window.addEventListener('conversation-status-changed', (e) => {
            console.log('[Event] Conversation status changed:', e.detail);
        });
    });

    // Cleanup on page leave
    window.addEventListener('beforeunload', function() {
        window.SSEManager.disconnectAll();
    });
</script>
@endpush
