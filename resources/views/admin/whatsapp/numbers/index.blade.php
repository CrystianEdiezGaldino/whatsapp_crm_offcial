@extends('layouts.app')

@section('title', 'Números WhatsApp')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-on-surface">Números WhatsApp</h1>
        <div class="flex gap-2">
            <button onclick="syncWithSystemToken()" class="bg-success text-on-success px-4 py-2 rounded-lg font-semibold hover:opacity-90 flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined">verified_user</span> Usar Token do Sistema
            </button>
            <button onclick="openSyncModal()" class="bg-info text-on-info px-4 py-2 rounded-lg font-semibold hover:opacity-90 flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined">sync</span> Importar da Meta
            </button>
            <a href="{{ route('admin.whatsapp.numbers.create') }}" class="bg-primary text-on-primary px-4 py-2 rounded-lg font-semibold hover:opacity-90 flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined">add</span> Novo Número
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if($numbers->isEmpty())
    <div class="bg-gray-100 p-8 rounded-lg text-center">
        <span class="material-symbols-outlined text-5xl text-gray-600 mb-4 block">phone</span>
        <p class="text-gray-600 text-lg">Nenhum número cadastrado ainda</p>
        <a href="{{ route('admin.whatsapp.numbers.create') }}" class="mt-4 inline-block bg-primary text-on-primary px-4 py-2 rounded-lg font-semibold">
            Adicionar Primeiro Número
        </a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($numbers as $number)
        <div class="bg-white border border-gray-200 rounded-lg p-6 flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-lg font-bold text-on-surface">{{ $number->display_name }}</h3>
                    @if($number->is_active)
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span> Ativo
                    </span>
                    @endif
                    @if($number->verified_at)
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">verified</span> Verificado
                    </span>
                    @else
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                        Não Verificado
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mb-2">
                    <span class="font-semibold">Número:</span> {{ $number->phone_number }}
                </p>
                @if($number->business_account_id)
                <p class="text-xs text-gray-600">
                    <span class="font-semibold">ID Conta:</span> {{ $number->business_account_id }}
                </p>
                @endif
            </div>

            <div class="flex gap-2">
                @if(!$number->is_active)
                <button onclick="setActive({{ $number->id }})" class="bg-secondary text-on-secondary px-4 py-2 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                    <span class="material-symbols-outlined text-sm">radio_button_unchecked</span> Ativar
                </button>
                @endif

                @if(!$number->verified_at)
                <button onclick="verify({{ $number->id }})" class="bg-info text-on-info px-4 py-2 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                    <span class="material-symbols-outlined text-sm">verified_user</span> Verificar
                </button>
                @endif

                @if(!$number->is_active)
                <button onclick="deleteNumber({{ $number->id }})" class="bg-error text-on-error px-4 py-2 rounded-lg text-xs font-semibold hover:opacity-90 flex items-center gap-1 transition-all">
                    <span class="material-symbols-outlined text-sm">delete</span> Remover
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

    <!-- Sync Meta Modal -->
    <div id="syncMetaModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-on-surface mb-4">Importar Números da Meta</h3>
            <p class="text-sm text-gray-600 mb-6">Cole seu access token da Meta. Opcionalmente, informe o WABA ID se a detecção automática falhar.</p>

            <form id="syncMetaForm" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">Access Token *</label>
                    <textarea name="access_token" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-info focus:border-info font-mono text-xs" rows="3" placeholder="Cole seu access token da Meta"></textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">WABA ID (opcional)</label>
                    <input type="text" name="business_account_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-info focus:border-info" placeholder="Ex: 123456789012345">
                    <p class="text-xs text-gray-500 mt-1">Se deixar vazio, será feita detecção automática ou usará WA_WABA_ID do .env</p>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeSyncModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-on-surface hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-info text-on-info rounded-lg font-semibold hover:opacity-90 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">sync</span> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    async function syncWithSystemToken() {
        if (!confirm('Importar números usando o token do sistema?\n\nIsso buscará os números WhatsApp da sua Business Account registrada.')) {
            return;
        }

        try {
            const response = await fetch('{{ route("admin.whatsapp.numbers.sync-meta") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ access_token: 'system' }),
            });

            const data = await response.json();
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Erro: ' + (data.message || 'Erro ao importar números'));
            }
        } catch (error) {
            alert('❌ Erro: ' + error.message);
        }
    }

    function openSyncModal() {
        document.getElementById('syncMetaModal').classList.remove('hidden');
    }

    function closeSyncModal() {
        document.getElementById('syncMetaModal').classList.add('hidden');
        document.getElementById('syncMetaForm').reset();
    }

    document.getElementById('syncMetaForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('{{ route("admin.whatsapp.numbers.sync-meta") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData)),
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                closeSyncModal();
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Erro ao importar números'));
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    });

    // Close modal on outside click
    document.getElementById('syncMetaModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSyncModal();
    });

    async function setActive(numberId) {
        if (!confirm('Definir este número como ativo? Isso afetará todo o sistema.')) return;

        try {
            const response = await fetch(`/admin/whatsapp/numbers/${numberId}/set-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }

    async function verify(numberId) {
        try {
            const response = await fetch(`/admin/whatsapp/numbers/${numberId}/verify`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }

    async function deleteNumber(numberId) {
        if (!confirm('Remover este número? Esta ação não pode ser desfeita.')) return;

        try {
            const response = await fetch(`/admin/whatsapp/numbers/${numberId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }
</script>
@endsection
