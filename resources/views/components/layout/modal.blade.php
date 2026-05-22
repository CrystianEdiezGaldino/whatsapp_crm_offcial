@props([
    'id',
    'title' => null,
    'size' => 'md',
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        default => 'max-w-md',
    };
@endphp

<div id="{{ $id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg {{ $sizeClasses }} w-full mx-4 p-6 {{ $class }}">
        @if($title)
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-on-surface">{{ $title }}</h3>
                <button onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        @endif
        <div>{{ $slot }}</div>
    </div>
</div>
