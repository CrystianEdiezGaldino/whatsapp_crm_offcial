@extends('layouts.app')

@section('title', 'Contatos')

@section('content')
<header class="flex justify-between items-center h-16 px-6 w-full sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-gray-200">
    <div class="flex items-center gap-4 flex-1">
        <div class="relative w-full max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-600">search</span>
            <form method="GET" action="{{ route('contacts.index') }}" class="flex">
                <input name="search" value="{{ request('search') }}" class="w-full bg-gray-100-low border border-gray-200 rounded-lg pl-11 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Pesquisar por nome ou WhatsApp..." type="text">
            </form>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-1 px-3 py-1 bg-secondary-100/20 text-on-secondary-container rounded-full">
            <span class="w-2 h-2 bg-secondary rounded-full"></span>
            <span class="text-xs font-semibold">Online</span>
        </div>
    </div>
</header>

<div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-primary">Gestao de Clientes</h2>
            <p class="text-sm text-gray-600">Gerencie sua base de contatos e segmentacoes do WhatsApp.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('newContactModal').classList.remove('hidden')" class="bg-primary text-on-primary text-xs font-semibold px-4 py-2 flex items-center gap-1 active:scale-95 transition-transform">
                <span class="material-symbols-outlined text-base">person_add</span>
                Novo Contato
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <form method="GET" action="{{ route('contacts.index') }}" class="bg-white p-4 border border-gray-200 shadow-sm">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Filtrar por Tag</label>
            <select name="tag" onchange="this.form.submit()" class="w-full border-none bg-gray-100-low focus:ring-1 focus:ring-secondary text-sm rounded">
                <option value="">Todas as Tags</option>
                <option value="VIP" {{ request('tag') === 'VIP' ? 'selected' : '' }}>VIP</option>
                <option value="Suporte" {{ request('tag') === 'Suporte' ? 'selected' : '' }}>Suporte</option>
                <option value="Lead Frio" {{ request('tag') === 'Lead Frio' ? 'selected' : '' }}>Lead Frio</option>
                <option value="Novo Lead" {{ request('tag') === 'Novo Lead' ? 'selected' : '' }}>Novo Lead</option>
            </select>
        </form>
        <form method="GET" action="{{ route('contacts.index') }}" class="bg-white p-4 border border-gray-200 shadow-sm">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Agente Responsavel</label>
            <select name="agent" onchange="this.form.submit()" class="w-full border-none bg-gray-100-low focus:ring-1 focus:ring-secondary text-sm rounded">
                <option value="">Todos os Agentes</option>
                @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ request('agent') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100-low border-b border-gray-200">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Contato</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">WhatsApp</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tags</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Ultimo Contato</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Agente</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($contacts as $contact)
                    <tr class="hover:bg-gray-100-low/50 transition-colors group">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-sm text-on-primary-fixed">
                                    {{ $contact->initials }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-primary">{{ $contact->name }}</p>
                                    <p class="text-xs text-gray-600">{{ $contact->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1 text-sm text-on-surface">
                                <span class="material-symbols-outlined text-secondary text-base">phone_android</span>
                                {{ $contact->phone }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($contact->tags ?? [] as $tag)
                                <span class="px-1 py-0.5 bg-secondary-100/30 text-on-secondary-container text-xs font-semibold rounded">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ $contact->last_message_at?->diffForHumans() ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            {{ $contact->assignedUser?->name ?? 'Sem agente' }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <form method="POST" action="{{ route('conversations.start') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                                    <button type="submit" class="p-1 hover:bg-secondary-100/20 text-secondary rounded transition-colors" title="Iniciar Chat">
                                        <span class="material-symbols-outlined text-lg">chat</span>
                                    </button>
                                </form>
                                <button onclick="editContact({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ $contact->phone }}', '{{ $contact->email }}', '{{ implode(',', $contact->tags ?? []) }}', '{{ addslashes($contact->notes ?? '') }}')" class="p-1 hover:bg-gray-100-highest text-gray-600 rounded transition-colors" title="Editar">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="inline" onsubmit="return confirm('Remover contato?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1 hover:bg-red-50 text-error rounded transition-colors" title="Remover">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-100-low flex items-center justify-between border-t border-gray-200">
            <span class="text-xs text-gray-600">Exibindo {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} de {{ $contacts->total() }} contatos</span>
            {{ $contacts->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- New Contact Modal -->
<div id="newContactModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-on-surface">Novo Contato</h3>
            <button onclick="document.getElementById('newContactModal').classList.add('hidden')" class="text-gray-600 hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('contacts.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Nome</label>
                    <input name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Nome completo">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">WhatsApp</label>
                    <input name="phone" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="5511999999999">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Email</label>
                    <input name="email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="email@exemplo.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Tags (separar por virgula)</label>
                    <input name="tags" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="VIP, Suporte">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Notas</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Observacoes..."></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('newContactModal').classList.add('hidden')" class="flex-1 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2 rounded-lg text-sm font-semibold active:scale-95 transition-transform">Criar Contato</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Contact Modal -->
<div id="editContactModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-on-surface">Editar Contato</h3>
            <button onclick="document.getElementById('editContactModal').classList.add('hidden')" class="text-gray-600 hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editContactForm" method="POST" action="">
            @csrf @method('PUT')
            <div class="space-y-4">
                <input type="hidden" id="edit_contact_id">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Nome</label>
                    <input id="edit_name" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">WhatsApp</label>
                    <input id="edit_phone" name="phone" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Email</label>
                    <input id="edit_email" name="email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Tags</label>
                    <input id="edit_tags" name="tags" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Notas</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('editContactModal').classList.add('hidden')" class="flex-1 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2 rounded-lg text-sm font-semibold active:scale-95 transition-transform">Salvar</button>
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
