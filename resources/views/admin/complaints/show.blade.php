@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detalhes da Reclamação</h1>
        <a href="{{ route('admin.complaints.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="text-gray-600 font-semibold">Contato</label>
                <p class="text-lg text-gray-900">{{ $complaint->conversation->contact->name }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Responsável</label>
                <p class="text-lg text-gray-900">{{ $complaint->responsible?->name ?? 'Não atribuído' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Rating</label>
                <p class="text-lg text-{{ $complaint->rating <= 2 ? 'red' : ($complaint->rating === 3 ? 'yellow' : 'green') }}-600 font-bold">
                    {{ $complaint->rating }}/5
                </p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Severidade</label>
                <p class="text-lg">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold"
                        :class="{ 'bg-red-100 text-red-800': '{{ $complaint->severity }}' === 'high', 'bg-yellow-100 text-yellow-800': '{{ $complaint->severity }}' === 'medium', 'bg-green-100 text-green-800': '{{ $complaint->severity }}' === 'low' }">
                        {{ ucfirst($complaint->severity) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mb-6">
            <label class="text-gray-600 font-semibold block mb-2">Mensagem do Cliente</label>
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                {{ $complaint->customer_note ?? 'Sem mensagem' }}
            </div>
        </div>

        <div class="mb-6">
            <label class="text-gray-600 font-semibold block mb-2">Status</label>
            <p class="text-lg">
                <span class="px-3 py-1 rounded-full text-sm font-semibold"
                    :class="{ 'bg-red-100 text-red-800': in_array('{{ $complaint->status }}', ['open', 'reviewing']), 'bg-green-100 text-green-800': '{{ $complaint->status }}' === 'resolved' }">
                    {{ ucfirst($complaint->status) }}
                </span>
            </p>
        </div>

        @if ($complaint->status === 'resolved' || $complaint->status === 'dismissed')
            <div class="bg-blue-50 p-4 rounded border border-blue-200">
                <label class="text-gray-600 font-semibold block mb-2">Notas de Revisão</label>
                <p class="text-gray-900 mb-4">{{ $complaint->review_notes }}</p>
                <label class="text-gray-600 font-semibold block mb-2">Ação Tomada</label>
                <p class="text-gray-900">{{ ucfirst($complaint->action_taken) }}</p>
            </div>
        @endif

        @if ($complaint->status === 'open')
            <div class="flex gap-4">
                <a href="{{ route('admin.complaints.review', $complaint) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Revisar
                </a>
                <a href="{{ route('admin.complaints.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Voltar
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
