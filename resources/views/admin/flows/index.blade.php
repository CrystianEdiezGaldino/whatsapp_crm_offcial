@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gerenciar Fluxos</h1>
        <a href="{{ route('admin.flows.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Novo Fluxo
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $message }}
        </div>
    @endif

    @if ($flows->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500 mb-4">Nenhum fluxo criado ainda.</p>
            <a href="{{ route('admin.flows.create') }}" class="text-blue-600 hover:text-blue-800">Criar primeiro fluxo</a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nome</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tipo</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trigger</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flows as $flow)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <strong>{{ $flow->name }}</strong>
                            @if ($flow->created_by)
                                <div class="text-xs text-gray-500">por {{ $flow->createdBy->name ?? 'Sistema' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                {{ $flow->type === 'primary' ? 'Principal' : 'Secundário' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $flow->trigger_type === 'on_new_conversation' ? 'Nova Conversa' : ($flow->trigger_type === 'on_command' ? 'Comando' : 'Manual') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($flow->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Ativo</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.flows.edit', $flow) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                                <form action="{{ route('admin.flows.toggle', $flow) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="text-amber-600 hover:text-amber-800">
                                        {{ $flow->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.flows.executions', $flow) }}" class="text-green-600 hover:text-green-800">Histórico</a>
                                <form action="{{ route('admin.flows.destroy', $flow) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Deletar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $flows->links() }}
        </div>
    @endif
</div>
@endsection
