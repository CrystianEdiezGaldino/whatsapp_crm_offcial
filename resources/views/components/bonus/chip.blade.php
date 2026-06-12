@props([
    'type' => 'default',
    'deletable' => false,
    'onDelete' => null,
    'class' => '',
])

@php
    $bgClasses = match($type) {
        'primary' => 'bg-secondary text-on-secondary',
        'secondary' => 'bg-gray-100 text-on-surface',
        'error' => 'bg-error text-on-error',
        'default' => 'bg-gray-100-low text-on-surface',
        default => 'bg-gray-100-low text-on-surface',
    };
@endphp

<div class="inline-flex items-center gap-2 {{ $bgClasses }} px-3 py-1.5 rounded-full text-sm font-medium {{ $class }}">
    {{ $slot }}
    @if($deletable)
        <button
            type="button"
            onclick="{{ $onDelete ?? 'this.parentElement.remove()' }}"
            class="ml-1 hover:opacity-75 transition-opacity flex-shrink-0"
        >
            <span class="material-symbols-outlined text-base">close</span>
        </button>
    @endif
</div>
