{{-- Select Component --}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'error' => null,
    'required' => false,
    'class' => '',
])

@php
    $value = $value ?? old($name);
    $borderClass = $error ? 'border-error' : 'border-outline-variant';
@endphp

<div class="space-y-2">
    @if($label)
        <label class="block text-xs font-semibold text-on-surface-variant uppercase">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        class="w-full border {{ $borderClass }} rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $class }}"
        @if($required) required @endif
    >
        <option value="">-- Selecionar --</option>
        @foreach($options as $key => $label)
            <option value="{{ $key }}" @selected($value == $key)>{{ $label }}</option>
        @endforeach
    </select>

    @if($error)
        <span class="text-xs text-error">{{ $error }}</span>
    @endif
</div>
