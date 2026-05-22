@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
    'class' => '',
])

@php
    $bgClasses = match($type) {
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800',
    };

    $iconName = match($type) {
        'success' => 'check_circle',
        'warning' => 'warning',
        'error' => 'error',
        'info' => 'info',
        default => 'info',
    };
@endphp

<div class="flex gap-3 p-4 border rounded-lg {{ $bgClasses }} {{ $class }}" role="alert">
    <span class="material-symbols-outlined flex-shrink-0">{{ $iconName }}</span>
    <div class="flex-1">
        @if($title)
            <h4 class="font-semibold mb-1">{{ $title }}</h4>
        @endif
        {{ $slot }}
    </div>
    @if($dismissible)
        <button onclick="this.parentElement.remove()" class="flex-shrink-0 hover:opacity-75 transition-opacity">
            <span class="material-symbols-outlined text-base">close</span>
        </button>
    @endif
</div>
