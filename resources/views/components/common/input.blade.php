{{-- Input Component --}}
@props([
    'name',
    'type' => 'text',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'class' => '',
])

@php
    $value = $value ?? old($name);
    $borderClass = $error ? 'border-error' : 'border-gray-200';
@endphp

<div class="space-y-2">
    @if($label)
        <label class="block text-xs font-semibold text-gray-600 uppercase">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        class="w-full border {{ $borderClass }} rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $class }}"
        @if($value) value="{{ $value }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
    >

    @if($error)
        <span class="text-xs text-error">{{ $error }}</span>
    @endif
</div>
