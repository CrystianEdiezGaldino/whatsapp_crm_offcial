@extends('layouts.app')

@section('title', 'Contatos')

@section('content')
<x-layout.page-header title="Contatos" subtitle="Gerencie sua base de contatos do WhatsApp">
    <x-slot:search>
        <form method="GET" action="{{ route('contacts.index') }}" class="w-full">
            <x-common.search-input
                name="search"
                :value="request('search')"
                placeholder="Pesquisar por nome ou WhatsApp..."
            />
        </form>
    </x-slot:search>

    <span class="status-pill">
        <span class="status-pill__dot"></span>
        Online
    </span>

    <x-common.button type="button" variant="primary" size="sm" onclick="document.getElementById('newContactModal').classList.remove('hidden')">
        <span class="material-symbols-outlined text-[16px]">person_add</span>
        Novo Contato
    </x-common.button>
</x-layout.page-header>

<div class="page-body design-scrollbar">
    @if(session('success'))
        <div class="alert-inset-error !bg-[#E8F8EF] !text-[#1DA85A] !shadow-none border border-[#C8EDD8] mb-4">
            {{ session('success') }}
        </div>
    @endif

    <x-layout.page-section title="Filtros">
        <div class="form-grid form-grid--filters-2">
            <form method="GET" action="{{ route('contacts.index') }}" class="contents">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <x-common.select
                    name="tag"
                    label="Tag"
                    variant="inset"
                    placeholder="Todas as tags"
                    :options="['VIP' => 'VIP', 'Suporte' => 'Suporte', 'Lead Frio' => 'Lead Frio', 'Novo Lead' => 'Novo Lead']"
                    :value="request('tag')"
                    onchange="this.form.submit()"
                />
            </form>
            <form method="GET" action="{{ route('contacts.index') }}" class="contents">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('tag'))
                    <input type="hidden" name="tag" value="{{ request('tag') }}">
                @endif
                <x-common.select
                    name="agent"
                    label="Agente responsável"
                    variant="inset"
                    placeholder="Todos os agentes"
                    :options="$agents->pluck('name', 'id')->all()"
                    :value="request('agent')"
                    onchange="this.form.submit()"
                />
            </form>
        </div>
    </x-layout.page-section>

    <x-layout.page-section>
        <x-layout.data-table
            title="Lista de contatos"
            :subtitle="$contacts->total() . ' contatos cadastrados'"
        >
            <thead>
                <tr>
                    <th>Contato</th>
                    <th>WhatsApp</th>
                    <th>Tags</th>
                    <th>Último contato</th>
                    <th>Agente</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>
                        <div class="contact-row">
                            <x-common.contact-avatar :initials="$contact->initials" />
                            <div class="min-w-0">
                                <p class="contact-row__name truncate">{{ $contact->name }}</p>
                                <p class="contact-row__meta truncate">{{ $contact->email ?: 'Sem e-mail' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5 font-semibold text-gray-700">
                            <span class="material-symbols-outlined text-secondary text-[18px]">phone_android</span>
                            {{ $contact->phone }}
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @forelse($contact->tags ?? [] as $tag)
                                <span class="badge badge-info">{{ $tag }}</span>
                            @empty
                                <span class="text-xs text-gray-400 font-semibold">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="text-gray-600 font-medium">
                        {{ $contact->last_message_at?->diffForHumans() ?? '—' }}
                    </td>
                    <td class="font-semibold text-gray-700">
                        {{ $contact->assignedUser?->name ?? 'Sem agente' }}
                    </td>
                    <td>
                        <div class="table-actions">
                            <form method="POST" action="{{ route('conversations.start') }}" class="inline">
                                @csrf
                                <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                                <button type="submit" class="icon-btn" title="Iniciar chat">
                                    <span class="material-symbols-outlined text-[18px]">chat</span>
                                </button>
                            </form>
                            <button type="button" onclick="editContact({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ $contact->phone }}', '{{ $contact->email }}', '{{ implode(',', $contact->tags ?? []) }}', '{{ addslashes($contact->notes ?? '') }}')" class="icon-btn" title="Editar">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="inline" onsubmit="return confirm('Remover contato?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn !text-error hover:!bg-red-50" title="Remover">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>

            <x-slot:footer>
                <span>Exibindo {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} de {{ $contacts->total() }} contatos</span>
                {{ $contacts->withQueryString()->links() }}
            </x-slot:footer>
        </x-layout.data-table>
    </x-layout.page-section>
</div>

<!-- Novo contato -->
<div id="newContactModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="card-auth !max-w-md w-full">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-extrabold text-gray-900">Novo Contato</h3>
            <button type="button" onclick="document.getElementById('newContactModal').classList.add('hidden')" class="icon-btn">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('contacts.store') }}" class="space-y-4">
            @csrf
            <x-common.input name="name" label="Nome" variant="inset" placeholder="Nome completo" required />
            <x-common.input name="phone" label="WhatsApp" variant="inset" placeholder="5511999999999" required />
            <x-common.input name="email" type="email" label="E-mail" variant="inset" placeholder="email@exemplo.com" />
            <x-common.input name="tags" label="Tags" variant="inset" placeholder="VIP, Suporte" />
            <x-common.textarea name="notes" label="Notas" variant="inset" rows="2" placeholder="Observações..." />
            <div class="form-actions pt-2">
                <x-common.button type="button" variant="secondary" onclick="document.getElementById('newContactModal').classList.add('hidden')">Cancelar</x-common.button>
                <x-common.button type="submit" variant="primary">Criar contato</x-common.button>
            </div>
        </form>
    </div>
</div>

<!-- Editar contato -->
<div id="editContactModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="card-auth !max-w-md w-full">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-extrabold text-gray-900">Editar Contato</h3>
            <button type="button" onclick="document.getElementById('editContactModal').classList.add('hidden')" class="icon-btn">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <form id="editContactForm" method="POST" action="" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" id="edit_contact_id">
            <x-common.input id="edit_name" name="name" label="Nome" variant="inset" required />
            <x-common.input id="edit_phone" name="phone" label="WhatsApp" variant="inset" required />
            <x-common.input id="edit_email" name="email" type="email" label="E-mail" variant="inset" />
            <x-common.input id="edit_tags" name="tags" label="Tags" variant="inset" />
            <x-common.textarea id="edit_notes" name="notes" label="Notas" variant="inset" rows="2" />
            <div class="form-actions pt-2">
                <x-common.button type="button" variant="secondary" onclick="document.getElementById('editContactModal').classList.add('hidden')">Cancelar</x-common.button>
                <x-common.button type="submit" variant="primary">Salvar</x-common.button>
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
