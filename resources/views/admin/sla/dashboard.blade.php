@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Monitor SLA</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 font-semibold">Total em Atendimento</div>
            <div class="text-3xl font-bold text-blue-600" id="totalOpen">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 font-semibold">Primeira Resposta em Risco</div>
            <div class="text-3xl font-bold text-orange-600" id="firstResponseAtRisk">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 font-semibold">Resolução em Risco</div>
            <div class="text-3xl font-bold text-orange-600" id="resolutionAtRisk">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 font-semibold">Breaches Encontrados</div>
            <div class="text-3xl font-bold text-red-600" id="breaches">0</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Status de SLA por Setor</h2>
            <div id="sectorsList" class="space-y-3">
                <p class="text-gray-500">Carregando...</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Conversas Críticas</h2>
            <div id="criticalList" class="space-y-3">
                <p class="text-gray-500">Nenhuma conversa crítica</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Conversas Monitoradas</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Setor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">1ª Resposta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resolução</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tempo na Fila</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="conversationsList">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Carregando conversas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const refreshInterval = 30000; // 30 seconds

    async function loadSLAData() {
        try {
            const response = await fetch('{{ route("admin.sla.metrics") }}');
            const data = await response.json();

            // Update metrics
            document.getElementById('totalOpen').textContent = data.total_open;
            document.getElementById('firstResponseAtRisk').textContent = data.first_response_at_risk;
            document.getElementById('resolutionAtRisk').textContent = data.resolution_at_risk;
            document.getElementById('breaches').textContent = data.breaches;

            // Update sectors list
            const sectorsList = document.getElementById('sectorsList');
            if (data.by_sector && data.by_sector.length > 0) {
                sectorsList.innerHTML = data.by_sector.map(sector => `
                    <div class="border rounded p-3">
                        <div class="font-semibold text-gray-900">${sector.name}</div>
                        <div class="text-sm text-gray-600">
                            Conversas: ${sector.conversations_count} |
                            Breaches: ${sector.breaches}
                        </div>
                    </div>
                `).join('');
            }

            // Update conversations list
            const conversationsList = document.getElementById('conversationsList');
            if (data.conversations && data.conversations.length > 0) {
                conversationsList.innerHTML = data.conversations.map(conv => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${conv.contact}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            ${conv.sector}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                style="background-color: ${conv.first_response_breached ? '#FEE2E2' : '#DBEAFE'}; color: ${conv.first_response_breached ? '#991B1B' : '#1E40AF'};">
                                ${conv.first_response_remaining || 'Expirado'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                style="background-color: ${conv.resolution_breached ? '#FEE2E2' : '#DBEAFE'}; color: ${conv.resolution_breached ? '#991B1B' : '#1E40AF'};">
                                ${conv.resolution_remaining || 'Expirado'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                style="background-color: ${conv.priority === 'urgent' ? '#FEE2E2' : '#F3E8FF'}; color: ${conv.priority === 'urgent' ? '#991B1B' : '#6B21A8'};">
                                ${conv.priority}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            ${conv.wait_time || 'N/A'}
                        </td>
                    </tr>
                `).join('');
            } else {
                conversationsList.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhuma conversa</td></tr>';
            }
        } catch (error) {
            console.error('Erro ao carregar dados SLA:', error);
        }
    }

    // Load initial data
    loadSLAData();

    // Refresh every 30 seconds
    setInterval(loadSLAData, refreshInterval);
</script>
@endsection
