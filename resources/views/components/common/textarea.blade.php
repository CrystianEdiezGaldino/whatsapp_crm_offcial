{{-- Textarea Component --}}
@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'rows' => 4,
    'error' => null,
    'maxlength' => null,
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

    <textarea
        name="{{ $name }}"
        rows="{{ $rows }}"
        class="w-full border {{ $borderClass }} rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary {{ $class }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($required) required @endif
    >{{ $value }}</textarea>

    <div class="flex justify-between items-center">
        @if($error)
            <span class="text-xs text-error">{{ $error }}</span>
        @else
            <span></span>
        @endif

        @if($maxlength)
            <span class="text-xs text-gray-600" id="char-count-{{ $name }}">
                <span id="current-{{ $name }}">{{ strlen($value) }}</span> / {{ $maxlength }}
            </span>
        @endif
    </div>
</div>

@if($maxlength)
    <script>
        document.querySelector('textarea[name="{{ $name }}"]')?.addEventListener('input', function() {
            document.getElementById('current-{{ $name }}').textContent = this.value.length;
        });
    </script>
@endif
