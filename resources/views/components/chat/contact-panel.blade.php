@props([
    'contact',
    'conversation',
])

<aside
    class="contact-panel design-scrollbar"
    data-contact-id="{{ $contact->id }}"
    data-conversation-id="{{ $conversation->id }}"
    data-notes-url="{{ route('contacts.notes.update', $contact) }}"
    data-tags-url="{{ route('tags.index') }}"
    data-conversation-tags-url="{{ route('conversations.tags.json', $conversation) }}"
    data-attach-tags-url="{{ route('conversations.tags.attach', $conversation) }}"
>
    <div class="contact-panel__head">
        <span class="contact-panel__title">Dados do contato</span>
    </div>

    <div class="contact-panel__identity">
        <x-common.contact-avatar :initials="$contact->initials" size="lg" />
        <h2 class="contact-panel__name">{{ $contact->name }}</h2>
        <p class="contact-panel__phone">{{ $contact->phone }}</p>
        @if($contact->email)
            <p class="contact-panel__email">{{ $contact->email }}</p>
        @endif
    </div>

    <div class="contact-panel__grid">
        <div class="contact-panel__field">
            <span class="contact-panel__label">WhatsApp</span>
            <span class="contact-panel__value">{{ $contact->phone }}</span>
        </div>
        <div class="contact-panel__field">
            <span class="contact-panel__label">Agente ativo</span>
            <span class="contact-panel__value">{{ $conversation->assignedUser?->name ?? '—' }}</span>
        </div>
        <div class="contact-panel__field">
            <span class="contact-panel__label">Última mensagem</span>
            <span class="contact-panel__value">{{ $conversation->last_message_at?->diffForHumans(short: true) ?? '—' }}</span>
        </div>
        <div class="contact-panel__field">
            <span class="contact-panel__label">Setor</span>
            <span class="contact-panel__value">{{ $conversation->sector?->name ?? 'Geral' }}</span>
        </div>
    </div>

    <div class="contact-panel__section">
        <div class="contact-panel__section-head">
            <span class="contact-panel__label">Notas</span>
            <span id="contactNotesStatus" class="contact-panel__save-status" aria-live="polite"></span>
        </div>
        <div class="flex gap-2 mb-2">
            <textarea
                id="contactNotes"
                class="contact-panel__notes-input flex-1"
                rows="4"
                maxlength="5000"
                placeholder="Anote informações importantes sobre este contato..."
            >{{ $contact->notes }}</textarea>
            <button type="button" id="improveContactNotesBtn" class="hover:text-secondary transition-colors" title="Melhorar com IA" onclick="openImproveContactNotesModal()">
                <span class="material-symbols-outlined text-xl">auto_awesome</span>
            </button>
        </div>
        <button type="button" id="saveContactNotes" class="contact-panel__save-btn">
            <span class="material-symbols-outlined text-[16px]">save</span>
            Salvar nota
        </button>
    </div>

    <div class="contact-panel__section">
        <div class="contact-panel__section-head">
            <span class="contact-panel__label">Etiquetas do atendimento</span>
            <button type="button" id="openTagsModalBtn" class="contact-panel__link">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Adicionar
            </button>
        </div>
        <div id="conversationTags" class="contact-panel__tag-list">
            @forelse($conversation->tags as $tag)
                <div class="contact-panel__tag-wrap" data-tag-id="{{ $tag->id }}">
                    <span class="contact-panel__tag-pill" style="--tag-color: {{ $tag->color }}">
                        <span class="contact-panel__tag-dot"></span>
                        <span class="contact-panel__tag-name">{{ $tag->name }}</span>
                    </span>
                    <button
                        type="button"
                        class="contact-panel__tag-remove"
                        title="Remover etiqueta"
                        data-tag-id="{{ $tag->id }}"
                        aria-label="Remover {{ $tag->name }}"
                    >
                        <span class="material-symbols-outlined text-[14px]">close</span>
                    </button>
                </div>
            @empty
                <span class="contact-panel__tag-empty">Nenhuma etiqueta</span>
            @endforelse
        </div>
    </div>

    <div id="tagsModal" class="tags-modal hidden" role="dialog" aria-modal="true" aria-labelledby="tagsModalTitle">
        <div class="tags-modal__backdrop" data-close-tags-modal></div>
        <div class="tags-modal__card">
            <div class="tags-modal__header">
                <div>
                    <h3 id="tagsModalTitle" class="tags-modal__title">Etiquetas do atendimento</h3>
                    <p class="tags-modal__subtitle">Toque para marcar ou desmarcar</p>
                </div>
                <button type="button" class="tags-modal__close" data-close-tags-modal aria-label="Fechar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="tagsContainer" class="tags-modal__body custom-scrollbar"></div>
            <div class="tags-modal__footer">
                <button type="button" class="tags-modal__done" data-close-tags-modal>Concluir</button>
            </div>
        </div>
    </div>

    <!-- Improve Contact Notes Modal -->
    <div id="improveContactNotesModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="glass-modal rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined">auto_awesome</span>
                        Melhorar Nota com IA
                    </h2>
                    <button onclick="closeImproveContactNotesModal()" class="material-symbols-outlined text-gray-600 hover:text-error transition-colors">close</button>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Tipo de Melhoria:</label>
                    <select id="improveContactNotesTypeSelect" class="w-full border border-gray-200/50 rounded-lg p-3 text-sm focus:ring-2 focus:ring-secondary-container/50 focus:border-secondary transition-all" onchange="refreshImproveContactNotes()">
                        <option value="grammar">✓ Corrigir Ortografia/Gramática</option>
                        <option value="professional">👔 Reformular para Tom Profissional</option>
                        <option value="both">🎯 Ambos (Gramática + Profissional)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Nota Original:</label>
                    <div id="improveContactNotesOriginalText" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar"></div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-2">Versão Melhorada:</label>
                    <div id="improveContactNotesLoadingSpinner" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 min-h-[80px] max-h-[120px] flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-6 h-6 border-2 border-secondary border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-xs text-gray-600">Processando com IA...</p>
                        </div>
                    </div>
                    <div id="improveContactNotesImprovedText" class="hidden w-full bg-secondary/10 border border-secondary/30 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar whitespace-pre-wrap"></div>
                </div>

                <div id="improveContactNotesErrorMessage" class="hidden bg-error/20 border border-error/30 rounded-lg p-3 text-sm text-error"></div>
            </div>

            <div class="p-6 border-t border-gray-200/50 flex gap-3 justify-end">
                <button onclick="closeImproveContactNotesModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-on-surface border border-gray-200/50 hover:bg-gray-100/50 transition-all">
                    Cancelar
                </button>
                <button id="improveContactNotesUseBtn" onclick="applyImprovedContactNotes()" disabled class="px-4 py-2 rounded-lg text-sm font-semibold bg-secondary text-on-secondary hover:shadow-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    Usar
                </button>
            </div>
        </div>
    </div>

    {{ $slot }}
</aside>
