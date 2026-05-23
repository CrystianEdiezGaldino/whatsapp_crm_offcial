@extends('layouts.app')

@section('title', 'Health Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">🟢 SYSTEM HEALTH</h1>
        <p class="text-gray-600 mt-2">Real-time monitoring of webhooks, rate limits, and message processing</p>
    </div>

    <!-- Status Alert -->
    @if($webhookHealth['alert'])
        <div class="mb-8 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <span class="text-2xl">⚠️</span>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        {{ $webhookHealth['alert'] }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Webhooks Card -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    📨 WEBHOOKS (últimas 24h)
                </h2>
            </div>
            <div class="px-6 py-6 space-y-4">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($webhookHealth['status'] === 'OK')
                            bg-green-100 text-green-800
                        @elseif($webhookHealth['status'] === 'WARNING')
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800
                        @endif
                    ">
                        @if($webhookHealth['status'] === 'OK')
                            🟢 OK
                        @elseif($webhookHealth['status'] === 'WARNING')
                            🟡 WARNING
                        @else
                            🔴 CRITICAL
                        @endif
                    </span>
                </div>

                <!-- Last webhook -->
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Last webhook:</span>
                    <span class="font-mono text-sm">
                        @if($webhookHealth['last_webhook_at'])
                            {{ $webhookHealth['last_webhook_at']->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </span>
                </div>

                <!-- Success rate -->
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Success rate:</span>
                    <div class="w-32">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($webhookHealth['success_rate'], 100) }}%"></div>
                            </div>
                            <span class="text-sm font-mono">{{ $webhookHealth['success_rate'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="pt-4 border-t border-gray-200 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-mono font-semibold">{{ $webhookHealth['total_received_24h'] }} webhooks</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Successful:</span>
                        <span class="font-mono text-green-600">{{ $webhookHealth['successful_24h'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Failed:</span>
                        <span class="font-mono text-red-600">{{ $webhookHealth['failed_24h'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Avg processing:</span>
                        <span class="font-mono">{{ $webhookHealth['avg_processing_time_ms'] }}ms</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limits Card -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    ⚡ RATE LIMITS (Current Usage)
                </h2>
            </div>
            <div class="px-6 py-6 space-y-4">
                @foreach($rateLimitStatus as $action => $status)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-600">
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </span>
                            <span class="text-sm font-mono text-gray-900">
                                {{ $status['current'] }}/{{ $status['limit'] }} ({{ $status['percentage'] }}%)
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="
                                h-2 rounded-full
                                @if($status['percentage'] >= 100)
                                    bg-red-500
                                @elseif($status['percentage'] >= 80)
                                    bg-yellow-500
                                @else
                                    bg-green-500
                                @endif
                            " style="width: {{ min($status['percentage'], 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Failed Messages Card -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                ❌ FAILED MESSAGES (Pending Retry)
            </h2>
        </div>
        <div class="px-6 py-6">
            @if($failedMessages > 0)
                <div class="space-y-2">
                    <p class="text-2xl font-bold text-red-600">{{ $failedMessages }} messages</p>
                    <p class="text-gray-600 text-sm">
                        These messages will be automatically retried with exponential backoff.
                    </p>
                    <p class="text-gray-600 text-sm">
                        Next automatic retry: {{ now()->addMinute()->format('H:i:s') }}
                    </p>
                </div>
            @else
                <div class="flex items-center text-green-600">
                    <span class="text-2xl mr-3">✅</span>
                    <span>All messages delivered successfully</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Webhooks Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                📋 RECENT WEBHOOKS (últimos 20)
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Processing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentWebhooks as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $log['created_at'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ucfirst($log['type']) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $log['phone'] ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($log['status'] === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Success
                                    </span>
                                @elseif($log['status'] === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ❌ Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        ⏳ {{ ucfirst($log['status']) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $log['processing_time_ms'] }}ms
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $log['error'] ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No webhooks received yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
