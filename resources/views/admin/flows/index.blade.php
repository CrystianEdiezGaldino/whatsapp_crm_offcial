@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gerenciar Fluxos</h1>
        <a href="{{ route('admin.flows.create') }}" class="btn-nm-primary">
            + Novo Fluxo
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert-neumorphic success mb-4">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert-neumorphic error mb-4">
            {{ $message }}
        </div>
    @endif

    @if ($flows->isEmpty())
        <div class="card-nm p-8 text-center">
            <p class="text-gray-500 mb-4">Nenhum fluxo criado ainda.</p>
            <a href="{{ route('admin.flows.create') }}" class="text-[#1DA85A] hover:text-[#148A52] font-semibold">Criar primeiro fluxo</a>
        </div>
    @else
        <div class="card-nm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nome</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tipo</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trigger</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($flows as $flow)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <strong>{{ $flow->name }}</strong>
                            @if ($flow->created_by)
                                <div class="text-xs text-gray-500">por {{ $flow->createdBy->name ?? 'Sistema' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                {{ $flow->type === 'primary' ? 'Principal' : 'Secundário' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $flow->trigger_type === 'on_new_conversation' ? 'Nova Conversa' : ($flow->trigger_type === 'on_command' ? 'Comando' : 'Manual') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($flow->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Ativo</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.flows.edit', $flow) }}" class="text-[#1DA85A] hover:text-[#148A52] font-semibold transition-colors">Editar</a>
                                <form action="{{ route('admin.flows.toggle', $flow) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="text-amber-600 hover:text-amber-700 font-semibold transition-colors">
                                        {{ $flow->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.flows.executions', $flow) }}" class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">Histórico</a>
                                <form action="{{ route('admin.flows.destroy', $flow) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold transition-colors">Deletar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $flows->links() }}
        </div>
    @endif
</div>
@endsection
