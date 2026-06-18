@props([
    'name' => 'search',
    'placeholder' => 'Buscar...',
    'value' => null,
    'icon' => 'search',
    'class' => '',
])

@php
    $value = $value ?? old($name);
@endphp

<div class="search-input {{ $class }}">
    <span class="material-symbols-outlined search-input__icon">{{ $icon }}</span>
    <input
        type="search"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="search-input__field"
        {{ $attributes->except(['class', 'name', 'placeholder', 'value', 'icon']) }}
    >
</div>
