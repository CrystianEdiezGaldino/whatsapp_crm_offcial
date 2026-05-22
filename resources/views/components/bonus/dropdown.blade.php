@props([
    'id',
    'label' => 'Menu',
    'items' => [],
    'align' => 'left',
    'class' => '',
])

@php
    $alignClasses = $align === 'right' ? 'right-0' : 'left-0';
@endphp

<div class="relative inline-block {{ $class }}">
    <button
        onclick="toggleDropdown('{{ $id }}')"
        class="px-3 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors text-sm font-medium flex items-center gap-2"
    >
        {{ $label }}
        <span class="material-symbols-outlined text-base">expand_more</span>
    </button>

    <div id="{{ $id }}" class="hidden absolute {{ $alignClasses }} mt-2 w-48 bg-white border border-outline-variant rounded-lg shadow-lg z-40">
        @foreach($items as $item)
            @if(isset($item['divider']) && $item['divider'])
                <div class="border-t border-outline-variant"></div>
            @else
                @if(isset($item['href']))
                    <a href="{{ $item['href'] }}" class="block px-4 py-2 hover:bg-surface-container text-on-surface text-sm transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <button type="button" onclick="{{ $item['onclick'] ?? '' }}" class="w-full text-left px-4 py-2 hover:bg-surface-container text-on-surface text-sm transition-colors">
                        {{ $item['label'] }}
                    </button>
                @endif
            @endif
        @endforeach
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('hidden');
    document.addEventListener('click', function(e) {
        if (!e.target.closest(`#${id}`) && !e.target.closest(`button[onclick*="toggleDropdown('${id}')"]`)) {
            dropdown.classList.add('hidden');
        }
    }, { once: true });
}
</script>
