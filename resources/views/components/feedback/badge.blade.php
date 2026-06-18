@props([
    'type' => 'info',
    'size' => 'md',
    'class' => '',
])

@php
    $typeClasses = match($type) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        'info' => 'badge-info',
        'secondary' => 'bg-gray-100 text-gray-600',
        default => 'badge-info',
    };

    $sizeClasses = match($size) {
        'sm' => 'text-[10px] px-2 py-0.5',
        'lg' => 'text-sm px-3 py-1',
        default => '',
    };
@endphp

<span class="badge {{ $typeClasses }} {{ $sizeClasses }} {{ $class }}">
    {{ $slot }}
</span>
