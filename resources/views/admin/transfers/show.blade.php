@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detalhes da Transferência</h1>
        <a href="{{ route('admin.transfers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="text-gray-600 font-semibold">Contato</label>
                <p class="text-lg text-gray-900">{{ $transfer->conversation->contact->name }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Status</label>
                <p>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold"
                        :class="{ 'bg-yellow-100 text-yellow-800': '{{ $transfer->status }}' === 'pending', 'bg-blue-100 text-blue-800': '{{ $transfer->status }}' === 'approved', 'bg-green-100 text-green-800': '{{ $transfer->status }}' === 'completed', 'bg-red-100 text-red-800': '{{ $transfer->status }}' === 'rejected' }">
                        {{ ucfirst($transfer->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="text-gray-600 font-semibold">De (Atendente)</label>
                <p class="text-gray-900">{{ $transfer->fromUser?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Para (Atendente)</label>
                <p class="text-gray-900">{{ $transfer->toUser?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">De (Setor)</label>
                <p class="text-gray-900">{{ $transfer->fromSector?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Para (Setor)</label>
                <p class="text-gray-900">{{ $transfer->toSector?->name ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mb-6 pb-6 border-b">
            <label class="text-gray-600 font-semibold block mb-2">Motivo da Transferência</label>
            <div class="bg-gray-50 p-4 rounded">
                {{ $transfer->reason ?? 'Sem motivo especificado' }}
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="text-gray-600 font-semibold">Solicitado Por</label>
                <p class="text-gray-900">{{ $transfer->requestedBy?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Solicitado Em</label>
                <p class="text-gray-900">{{ $transfer->requested_at->format('d/m/Y H:i') }}</p>
            </div>
            @if ($transfer->approved_at)
                <div>
                    <label class="text-gray-600 font-semibold">Aprovado Em</label>
                    <p class="text-gray-900">{{ $transfer->approved_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif
        </div>

        @if ($transfer->status === 'rejected')
            <div class="bg-red-50 p-4 rounded border border-red-200 mb-6">
                <label class="text-gray-600 font-semibold block mb-2">Motivo da Rejeição</label>
                <p class="text-gray-900">{{ $transfer->rejection_reason }}</p>
            </div>
        @endif

        @if ($transfer->status === 'pending')
            <div class="flex gap-4">
                <form action="{{ route('admin.transfers.approve', $transfer) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Aprovar
                    </button>
                </form>

                <button onclick="showRejectForm()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Rejeitar
                </button>
            </div>

            <form action="{{ route('admin.transfers.reject', $transfer) }}" method="POST" id="rejectForm" style="display:none;" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-gray-700 font-bold mb-2">Motivo da Rejeição</label>
                    <textarea id="reason" name="reason" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Confirmar Rejeição
                </button>
                <button type="button" onclick="hideRejectForm()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2">
                    Cancelar
                </button>
            </form>
        @elseif ($transfer->status === 'approved')
            <form action="{{ route('admin.transfers.complete', $transfer) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Marcar como Concluída
                </button>
            </form>
        @endif
    </div>
</div>

<script>
    function showRejectForm() {
        document.getElementById('rejectForm').style.display = 'block';
    }
    function hideRejectForm() {
        document.getElementById('rejectForm').style.display = 'none';
    }
</script>
@endsection
