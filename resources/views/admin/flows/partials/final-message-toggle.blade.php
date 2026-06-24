@php
    $enabled = $enabled ?? false;
@endphp
<div class="flex items-center justify-between mb-1.5">
    <label class="text-xs font-bold text-gray-500">Mensagem Final</label>
    <button
        type="button"
        id="finalMsgToggleBtn"
        class="inline-flex items-center gap-2 border-0 bg-transparent p-0 cursor-pointer"
        onclick="toggleFinalMessage()"
        aria-pressed="{{ $enabled ? 'true' : 'false' }}"
        aria-label="Habilitar mensagem final"
    >
        <span class="text-[11px] text-gray-400 font-medium select-none">Habilitar</span>
        <span id="finalMsgToggle" class="toggle-switch pointer-events-none {{ $enabled ? 'active' : '' }}" aria-hidden="true"></span>
    </button>
</div>
<div id="finalMsgContainer" class="{{ $enabled ? '' : 'hidden' }}">
    <div class="relative">
        <textarea
            name="config[final_message]"
            id="finalMsgTextarea"
            class="textarea-primary message-textarea"
            rows="3"
            placeholder="Use {nome}, {telefone}, {setor}, {agente} para variáveis..."
        >{{ $finalMessage ?? '' }}</textarea>
        <button type="button" class="btn-insert-variable absolute top-2 right-2 bg-[#F0F2F7] hover:bg-[#E8EAF0] px-2.5 py-1 rounded-[8px] text-[11px] font-bold text-gray-500 transition-colors">
            + Variável
        </button>
    </div>
</div>
@error('config.final_message')<span class="text-error text-xs mt-1 block">{{ $message }}</span>@enderror
