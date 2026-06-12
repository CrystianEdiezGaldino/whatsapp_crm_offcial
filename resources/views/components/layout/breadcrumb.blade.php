@props([
    'items' => [],
    'class' => '',
])

<nav class="flex items-center gap-2 {{ $class }}">
    @foreach($items as $index => $item)
        @if($index > 0)
            <span class="text-gray-600">/</span>
        @endif
        @if(isset($item['href']))
            <a href="{{ $item['href'] }}" class="text-secondary hover:underline text-sm">{{ $item['label'] }}</a>
        @else
            <span class="text-on-surface text-sm">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
