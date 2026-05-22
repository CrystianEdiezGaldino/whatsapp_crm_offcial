@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
])

<div class="bg-white border border-outline-variant rounded-xl p-5 {{ $class }}">
    @if($title)
        <div class="mb-4">
            <h3 class="text-lg font-bold text-on-surface">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-on-surface-variant mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div>{{ $slot }}</div>
</div>
