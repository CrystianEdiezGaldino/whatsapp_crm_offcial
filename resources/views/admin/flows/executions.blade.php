@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Histórico de Execuções</h1>
            <p class="text-gray-500 mt-1">Fluxo: <strong>{{ $flow->name }}</strong></p>
        </div>
        <a href="{{ route('admin.flows.index') }}" class="text-blue-600 hover:text-blue-800">← Voltar</a>
    </div>

    @if ($executions->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Nenhuma execução registrada ainda.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Contato</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Opção Escolhida</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Setor Atribuído</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($executions as $execution)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <strong>{{ $execution->conversation->contact->name ?? 'Desconhecido' }}</strong>
                            <div class="text-xs text-gray-500">{{ $execution->conversation->contact->phone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if ($execution->client_choice)
                                {{ $execution->client_choice }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if ($execution->resultSector)
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $execution->resultSector->name }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($execution->status === 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Completo</span>
                            @elseif ($execution->status === 'in_progress')
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded text-xs">Em Progresso</span>
                            @elseif ($execution->status === 'failed')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Erro</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">{{ $execution->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $execution->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $executions->links() }}
        </div>
    @endif
</div>
@endsection
