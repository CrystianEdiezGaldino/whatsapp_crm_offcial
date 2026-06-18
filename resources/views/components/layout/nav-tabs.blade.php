@props([
    'items' => [],
])

<nav class="nav-tabs" aria-label="Navegação da página">
    @foreach($items as $item)
        <a
            href="{{ $item['href'] ?? '#' }}"
            @class([
                'nav-tabs__item',
                'nav-tabs__item--active' => $item['active'] ?? false,
            ])
        >
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
