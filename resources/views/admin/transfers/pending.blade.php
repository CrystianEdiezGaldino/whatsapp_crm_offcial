@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Transferências Pendentes</h1>
        <a href="{{ route('admin.transfers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Voltar
        </a>
    </div>

    @if (empty($transfers))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            Nenhuma transferência pendente
        </div>
    @else
        <div class="space-y-4">
            @foreach ($transfers as $transfer)
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="text-gray-600 font-semibold text-sm">Contato</label>
                            <p class="text-lg text-gray-900">{{ $transfer->conversation->contact->name }}</p>
                        </div>
                        <div>
                            <label class="text-gray-600 font-semibold text-sm">De</label>
                            <p class="text-gray-900">{{ $transfer->fromUser?->name }}</p>
                        </div>
                        <div>
                            <label class="text-gray-600 font-semibold text-sm">Para</label>
                            <p class="text-gray-900">{{ $transfer->toUser?->name }}</p>
                        </div>
                        <div>
                            <label class="text-gray-600 font-semibold text-sm">Data</label>
                            <p class="text-gray-900">{{ $transfer->requested_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-gray-600 font-semibold text-sm">Motivo</label>
                        <p class="text-gray-700">{{ $transfer->reason ?? 'Sem motivo' }}</p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.transfers.show', $transfer) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
