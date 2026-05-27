@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tokens WhatsApp</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Status Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Status Atual</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="text-gray-600 font-semibold">Status do Token</label>
                <p class="text-lg">
                    @if($status['status'] === 'valid')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">✅ Válido</span>
                    @elseif($status['status'] === 'expiring_soon')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">⚠️ Expirando em breve</span>
                    @elseif($status['status'] === 'expired')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">❌ Expirado</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">❓ Não armazenado</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Expiração</label>
                <p class="text-gray-900">{{ $status['time_until_expiration'] ?? 'Não definida' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Última Renovação</label>
                <p class="text-gray-900">{{ $status['last_refreshed_at']?->format('d/m/Y H:i') ?? 'Nunca' }}</p>
            </div>
            <div>
                <label class="text-gray-600 font-semibold">Tentativas de Renovação</label>
                <p class="text-gray-900">{{ $status['refresh_attempts'] ?? 0 }}/3</p>
            </div>
        </div>

        <div class="flex gap-2">
            <form action="{{ route('admin.whatsapp.token.refresh') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    🔄 Renovar Token Agora
                </button>
            </form>
            <form action="{{ route('admin.whatsapp.token.sync-env') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    📥 Sincronizar do .env
                </button>
            </form>
        </div>
    </div>

    <!-- Manual Token Input -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Adicionar Token Manualmente</h2>

        <form action="{{ route('admin.whatsapp.token.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="token" class="block text-gray-700 font-bold mb-2">Token de Acesso</label>
                <textarea id="token" name="token" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cole o token de acesso da Meta aqui..." required></textarea>
            </div>

            <div class="mb-4">
                <label for="expires_in" class="block text-gray-700 font-bold mb-2">Tempo de Expiração (segundos, opcional)</label>
                <input type="number" id="expires_in" name="expires_in" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: 5184000 (60 dias)">
                <p class="text-xs text-gray-600 mt-1">Se deixar vazio, o token não terá data de expiração registrada</p>
            </div>

            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                💾 Armazenar Token
            </button>
        </form>
    </div>

    <!-- Histórico de Tokens -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Histórico de Tokens</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expira em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renovação</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tokens as $token)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ ucfirst($token->token_type) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $token->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $token->expires_at?->format('d/m/Y H:i') ?? 'Sem expiração' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($token->isExpired())
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Expirado</span>
                                @elseif($token->isExpiringSoon())
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Expirando</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Válido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $token->last_refreshed_at?->format('d/m/Y H:i') ?? 'Nunca' }}
                                <span class="text-xs text-gray-500">({{ $token->refresh_attempts }} tentativas)</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Nenhum token armazenado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Instruções -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-bold text-blue-900 mb-2">📋 Como obter um novo token da Meta</h3>
        <ol class="list-decimal list-inside text-blue-800 space-y-2">
            <li>Acesse <a href="https://developers.facebook.com" target="_blank" class="font-bold underline">developers.facebook.com</a></li>
            <li>Vá para seu App > Configurações > Básico</li>
            <li>Copie o Token de Acesso do App</li>
            <li>Cole no campo acima e clique em "Armazenar Token"</li>
            <li>O sistema renovará automaticamente quando estiver expirando (24h antes)</li>
        </ol>
    </div>
</div>
@endsection
