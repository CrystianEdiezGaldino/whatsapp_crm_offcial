@props([
    'initials',
    'size' => 'md',
    'variant' => 'default',
])

@php
    $sizeClass = match($size) {
        'lg' => 'contact-avatar--lg',
        'sm' => 'contact-avatar--sm',
        default => '',
    };
    $variantClass = match($variant) {
        'pending' => 'contact-avatar--pending',
        'active' => 'contact-avatar--active',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => trim("contact-avatar $sizeClass $variantClass")]) }}>
    {{ $initials }}
</div>
