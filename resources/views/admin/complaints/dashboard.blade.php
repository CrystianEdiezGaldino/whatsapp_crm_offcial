@extends('layouts.app')

@section('title', 'Dashboard de Reclamações')

@section('content')
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="h-16 px-6 sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-outline-variant flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Reclamações</h1>
            <p class="text-xs text-on-surface-variant">Painel de qualidade e atendimento</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.complaints.index') }}" class="px-4 py-2 border border-outline-variant rounded-xl text-sm font-semibold hover:bg-surface-container transition-colors">
                Ver lista
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-outline-variant p-5 shadow-sm border-l-4 border-l-red-500">
                <p class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-wide">Abertas</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $openComplaints->total() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-outline-variant p-5 shadow-sm border-l-4 border-l-orange-500">
                <p class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-wide">Alta severidade</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ $highSeverity }}</p>
            </div>
            <div class="bg-white rounded-xl border border-outline-variant p-5 shadow-sm border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-wide">Resolvidas recentes</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $recentlyResolved->count() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5">
                <h2 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500 text-lg">report</span>
                    Reclamações abertas
                </h2>
                <div class="space-y-3">
                    @forelse ($openComplaints as $complaint)
                    <div class="border border-outline-variant/60 rounded-xl p-3 hover:border-secondary/50 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-on-surface truncate">
                                    {{ $complaint->conversation?->contact?->name ?? 'Contato' }}
                                </p>
                                <p class="text-xs text-on-surface-variant mt-0.5">
                                    {{ $complaint->responsible?->name ?? '—' }} · Nota {{ $complaint->rating }}/5
                                </p>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md
                                {{ $complaint->severity === 'high' ? 'bg-red-100 text-red-700' : ($complaint->severity === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($complaint->severity) }}
                            </span>
                        </div>
                        <a href="{{ route('admin.complaints.review', $complaint) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary mt-2 hover:underline">
                            Revisar <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                    @empty
                    <p class="text-sm text-on-surface-variant text-center py-6">Nenhuma reclamação aberta</p>
                    @endforelse
                </div>
                @if($openComplaints->hasPages())
                <div class="mt-4">{{ $openComplaints->links() }}</div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5">
                <h2 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-lg">groups</span>
                    Por atendente
                </h2>
                <div class="space-y-2">
                    @forelse ($byResponsible as $item)
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg bg-surface-container-low">
                        <span class="text-sm text-on-surface">{{ $item->responsible?->name ?? 'N/A' }}</span>
                        <span class="bg-red-100 text-red-800 px-2.5 py-0.5 rounded-full text-xs font-bold">{{ $item->count }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-on-surface-variant text-center py-6">Nenhum dado</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="text-sm font-bold text-on-surface">Resolvidas recentemente</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant text-xs uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Contato</th>
                            <th class="px-5 py-3 text-left font-semibold">Ação</th>
                            <th class="px-5 py-3 text-left font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @forelse ($recentlyResolved as $complaint)
                        <tr class="hover:bg-surface-container-low/50">
                            <td class="px-5 py-3 font-medium text-on-surface">
                                {{ $complaint->conversation?->contact?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ ucfirst($complaint->action_taken ?? '—') }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $complaint->reviewed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-on-surface-variant">Nenhuma resolvida recentemente</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
