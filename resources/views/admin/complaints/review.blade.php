@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Revisar Reclamação</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="mb-6 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Detalhes da Reclamação</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-gray-600 font-semibold">Contato</label>
                    <p class="text-gray-900">{{ $complaint->conversation->contact->name }}</p>
                </div>
                <div>
                    <label class="text-gray-600 font-semibold">Rating</label>
                    <p class="text-{{ $complaint->rating <= 2 ? 'red' : 'orange' }}-600 font-bold">{{ $complaint->rating }}/5</p>
                </div>
                <div>
                    <label class="text-gray-600 font-semibold">Responsável</label>
                    <p class="text-gray-900">{{ $complaint->responsible?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-gray-600 font-semibold">Severidade</label>
                    <p>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold"
                            :class="{ 'bg-red-100 text-red-800': '{{ $complaint->severity }}' === 'high', 'bg-yellow-100 text-yellow-800': '{{ $complaint->severity }}' === 'medium' }">
                            {{ ucfirst($complaint->severity) }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <label class="text-gray-600 font-semibold block mb-2">Mensagem do Cliente</label>
                <div class="bg-gray-50 p-4 rounded">
                    {{ $complaint->customer_note ?? 'Sem mensagem' }}
                </div>
            </div>
        </div>

        <div class="mb-6 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Conversa Relacionada</h2>
            <div class="bg-gray-50 p-4 rounded">
                <p><strong>ID:</strong> {{ $complaint->conversation->id }}</p>
                <p><strong>Status:</strong> {{ ucfirst($complaint->conversation->status) }}</p>
                <p><strong>Prioridade:</strong> {{ ucfirst($complaint->conversation->priority_level) }}</p>
            </div>
        </div>

        <h2 class="text-xl font-bold text-gray-800 mb-4">Ações</h2>

        <div class="space-y-4">
            <form action="{{ route('admin.complaints.resolve', $complaint) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="review_notes" class="block text-gray-700 font-bold mb-2">Notas de Revisão</label>
                    <textarea id="review_notes" name="review_notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="action_taken" class="block text-gray-700 font-bold mb-2">Ação a Tomar</label>
                    <select id="action_taken" name="action_taken" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="none">Nenhuma</option>
                        <option value="coaching">Coaching</option>
                        <option value="retraining">Retreinamento</option>
                        <option value="suspension">Suspensão</option>
                    </select>
                </div>

                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Resolver Reclamação
                </button>
            </form>

            <form action="{{ route('admin.complaints.dismiss', $complaint) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="dismiss_notes" class="block text-gray-700 font-bold mb-2">Motivo da Rejeição</label>
                    <textarea id="dismiss_notes" name="review_notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <button type="submit" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Tem certeza que deseja descartar essa reclamação?');">
                    Descartar Reclamação
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
