@props([
    'items' => [],
    'class' => '',
])

<div class="space-y-2 {{ $class }}">
    @foreach($items as $index => $item)
        @php $isActive = $item['active'] ?? false; @endphp
        <div class="border border-gray-200 rounded-lg">
            <button
                onclick="toggleAccordion({{ $index }})"
                class="w-full px-4 py-3 flex justify-between items-center text-on-surface font-semibold hover:bg-gray-100 transition-colors"
            >
                <span>{{ $item['title'] }}</span>
                <span class="material-symbols-outlined transition-transform" id="icon-{{ $index }}" style="transform: rotate({{ $isActive ? 180 : 0 }}deg)">expand_more</span>
            </button>
            <div id="content-{{ $index }}" class="@if(!$isActive) hidden @endif px-4 py-3 bg-gray-100 text-gray-600 border-t border-gray-200">
                {{ $item['content'] }}
            </div>
        </div>
    @endforeach
</div>

<script>
function toggleAccordion(index) {
    const content = document.getElementById(`content-${index}`);
    const icon = document.getElementById(`icon-${index}`);
    const isHidden = content.classList.contains('hidden');
    content.classList.toggle('hidden', !isHidden);
    icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
}
</script>
