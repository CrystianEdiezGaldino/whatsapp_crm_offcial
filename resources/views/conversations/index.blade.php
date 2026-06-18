@extends('layouts.app', ['fullHeight' => true])

@section('title', 'Atendimentos')

@section('content')
<div class="flex h-full w-full min-h-0">
    <!-- Coluna: lista de chats -->
    <section class="chat-list-column">
        <div class="chat-list-header">
            <div class="chat-list-header__row">
                <h2 class="chat-list-header__title">Atendimentos</h2>
                <span class="chat-status-badge">WhatsApp conectado</span>
            </div>
            <div class="chat-search-bar">
                <span class="material-symbols-outlined text-gray-400 text-[16px]">search</span>
                <input type="text" id="chatSearchInput" class="chat-search-bar__field" placeholder="Buscar nome ou telefone...">
            </div>
        </div>

        @php
            $queueTabs = [];
            if (auth()->user()->isAdmin()) {
                $queueTabs[] = [
                    'label' => 'Todos',
                    'count' => $totalCount,
                    'countKey' => 'total',
                    'href' => route('conversations.index'),
                    'active' => !request('assigned') && !request('status'),
                ];
            }
            $queueTabs[] = [
                'label' => 'Fila',
                'count' => $pendingCount,
                'countKey' => 'pending',
                'href' => route('conversations.index', ['status' => 'pending']),
                'active' => request('status') === 'pending',
            ];
            $queueTabs[] = [
                'label' => 'Meus',
                'href' => route('conversations.index', ['assigned' => 'mine']),
                'active' => request('assigned') === 'mine',
            ];
        @endphp
        <x-chat.queue-tabs :tabs="$queueTabs" />
        <div class="chat-list-scroll design-scrollbar">
            <div class="chat-list-items">
            @forelse($conversations as $conv)
            @php
                $convPending = $conv->isPendingInQueue();
                $convResolved = $conv->status === 'resolved' || $conv->status === 'closed';
                $shouldShow = !request('status') || (request('status') === 'pending' && $conv->isPendingInQueue());
            @endphp
            @if($shouldShow && $conv->contact)
                <x-chat.list-item
                    :href="route('conversations.index', ['conversation' => $conv->id] + request()->all())"
                    :contact="$conv->contact"
                    :conversation="$conv"
                    :active="$activeConversation?->id === $conv->id"
                    :pending="$convPending"
                    :resolved="$convResolved"
                />
            @endif
            @empty
            <div class="chat-list-empty">
                @if(request('status') === 'pending')
                    Nenhum atendimento na fila.
                @else
                    Nenhum atendimento aqui.
                @endif
            </div>
            @endforelse
            </div>
        </div>
    </section>

    <!-- Coluna: conversa -->
    <section class="flex-1 flex flex-col bg-gray-50 relative overflow-hidden min-w-0">
        <!-- Toast de Notificação -->
        <div id="notificationToast" class="fixed bottom-6 right-6 bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg shadow-lg hidden transition-all duration-300 z-50 max-w-xs animate-slideInUp">
            <div class="flex items-center gap-3">
                <span class="text-lg" id="toastIcon">📩</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold" id="toastSender">Nova mensagem</p>
                    <p class="text-xs opacity-90" id="toastMessage">Você tem uma mensagem</p>
                </div>
                <button onclick="document.getElementById('notificationToast').classList.add('hidden')" class="text-white hover:opacity-75 flex-shrink-0">✓</button>
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
                <button onclick="document.getElementById('pendingBadge').classList.add('hidden')" class="text-on-error hover:opacity-75 flex-shrink-0">✓</button>
            </div>
        </div>

        @if($activeConversation?->contact)
        <!-- Chat Header -->
        <div class="h-[66px] shrink-0 flex items-center gap-3 px-[18px] bg-white border-b border-gray-200">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-[42px] h-[42px] rounded-full bg-secondary-container text-secondary flex items-center justify-center font-bold text-base shrink-0">
                    {{ $activeConversation->contact->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-sm font-bold text-on-surface">{{ $activeConversation->contact->name }}</h2>
                        @php
                            $activeClaim = $activeConversation->getActiveClaim();
                            $isAdmin = Auth::user()->isAdmin();
                            $hasMyClaim = $activeClaim && (int) $activeClaim->user_id === (int) Auth::id();
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
        <div class="border-b border-gray-200 bg-gray-100-low">
            <details class="group cursor-pointer">
                <summary class="p-3 flex items-center gap-2 text-sm font-semibold text-on-surface hover:bg-gray-100 transition-colors list-none">
                    <span class="material-symbols-outlined text-lg group-open:hidden">expand_more</span>
                    <span class="material-symbols-outlined text-lg hidden group-open:inline">expand_less</span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">history</span>
                        Histórico de Atendimentos
                        <span class="bg-secondary-100 text-on-secondary-container text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $previousConversations->count() }}</span>
                    </span>
                </summary>
                <div class="space-y-2 p-3 border-t border-gray-200 bg-white">
                    @foreach($previousConversations as $prev)
                    <button onclick="openHistoryModal({{ $prev->id }})" class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-100-low transition-colors cursor-pointer active:scale-95">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <div>
                                <p class="text-xs font-bold text-on-surface">
                                    {{ $prev->created_at->format('d/m/Y \às H:i') }}
                                </p>
                                @php
                                    $lastClaim = $prev->claims()->latest('claimed_at')->first();
                                @endphp
                                @if($lastClaim)
                                <p class="text-[11px] text-gray-600 mt-0.5">
                                    📩 {{ $lastClaim->user->name ?? 'Agente desconhecido' }}
                                </p>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold text-white bg-green-600 px-2 py-1 rounded">✓ Resolvido</span>
                        </div>
                        @if($prev->lastMessage)
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">
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
                <span class="text-[10px] uppercase text-gray-600 bg-white/60 backdrop-blur-sm py-1.5 px-4 rounded-full tracking-wider border border-gray-200/30 shadow-sm">
                    📩 {{ $activeConversation->created_at->format('d \d\e M \d\e Y') }}
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
                                <img src="{{ Storage::url($msg->media_url) }}" alt="{{ $msg->media_filename ?? 'Imagem' }}" class="max-w-[280px] max-h-[240px] rounded-lg object-cover border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity">
                            </a>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'audio/'))
                            <div class="bg-white border border-gray-200 rounded-lg p-3 min-w-[260px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-primary text-lg">mic</span>
                                    <p class="text-xs font-bold text-on-surface truncate flex-1">{{ $msg->media_filename ?? 'Audio' }}</p>
                                    <a href="{{ Storage::url($msg->media_url) }}" download class="material-symbols-outlined text-gray-600 text-base hover:text-primary">download</a>
                                </div>
                                <audio controls class="w-full h-8" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'audio/mpeg' }}">
                                </audio>
                            </div>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'video/'))
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden max-w-[300px]">
                                <video controls class="w-full max-h-[200px]" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'video/mp4' }}">
                                </video>
                            </div>
                            @else
                            <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $msg->media_filename ?? 'Arquivo' }}</p>
                                    <p class="text-[10px] text-gray-600">{{ $msg->mime_type ?? '' }}</p>
                                </div>
                                <a href="{{ Storage::url($msg->media_url) }}" download class="material-symbols-outlined text-gray-600 text-lg hover:text-primary">download</a>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($msg->content)
                        <div class="bg-white p-4 rounded-xl rounded-bl-none border border-gray-200 shadow-sm">
                            <p class="text-sm text-on-surface leading-relaxed whitespace-pre-wrap">{{ $msg->content }}</p>
                        </div>
                        @endif
                        <span class="text-[10px] text-gray-600 mt-1 block">{{ $msg->created_at->format('H:i') }}</span>
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
                            <div class="bg-white border border-gray-200 rounded-lg p-3 min-w-[260px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-primary text-lg">mic</span>
                                    <p class="text-xs font-bold text-on-surface truncate flex-1">{{ $msg->media_filename ?? 'Audio' }}</p>
                                </div>
                                <audio controls class="w-full h-8" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'audio/mpeg' }}">
                                </audio>
                            </div>
                            @elseif(str_starts_with($msg->mime_type ?? '', 'video/'))
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden max-w-[300px]">
                                <video controls class="w-full max-h-[200px]" preload="metadata">
                                    <source src="{{ Storage::url($msg->media_url) }}" type="{{ $msg->mime_type ?? 'video/mp4' }}">
                                </video>
                            </div>
                            @else
                            <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $msg->media_filename ?? 'Arquivo' }}</p>
                                    <p class="text-[10px] text-gray-600">{{ $msg->mime_type ?? '' }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($msg->content)
                        <div class="bg-primary text-on-primary p-4 rounded-xl rounded-br-none shadow-md">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->content }}</p>
                        </div>
                        @endif
                        <div class="flex justify-end items-center gap-1 mt-1">
                            <span class="text-[10px] text-gray-600">{{ $msg->created_at->format('H:i') }}</span>
                            @if($msg->status === 'read')
                            <span class="material-symbols-outlined text-[14px] text-blue-500">done_all</span>
                            @elseif($msg->status === 'delivered')
                            <span class="material-symbols-outlined text-[14px] text-gray-600">done_all</span>
                            @elseif($msg->status === 'failed')
                            <span class="material-symbols-outlined text-[14px] text-error">error</span>
                            @else
                            <span class="material-symbols-outlined text-[14px] text-gray-600">check</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Macros Quick Bar -->
        @if($macros->count() > 0)
        <div class="px-4 py-3 bg-white/60 backdrop-blur-sm border-t border-gray-200/50">
            <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-1">
                <span class="text-[10px] font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap mr-2 flex items-center">✓ Rápido:</span>
                @foreach($macros as $macro)
                <button onclick="applyMacro('{{ addslashes($macro->content) }}')" class="whitespace-nowrap bg-white/80 border border-gray-200/50 px-3 py-1.5 rounded-full text-xs text-on-surface hover:bg-white hover:border-gray-200/80 transition-all shadow-sm hover:shadow-md shrink-0">
                    {{ $macro->name }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Chat Input -->
        @if($hasMyClaim || $isAdmin)
        <div class="p-4 bg-white/70 backdrop-blur-sm border-t border-gray-200/50 space-y-3">
            <!-- File Preview -->
            <div id="filePreview" class="hidden bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-lg p-3 flex items-center gap-3 shadow-sm">
                <div id="filePreviewThumb" class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                    <span id="filePreviewIcon" class="material-symbols-outlined text-gray-600 text-xl">description</span>
                    <img id="filePreviewImg" class="w-full h-full object-cover hidden">
                    <audio id="filePreviewAudio" class="hidden" preload="metadata"></audio>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="filePreviewName" class="text-xs font-bold text-on-surface truncate"></p>
                    <p id="filePreviewSize" class="text-[10px] text-gray-600"></p>
                </div>
                <button type="button" onclick="clearAttachment()" class="material-symbols-outlined text-gray-600 hover:text-error transition-colors text-lg">close</button>
            </div>
            <form id="chatForm" method="POST" action="{{ route('conversations.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $activeConversation->id }}">
                <div class="relative">
                    <textarea id="messageInput" name="content" rows="2" class="w-full bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-xl p-4 pr-48 focus:ring-2 focus:ring-secondary-container/50 focus:border-secondary transition-all resize-none text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm" placeholder="Escreva uma mensagem... (/ para macros)" @if(!$hasMyClaim || $activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') title="Esta conversa foi encerrada" @elseif($isAdmin) title="Clique em 'Para Mim' para reivindicar" @else title="Clamado por {{ $activeClaim && $activeClaim->user ? $activeClaim->user->name : 'outro agente' }}" @endif @endif></textarea>

                    <!-- Macros Menu -->
                    <div id="macrosMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white/95 backdrop-blur-sm border border-gray-200/50 rounded-xl shadow-lg z-50 max-h-64 overflow-y-auto custom-scrollbar">
                        <div id="macrosMenuItems" class="space-y-1 p-2"></div>
                    </div>

                    <div class="absolute right-4 bottom-4 flex items-center gap-2 text-gray-600" id="chatActions">
                        <button type="button" id="emojiBtn" class="hover:text-secondary transition-colors relative {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Emoji" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined text-xl">sentiment_satisfied</span>
                        </button>
                        <button type="button" id="improveTextBtn" class="hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Melhorar com IA" onclick="openImproveTextModal()" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined text-xl">auto_awesome</span>
                        </button>
                        <label class="cursor-pointer hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Arquivo">
                            <span class="material-symbols-outlined">attach_file</span>
                            <input type="file" name="attachment" id="fileInput" class="hidden" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                        </label>
                        <button type="button" id="audioRecordBtn" class="hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Audio" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined">mic</span>
                        </button>
                        <button type="submit" class="bg-secondary text-on-secondary w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:shadow-lg active:scale-95 transition-all {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </div>

                    <!-- Emoji Picker -->
                    <div id="emojiPicker" class="hidden absolute bottom-full right-4 mb-2 bg-white/95 backdrop-blur-md border border-gray-200/50 rounded-xl shadow-lg z-50 w-80 sm:w-96 max-h-80 flex flex-col">
                        <div class="flex gap-1 p-2 border-b border-gray-200/50 overflow-x-auto custom-scrollbar flex-shrink-0" id="emojiCategories"></div>
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
                            <div class="grid grid-cols-7 gap-1" id="emojiGrid"></div>
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
                    <p class="text-sm text-gray-600 mb-4">Reivindicado por <strong>{{ $activeClaim->user->name }}</strong></p>
                @else
                    <p class="text-sm text-gray-600 mb-4">Reivindicado por outro agente</p>
                @endif
                <p class="text-xs text-gray-600/80">Você precisa reivindicar este atendimento para responder</p>
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
                <span class="material-symbols-outlined text-6xl text-gray-600-variant">chat</span>
                <p class="text-gray-600 mt-4">Selecione uma conversa para comecar</p>
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
            <h3 class="text-lg font-bold text-on-surface mb-2">✅ Encerrar Conversa</h3>
            <p class="text-sm text-gray-600 mb-6">Registre o motivo do encerramento para gerar relatórios precisos</p>

            <form id="resolutionForm" class="space-y-4">
                @csrf
                <input type="hidden" id="conversationId" name="conversation_id">

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Motivo do Encerramento</label>
                    <select name="resolution_reason" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                        <option value="">Selecione um motivo...</option>
                        <option value="problem_solved">✓ Problema Resolvido</option>
                        <option value="customer_satisfied">📩 Cliente Satisfeito</option>
                        <option value="follow_up_needed">✓ Acompanhamento Necessário</option>
                        <option value="transferred">✅ Transferido</option>
                        <option value="duplicate">📩 Conversa Duplicada</option>
                        <option value="spam">✅ Spam/Abuso</option>
                        <option value="no_response">✅ Sem Resposta do Cliente</option>
                        <option value="other">✓ Outro</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">O que foi feito?</label>
                    <textarea name="resolution_notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Descreva as a📩es tomadas..." required></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Comentários Internos (Opcional)</label>
                    <textarea name="internal_comments" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Notas para a equipe..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeResolutionModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
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
            <h3 class="text-lg font-bold text-on-surface mb-2">📩 Pedir Reabertura</h3>
            <p class="text-sm text-gray-600 mb-6">Explique por que esta conversa precisa ser reaberta</p>

            <form id="reopenRequestForm" class="space-y-4">
                @csrf
                <input type="hidden" id="reopenConversationId" name="conversation_id">

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Motivo da Reabertura</label>
                    <textarea name="reason" rows="4" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary resize-none" placeholder="Descreva por que a conversa precisa ser reaberta..." required minlength="10"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeReopenRequestModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-info text-on-info rounded-lg font-semibold hover:opacity-90 transition-all">
                        Enviar Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($activeConversation?->contact)
    <x-chat.contact-panel :contact="$activeConversation->contact" :conversation="$activeConversation" />
    @endif

    <!-- Improve Text Modal -->
    <div id="improveTextModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="glass-modal rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined">auto_awesome</span>
                        Melhorar Texto com IA
                    </h2>
                    <button onclick="closeImproveTextModal()" class="material-symbols-outlined text-gray-600 hover:text-error transition-colors">close</button>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Tipo de Melhoria:</label>
                    <select id="improveTypeSelect" class="w-full border border-gray-200/50 rounded-lg p-3 text-sm focus:ring-2 focus:ring-secondary-container/50 focus:border-secondary transition-all" onchange="refreshImprovement()">
                        <option value="grammar">✓ Corrigir Ortografia/Gramática</option>
                        <option value="professional">👔 Reformular para Tom Profissional</option>
                        <option value="both">🎯 Ambos (Gramática + Profissional)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Texto Original:</label>
                    <div id="improveOriginalText" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar"></div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Versão Melhorada:</label>
                    <div id="improveLoadingSpinner" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 min-h-[80px] max-h-[120px] flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-6 h-6 border-2 border-secondary border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-xs text-gray-600">Processando com IA...</p>
                        </div>
                    </div>
                    <div id="improveImprovedText" class="hidden w-full bg-secondary/10 border border-secondary/30 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar whitespace-pre-wrap"></div>
                </div>

                <div id="improveErrorMessage" class="hidden bg-error/20 border border-error/30 rounded-lg p-3 text-sm text-error"></div>
            </div>

            <div class="p-6 border-t border-gray-200/50 flex gap-3 justify-end">
                <button onclick="closeImproveTextModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-on-surface border border-gray-200/50 hover:bg-gray-100/50 transition-all">
                    Cancelar
                </button>
                <button id="improveUseBtn" onclick="applyImprovedText()" disabled class="px-4 py-2 rounded-lg text-sm font-semibold bg-secondary text-on-secondary hover:shadow-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    Usar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/helpers/contact-panel.js') }}"></script>
<script src="{{ asset('js/helpers/chat-inbox.js') }}"></script>
<script src="{{ asset('js/helpers/chat-list-poller.js') }}"></script>
<script src="{{ asset('js/helpers/macros-menu.js') }}"></script>
<script src="{{ asset('js/helpers/emoji-picker.js') }}"></script>
<script>
    // Debug logging
    console.log('[App] Initializing conversation page');
    console.log('[Auth] Has claim:', {{ $hasMyClaim ?? 'false' }});
    console.log('[Auth] Is Admin:', {{ $isAdmin ?? 'false' }});

    document.getElementById('chatSearchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('[data-chat-name]').forEach((row) => {
            const name = row.dataset.chatName || '';
            const phone = row.dataset.chatPhone || '';
            row.style.display = !q || name.includes(q) || phone.includes(q) ? '' : 'none';
        });
    });

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
        if (!input || !content) return;
        const vars = window.macroVariables || {};
        let out = content;
        for (const [key, val] of Object.entries(vars)) {
            const v = val == null ? '' : String(val);
            out = out.split('{' + key + '}').join(v);
            out = out.split('{{' + key + '}}').join(v);
        }
        input.value = out;
        input.focus();
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

    chatInbox.bindSendForm(document.getElementById('chatForm'), function (data) {
        const input = document.getElementById('messageInput');
        if (input) input.value = '';
        clearAttachment();
        if (data?.conversation_id && data?.message) {
            window.dispatchEvent(new CustomEvent('chat-message-sent', {
                detail: {
                    conversation_id: data.conversation_id,
                    content: data.message.content,
                    preview: data.message.content || 'Midia',
                },
            }));
        }
    });

    chatInbox.startPolling();
    @endif

    if (typeof ChatListPoller !== 'undefined') {
        window.chatListPoller = new ChatListPoller({
            url: @json(route('conversations.list-poll', request()->query())),
            intervalMs: 4000,
            activeConversationId: {{ $activeConversation?->id ?? 'null' }},
            initialIds: @json($conversations->pluck('id')),
        });
        window.chatListPoller.start();
    }

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

    @if($activeConversation?->contact)
    window.macroVariables = {
        nome: @json($activeConversation->contact->name),
        telefone: @json($activeConversation->contact->phone),
        setor: @json($activeConversation->sector?->name ?? 'Geral'),
    };
    @endif

    if (typeof initMacrosMenu === 'function') {
        initMacrosMenu({
            url: @json(route('conversations.macros-json')),
            variables: window.macroVariables || {},
            initialMacros: @json($macros ?? []),
        });
    }

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
            document.getElementById('toastIcon').textContent = '📩';
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
                            ${idx < events.length - 1 ? '<div class="w-1 h-12 bg-gray-100"></div>' : ''}
                        </div>
                        <div class="pt-1">
                            <h4 class="font-semibold text-on-surface">${event.title}</h4>
                            <p class="text-xs text-gray-600 mt-0.5">${event.description}</p>
                            <p class="text-[10px] text-gray-600 mt-1">${new Date(event.timestamp).toLocaleString('pt-BR')}</p>
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
                                <div class="bg-white p-3 rounded-lg rounded-bl-none border border-gray-200 max-w-xs">
                                    <p class="text-sm text-on-surface">${msg.content}</p>
                                </div>
                                <span class="text-[10px] text-gray-600 mt-0.5 block ml-8">${msg.created_at}</span>
                            </div>
                        </div>
                    `;
                } else {
                    messagesHtml += `
                        <div class="flex flex-col items-end mb-3">
                            <div class="bg-primary text-on-primary p-3 rounded-lg rounded-br-none max-w-xs">
                                <p class="text-sm">${msg.content}</p>
                            </div>
                            <span class="text-[10px] text-gray-600 mt-0.5 mr-8">${msg.created_at}</span>
                        </div>
                    `;
                }
            });

            modal.innerHTML = `
                <style>
                    .glass-modal { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); }
                </style>
                <div class="glass-modal rounded-xl shadow-2xl flex flex-col w-full max-w-4xl max-h-[85vh] border border-white/30">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-on-surface">📩 Histórico: ${conv.contact_name}</h2>
                            <div class="flex gap-6 mt-3 text-sm text-gray-600">
                                <span>📩 ${conv.contact_phone}</span>
                                <span>✅ Duração: ${conv.duration}</span>
                                <span>📩 ${conv.message_count} mensagens</span>
                                <span>📩 Atendido por: ${conv.claimed_by}</span>
                            </div>
                        </div>
                        <button onclick="document.getElementById('historyModal').remove()" class="text-gray-600 hover:text-on-surface text-2xl">✓</button>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar flex gap-4">
                        <div class="w-1/3 p-6 border-r border-gray-200 bg-surface-bright">
                            <h3 class="font-bold text-on-surface mb-4">Timeline de Eventos</h3>
                            <div class="space-y-2">
                                ${eventsHtml || '<p class="text-sm text-gray-600">Nenhum evento registrado</p>'}
                            </div>
                        </div>
                        <div class="w-2/3 p-6 space-y-2">
                            <h3 class="font-bold text-on-surface mb-4">Mensagens (${messages.length})</h3>
                            ${messagesHtml || '<div class="text-center text-gray-600 text-sm py-8">Nenhuma mensagem neste atendimento</div>'}
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-200 flex justify-end">
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

    // Polling: ChatListPoller + ChatInboxHelper (SSEManager desativado aqui — evita ERR_NO_BUFFER_SPACE)
</script>
@endpush



