@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Análise de Transferências</h1>
        <a href="{{ route('admin.transfers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Voltar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tempo Médio de Resolução</h2>
            <div class="text-4xl font-bold text-blue-600 mb-2">
                {{ $averageResolutionTime ?? 0 }} min
            </div>
            <p class="text-gray-600">Entre solicitação e conclusão</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Transferências por Motivo</h2>
            <div class="space-y-3">
                @forelse ($byReason as $item)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900">{{ $item->reason ?? 'Sem motivo' }}</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                            {{ $item->count }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">Nenhum dado disponível</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Transferências por Atendente (Destino)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atendente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $total = $byAgent->sum('count');
                        @endphp
                        @forelse ($byAgent as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item->toUser?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $item->count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $total > 0 ? ($item->count / $total) * 100 : 0 }}%"></div>
                                        </div>
                                        {{ $total > 0 ? round(($item->count / $total) * 100) : 0 }}%
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum dado disponível
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
