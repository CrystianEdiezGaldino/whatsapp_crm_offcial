@props([
    'tabs' => [],
])

<div class="chat-queue-tabs">
    @foreach($tabs as $tab)
        <a
            href="{{ $tab['href'] }}"
            @class([
                'chat-queue-tabs__item',
                'chat-queue-tabs__item--active' => $tab['active'] ?? false,
            ])
        >
            {{ $tab['label'] }}@if(isset($tab['count']))<span class="chat-queue-tabs__sep"> &middot; </span><span data-tab-count="{{ $tab['countKey'] ?? '' }}">{{ $tab['count'] }}</span>@endif
        </a>
    @endforeach
</div>

