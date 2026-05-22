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
    <section class="w-[360px] border-r border-outline-variant bg-white flex flex-col overflow-hidden shrink-0">
        <div class="p-4 border-b border-outline-variant">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-on-surface">Chats Ativos</h2>
                @php
                    $pendingCount = $conversations->filter(fn($c) => !$c->getActiveClaim())->count();
                    $totalCount = $conversations->count();
                @endphp
                @if($pendingCount > 0)
                <span class="bg-error text-on-error text-xs font-bold px-2.5 py-1 rounded-full animate-pulse">
                    {{ $pendingCount }} pendente{{ $pendingCount !== 1 ? 's' : '' }}
                </span>
                @endif
            </div>
            <div class="flex gap-1 flex-wrap">
                <a href="{{ route('conversations.index') }}" class="px-2 py-1 text-xs rounded {{ !request('assigned') && !request('status') ? 'bg-primary text-on-primary font-semibold' : 'text-on-surface-variant hover:bg-surface-container' }}">
                    Todos
                    <span class="text-[10px] ml-1 opacity-75">({{ $totalCount }})</span>
                </a>
                <a href="{{ route('conversations.index', ['status' => 'pending']) }}" class="px-2 py-1 text-xs rounded flex items-center gap-1 {{ request('status') === 'pending' ? 'bg-error text-on-error font-semibold' : 'text-on-surface-variant hover:bg-surface-container' }}">
                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                    Pendentes
                    @if($pendingCount > 0)
                    <span class="text-[10px] ml-1 font-bold">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('conversations.index', ['assigned' => 'mine']) }}" class="px-2 py-1 text-xs rounded {{ request('assigned') === 'mine' ? 'bg-primary text-on-primary font-semibold' : 'text-on-surface-variant hover:bg-surface-container' }}">Meus</a>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @forelse($conversations as $conv)
            @php
                $convPending = !$conv->getActiveClaim();
                $shouldShow = !request('status') || (request('status') === 'pending' && $convPending);
            @endphp
            @if($shouldShow && $conv->contact)
            <a href="{{ route('conversations.index', ['conversation' => $conv->id] + request()->all()) }}" class="block p-4 flex gap-3 cursor-pointer transition-colors border-l-4 {{ ($activeConversation?->id === $conv->id) ? 'bg-surface-container-low border-secondary' : ($convPending ? 'bg-red-50 border-error hover:bg-red-100' : 'border-transparent hover:bg-surface-container-low') }}" data-claim-info="{{ $convPending ? '⏱️ Pendente' : '🔒 ' . ($conv->getActiveClaim()?->user->name ?? 'Sem atribuição') }}">
                <div class="relative shrink-0">
                    <div class="w-12 h-12 rounded-full {{ $convPending ? 'bg-error' : 'bg-primary-fixed' }} flex items-center justify-center font-bold text-sm {{ $convPending ? 'text-on-error' : 'text-on-primary-fixed' }}">
                        {{ $conv->contact->initials }}
                    </div>
                    @if($convPending)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-error text-on-error rounded-full flex items-center justify-center text-[10px] font-bold border-2 border-white">!</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold {{ $convPending ? 'text-error' : 'text-on-surface' }} truncate">{{ $conv->contact->name }}</h3>
                            @if($convPending)
                            <span class="text-[10px] text-error font-semibold">⏱️ Pendente</span>
                            @else
                            <span class="text-[10px] text-yellow-700 font-semibold">🔒 {{ $conv->getActiveClaim()?->user->name ?? 'Sem atribuição' }}</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-on-surface-variant shrink-0">{{ $conv->last_message_at?->diffForHumans(short: true) }}</span>
                    </div>
                    <p class="text-sm text-on-surface-variant truncate mt-0.5">{{ $conv->lastMessage?->content ?? 'Sem mensagens' }}</p>
                </div>
            </a>
            @endif
            @empty
            <div class="p-8 text-center text-on-surface-variant text-sm">
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
    <section class="flex-1 flex flex-col bg-slate-50 relative overflow-hidden">
        <!-- Toast de Notificação -->
        <div id="notificationToast" class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-lg shadow-lg hidden transition-all duration-300 z-50 max-w-xs animate-slideInUp">
            <div class="flex items-center gap-3">
                <span class="text-lg" id="toastIcon">📩</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold" id="toastSender">Novo contato</p>
                    <p class="text-xs opacity-90" id="toastMessage">Você tem uma mensagem</p>
                </div>
                <button onclick="document.getElementById('notificationToast').classList.add('hidden')" class="text-white hover:opacity-75 flex-shrink-0">✕</button>
            </div>
        </div>

        <!-- Notification Badge for Pending Chats -->
        <div id="pendingBadge" class="fixed top-20 right-6 bg-error text-on-error p-4 rounded-xl shadow-lg hidden transition-all duration-300 z-50 max-w-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl">schedule</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Novo Atendimento Pendente</p>
                    <p class="text-xs opacity-90" id="pendingName">Aguardando sua ação</p>
                </div>
                <button onclick="document.getElementById('pendingBadge').classList.add('hidden')" class="text-on-error hover:opacity-75 flex-shrink-0">✕</button>
            </div>
        </div>

        @if($activeConversation?->contact)
        <!-- Chat Header -->
        <div class="p-4 bg-white border-b border-outline-variant flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-sm text-on-primary-fixed">
                    {{ $activeConversation->contact->initials }}
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-bold text-on-surface">{{ $activeConversation->contact->name }}</h2>
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-xs text-secondary font-semibold">{{ $activeConversation->contact->phone }}</p>
                        @php
                            $activeClaim = $activeConversation->getActiveClaim();
                            $isAdmin = Auth::user()->isAdmin();
                            $hasMyClaim = $activeClaim && $activeClaim->user_id === Auth::id();
                        @endphp
                        @if(!$activeClaim)
                            <span class="text-[11px] bg-red-100 text-red-800 px-2 py-0.5 rounded flex items-center gap-1">
                                <span>⏱️</span> Aguardando atendimento
                            </span>
                        @else
                            <span class="text-[11px] bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded flex items-center gap-1">
                                <span>🔒</span> Clamado por: {{ $activeClaim->user->name }}
                                @if($hasMyClaim)
                                <span class="ml-1 text-green-600 font-bold">(Você)</span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                @if(!$activeClaim)
                    @if($isAdmin)
                        <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-tertiary text-on-tertiary px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-base">assignment</span> Transferir para Mim
                        </button>
                        <button onclick="openReassignModal({{ $activeConversation->id }})" class="bg-tertiary text-on-tertiary px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-base">person_add</span> Transferir Para
                        </button>
                    @else
                        <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-secondary text-on-secondary px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-base">done</span> Clamar
                        </button>
                    @endif
                @elseif($hasMyClaim)
                    <button onclick="releaseConversation({{ $activeConversation->id }})" class="bg-warning text-on-warning px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">lock_open</span> Liberar
                    </button>
                @endif
                @if($isAdmin && $activeClaim && !$hasMyClaim)
                    <button onclick="openReassignModal({{ $activeConversation->id }})" class="bg-tertiary text-on-tertiary px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">person_add</span> Reatribuir
                    </button>
                @endif
                <form method="POST" action="{{ route('conversations.resolve', $activeConversation) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="bg-error text-on-error px-4 py-1.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-base">done_all</span> Encerrar
                    </button>
                </form>
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
            <div class="flex justify-center">
                <span class="text-[11px] uppercase text-on-surface-variant bg-surface-container py-1 px-3 rounded-full tracking-wider">
                    {{ $activeConversation->created_at->format('d/m/Y') }}
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
        <div class="px-4 py-2 bg-white border-t border-outline-variant">
            <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-1">
                @foreach($macros as $macro)
                <button onclick="applyMacro('{{ addslashes($macro->content) }}')" class="whitespace-nowrap bg-surface-container-low border border-outline-variant px-3 py-1.5 rounded-full text-xs text-on-surface hover:bg-surface-container transition-colors shrink-0">
                    {{ $macro->name }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Chat Input -->
        @if($hasMyClaim || $isAdmin)
        <div class="p-4 bg-white border-t border-outline-variant">
            <!-- File Preview -->
            <div id="filePreview" class="hidden mb-2 bg-surface-container-low border border-outline-variant rounded-lg p-3 flex items-center gap-3">
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
                    <textarea id="messageInput" name="content" rows="2" class="w-full bg-surface-container-low border border-outline-variant rounded-xl p-4 pr-48 focus:ring-1 focus:ring-secondary-container focus:border-primary transition-all resize-none text-sm disabled:opacity-50 disabled:cursor-not-allowed" placeholder="Escreva uma mensagem... (digite / para macros)" @if(!$hasMyClaim) disabled @if($isAdmin) title="Clique em 'Transferir para mim' para reivindicar esta conversa" @else title="Este atendimento foi clamado por {{ $activeClaim->user->name }}" @endif @endif></textarea>

                    <!-- Macros Menu -->
                    <div id="macrosMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white border border-outline-variant rounded-xl shadow-lg z-50 max-h-64 overflow-y-auto custom-scrollbar">
                        <div id="macrosMenuItems" class="space-y-1 p-2"></div>
                    </div>

                    <div class="absolute right-4 bottom-4 flex items-center gap-2 text-on-surface-variant">
                        <label class="cursor-pointer hover:text-primary transition-colors" title="Enviar arquivo">
                            <span class="material-symbols-outlined">attach_file</span>
                            <input type="file" name="attachment" id="fileInput" class="hidden" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt">
                        </label>
                        <button type="button" id="audioRecordBtn" class="hover:text-primary transition-colors" title="Gravar audio">
                            <span class="material-symbols-outlined">mic</span>
                        </button>
                        <button type="submit" class="bg-secondary text-on-secondary w-10 h-10 rounded-full flex items-center justify-center shadow-md active:scale-95 transition-transform">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @else
        <!-- Chat Bloqueado - Precisa Clamar -->
        <div class="p-6 bg-red-50 border-t border-red-200 flex flex-col items-center justify-center gap-4">
            <div class="text-center">
                <span class="material-symbols-outlined text-4xl text-red-500 block mb-2">lock</span>
                <h3 class="text-sm font-semibold text-red-900 mb-1">Atendimento Indisponível</h3>
                <p class="text-xs text-red-700 mb-4">Este atendimento foi reivindicado por <strong>{{ $activeClaim->user->name }}</strong></p>
                <p class="text-xs text-red-600">Você precisa reivindicar este atendimento para poder responder.</p>
            </div>
            <button onclick="claimConversation({{ $activeConversation->id }})" class="bg-secondary text-on-secondary px-4 py-2.5 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1.5 transition-all active:scale-95">
                <span class="material-symbols-outlined text-base">done</span>
                Reivindicar Atendimento
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

    <!-- Right: Contact Details -->
    @if($activeConversation?->contact)
    <section class="w-[300px] border-l border-outline-variant bg-white flex flex-col overflow-y-auto custom-scrollbar shrink-0">
        <div class="p-6 space-y-6">
            <!-- Contact Identity -->
            <div class="text-center">
                <div class="w-20 h-20 rounded-2xl mx-auto mb-3 bg-primary-fixed flex items-center justify-center font-bold text-2xl text-on-primary-fixed">
                    {{ $activeConversation->contact->initials }}
                </div>
                <h2 class="text-lg font-semibold text-on-surface">{{ $activeConversation->contact->name }}</h2>
                <p class="text-sm text-on-surface-variant">{{ $activeConversation->contact->email ?? 'Sem email' }}</p>
                <div class="flex justify-center gap-1 mt-2 flex-wrap">
                    @foreach($activeConversation->contact->tags ?? [] as $tag)
                    <span class="bg-secondary-container/30 text-on-secondary-container px-2 py-0.5 rounded-full text-xs font-semibold">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Contact Details -->
            <div class="space-y-3">
                <div class="flex items-center gap-3 p-2 bg-surface-container-low rounded-lg">
                    <span class="material-symbols-outlined text-on-surface-variant text-lg">call</span>
                    <div>
                        <p class="text-[11px] text-on-surface-variant">WhatsApp</p>
                        <p class="text-sm font-semibold">{{ $activeConversation->contact->phone }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-2 bg-surface-container-low rounded-lg">
                    <span class="material-symbols-outlined text-on-surface-variant text-lg">person</span>
                    <div>
                        <p class="text-[11px] text-on-surface-variant">Agente</p>
                        <p class="text-sm font-semibold">{{ $activeConversation->assignedUser?->name ?? 'Nenhum' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-2 bg-surface-container-low rounded-lg">
                    <span class="material-symbols-outlined text-on-surface-variant text-lg">schedule</span>
                    <div>
                        <p class="text-[11px] text-on-surface-variant">Ultima mensagem</p>
                        <p class="text-sm font-semibold">{{ $activeConversation->last_message_at?->diffForHumans() ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <h3 class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Notas</h3>
                <div class="bg-surface-container p-3 rounded-lg text-sm text-on-surface-variant">
                    {{ $activeConversation->contact->notes ?? 'Sem notas' }}
                </div>
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/helpers/chat-inbox.js') }}"></script>
<script>
    const chatEl = document.getElementById('chatMessages');
    if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;

    const APP_URL = '{{ config("app.url") }}';

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

    // Only initialize macros menu if the input exists (i.e., user has claimed the conversation)
    if (!messageInput || !macrosMenu) {
        console.log('Macros menu skipped: chat input not available');
    } else {
    let allMacros = [];
    let selectedMacroIndex = -1;

    // Flatten macros from grouped structure
    Object.values(macrosData).forEach(categoryMacros => {
        if (Array.isArray(categoryMacros)) {
            allMacros.push(...categoryMacros);
        }
    });

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
            const messages = data.messages || [];

            // Create modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
            modal.id = 'historyModal';

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
                <div class="bg-white rounded-xl shadow-xl flex flex-col w-full max-w-2xl max-h-[80vh]">
                    <div class="p-4 border-b border-outline-variant flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-bold text-on-surface">Histórico: ${conv.contact_name}</h2>
                            <p class="text-xs text-on-surface-variant mt-1">
                                ${conv.created_at} até ${conv.closed_at} • Atendido por: ${conv.claimed_by} • ${conv.message_count} mensagens
                            </p>
                        </div>
                        <button onclick="document.getElementById('historyModal').remove()" class="text-on-surface-variant hover:text-on-surface text-2xl">✕</button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar">
                        ${messagesHtml || '<div class="text-center text-on-surface-variant text-sm py-8">Nenhuma mensagem neste atendimento</div>'}
                    </div>
                    <div class="p-4 border-t border-outline-variant flex justify-end gap-2">
                        <button onclick="document.getElementById('historyModal').remove()" class="px-4 py-2 border border-outline-variant rounded-lg text-on-surface hover:bg-surface-container transition-colors">
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
</script>
@endpush
