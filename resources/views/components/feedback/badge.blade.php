@props([
    'type' => 'info',
    'size' => 'md',
    'class' => '',
])

@php
    $bgClasses = match($type) {
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'error' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        'secondary' => 'bg-surface-container text-on-surface-variant',
        default => 'bg-blue-100 text-blue-800',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };
@endphp

<span class="inline-flex items-center {{ $bgClasses }} {{ $sizeClasses }} rounded-full font-semibold {{ $class }}">
    {{ $slot }}
</span>
