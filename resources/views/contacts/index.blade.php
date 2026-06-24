@extends('layouts.app', ['fullHeight' => true])

@section('title', 'Contatos')

@section('content')
<div class="master-detail">
    <!-- Left Panel: Contact List -->
    <div class="master-panel" style="width: 380px;">
        <div class="master-panel-header">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-extrabold text-gray-900">Contatos</h2>
                <button type="button" onclick="document.getElementById('newContactModal').classList.remove('hidden')" class="w-[38px] h-[38px] rounded-[11px] bg-primary text-white flex items-center justify-center hover:bg-primary-dark transition-colors" title="Novo Contato">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                </button>
            </div>
            <form method="GET" action="{{ route('contacts.index') }}" class="relative">
                <span class="material-symbols-outlined text-gray-400 text-[16px] absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar contato..." class="input-primary !pl-9 !h-[38px] !text-[13px]">
            </form>
        </div>

        <div class="master-panel-list design-scrollbar">
            @forelse($contacts as $contact)
            <a href="{{ route('contacts.index', ['contact' => $contact->id] + request()->except('contact')) }}" class="master-panel-item {{ ($selectedContact ?? null)?->id === $contact->id ? 'active' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-[38px] h-[38px] rounded-full bg-[#EEF0FE] text-secondary flex items-center justify-center font-bold text-[13px] shrink-0">
                        {{ $contact->initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[13.5px] font-bold text-gray-900 truncate">{{ $contact->name }}</h3>
                        <p class="text-[12px] text-gray-400 font-medium truncate">{{ $contact->phone }}</p>
                    </div>
                    @if(!empty($contact->tags))
                    <span class="sector-tag text-[10px]">{{ is_array($contact->tags) ? ($contact->tags[0] ?? '') : '' }}</span>
                    @endif
                </div>
            </a>
            @empty
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">person_search</span>
                <p class="text-sm text-gray-400 font-semibold">Nenhum contato encontrado</p>
            </div>
            @endforelse

            @if($contacts->hasPages())
            <div class="p-3 border-t border-[#F2F4F8]">
                {{ $contacts->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Right Panel: Contact Detail -->
    <div class="detail-panel design-scrollbar">
        @if($selectedContact ?? null)
        <div class="max-w-2xl mx-auto">
            <!-- Contact Header Card -->
            <div class="card-primary mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#EEF0FE] text-secondary flex items-center justify-center font-bold text-xl shrink-0">
                        {{ $selectedContact->initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-extrabold text-gray-900 mb-1">{{ $selectedContact->name }}</h2>
                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">phone</span>
                            {{ $selectedContact->phone }}
                        </p>
                        @if($selectedContact->email)
                        <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-0.5">
                            <span class="material-symbols-outlined text-[16px]">mail</span>
                            {{ $selectedContact->email }}
                        </p>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <form method="POST" action="{{ route('conversations.start') }}" class="inline">
                            @csrf
                            <input type="hidden" name="contact_id" value="{{ $selectedContact->id }}">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-primary-dark flex items-center gap-1.5 transition-all">
                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                Iniciar atendimento
                            </button>
                        </form>
                        <button type="button" onclick="editContact({{ $selectedContact->id }}, '{{ addslashes($selectedContact->name) }}', '{{ $selectedContact->phone }}', '{{ $selectedContact->email }}', '{{ implode(',', $selectedContact->tags ?? []) }}', '{{ addslashes($selectedContact->notes ?? '') }}')" class="bg-white border border-[#E2E5EE] text-gray-700 px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-gray-50 flex items-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                            Editar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-3 mb-5">
                <div class="kpi-card text-center">
                    <p class="kpi-label">Matrícula</p>
                    <p class="kpi-value text-lg mt-1">{{ $selectedContact->registration ?? '—' }}</p>
                </div>
                <div class="kpi-card text-center">
                    <p class="kpi-label">Categoria</p>
                    <p class="kpi-value text-lg mt-1">{{ is_array($selectedContact->tags) && count($selectedContact->tags) > 0 ? $selectedContact->tags[0] : '—' }}</p>
                </div>
                <div class="kpi-card text-center">
                    <p class="kpi-label">Cadastrado</p>
                    <p class="kpi-value text-lg mt-1">{{ $selectedContact->created_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="kpi-card text-center">
                    <p class="kpi-label">Mensagens</p>
                    <p class="kpi-value text-lg mt-1">{{ $selectedContact->messages_count ?? 0 }}</p>
                </div>
            </div>

            <!-- Tags -->
            @if(!empty($selectedContact->tags) && is_array($selectedContact->tags))
            <div class="card-primary mb-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($selectedContact->tags as $tag)
                    <span class="tag-chip">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($selectedContact->notes)
            <div class="card-primary mb-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Notas</h3>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $selectedContact->notes }}</p>
            </div>
            @endif

            <!-- Recent Conversations -->
            <div class="card-primary">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Conversas recentes</h3>
                @php
                    $recentConversations = $selectedContact->conversations()->latest()->take(5)->get();
                @endphp
                @forelse($recentConversations as $conv)
                <a href="{{ route('conversations.index', ['conversation' => $conv->id]) }}" class="flex items-center gap-3 p-3 rounded-[10px] hover:bg-gray-50 transition-colors -mx-1">
                    <span class="material-symbols-outlined text-gray-400 text-[18px]">chat_bubble_outline</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $conv->lastMessage?->content ?? 'Sem mensagens' }}</p>
                        <p class="text-[11px] text-gray-400">{{ $conv->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $conv->status === 'resolved' || $conv->status === 'closed' ? 'bg-gray-100 text-gray-500' : 'bg-[#E8F8EF] text-primary' }}">
                        {{ $conv->status === 'resolved' || $conv->status === 'closed' ? 'Encerrado' : 'Aberto' }}
                    </span>
                </a>
                @empty
                <p class="text-sm text-gray-400 font-medium py-2">Nenhuma conversa registrada</p>
                @endforelse
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-full text-center">
            <span class="material-symbols-outlined text-6xl text-gray-200 mb-4">person_book</span>
            <h3 class="text-lg font-bold text-gray-400 mb-1">Selecione um contato</h3>
            <p class="text-sm text-gray-400">Escolha um contato na lista para ver seus detalhes</p>
        </div>
        @endif
    </div>
</div>

<!-- Novo contato -->
<div id="newContactModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="modal-card w-full max-w-md">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-extrabold text-gray-900">Novo Contato</h3>
            <button type="button" onclick="document.getElementById('newContactModal').classList.add('hidden')" class="modal-close-btn">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('contacts.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome</label>
                <input type="text" name="name" class="input-primary" placeholder="Nome completo" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">WhatsApp</label>
                <input type="text" name="phone" class="input-primary" placeholder="5511999999999" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">E-mail</label>
                <input type="email" name="email" class="input-primary" placeholder="email@exemplo.com">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tags</label>
                <input type="text" name="tags" class="input-primary" placeholder="VIP, Suporte">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Notas</label>
                <textarea name="notes" rows="2" class="textarea-primary" placeholder="Observações..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('newContactModal').classList.add('hidden')" class="btn-secondary text-sm px-4 py-2">Cancelar</button>
                <button type="submit" class="btn-primary text-sm px-4 py-2">Criar contato</button>
            </div>
        </form>
    </div>
</div>

<!-- Editar contato -->
<div id="editContactModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="modal-card w-full max-w-md">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-extrabold text-gray-900">Editar Contato</h3>
            <button type="button" onclick="document.getElementById('editContactModal').classList.add('hidden')" class="modal-close-btn">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <form id="editContactForm" method="POST" action="" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" id="edit_contact_id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome</label>
                <input type="text" id="edit_name" name="name" class="input-primary" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">WhatsApp</label>
                <input type="text" id="edit_phone" name="phone" class="input-primary" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">E-mail</label>
                <input type="email" id="edit_email" name="email" class="input-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tags</label>
                <input type="text" id="edit_tags" name="tags" class="input-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Notas</label>
                <textarea id="edit_notes" name="notes" rows="2" class="textarea-primary"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editContactModal').classList.add('hidden')" class="btn-secondary text-sm px-4 py-2">Cancelar</button>
                <button type="submit" class="btn-primary text-sm px-4 py-2">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editContact(id, name, phone, email, tags, notes) {
    document.getElementById('edit_contact_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_tags').value = tags;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editContactForm').action = '{{ url("/contacts") }}/' + id;
    document.getElementById('editContactModal').classList.remove('hidden');
}
</script>
@endpush
