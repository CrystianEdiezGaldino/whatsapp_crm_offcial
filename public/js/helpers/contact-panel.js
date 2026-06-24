/**
 * Painel do contato: notas e etiquetas do atendimento.
 */
(function (global) {
    const CATEGORY_LABELS = {
        priority: 'Prioridade',
        status: 'Status',
        outcome: 'Resultado',
        custom: 'Tipo de atendimento',
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function tagIdSet(ids) {
        return new Set((ids || []).map((id) => Number(id)));
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    class ContactPanelHelper {
        constructor(panel) {
            this.panel = panel;
            this.conversationId = Number(panel.dataset.conversationId);
            this.notesUrl = panel.dataset.notesUrl;
            this.tagsUrl = panel.dataset.tagsUrl;
            this.conversationTagsUrl = panel.dataset.conversationTagsUrl;
            this.attachTagsUrl = panel.dataset.attachTagsUrl;
            this.sectorsUrl = panel.dataset.sectorsUrl;
            this.conversationSectorUrl = panel.dataset.conversationSectorUrl;
            this.updateSectorUrl = panel.dataset.updateSectorUrl;
            this.selectedTagIds = new Set();
            this.selectedSectorId = null;
            this._savingNotes = false;

            this.notesEl = panel.querySelector('#contactNotes');
            this.notesStatusEl = panel.querySelector('#contactNotesStatus');
            this.notesBoxEl = panel.querySelector('#contactNotesBox');
            this.notesCounterEl = panel.querySelector('#contactNotesCounter');
            this.notesPreviewEl = panel.querySelector('#contactNotesPreview');
            this.notesStateEl = panel.querySelector('#contactNotesState');
            this.saveBtnEl = panel.querySelector('#saveContactNotes');
            this.saveBtnLabelEl = panel.querySelector('#saveContactNotesLabel');
            this.improveBtnEl = panel.querySelector('#improveContactNotesBtn');
            this._savedNotes = this.notesEl?.value ?? '';
            this.tagsListEl = panel.querySelector('#conversationTags');
            this.sectorEl = panel.querySelector('#conversationSector');
            this.modalEl = panel.querySelector('#tagsModal');
            this.tagsContainerEl = panel.querySelector('#tagsContainer');
            this.sectorModalEl = panel.querySelector('#sectorModal');
            this.sectorsContainerEl = panel.querySelector('#sectorsContainer');

            const initialSector = this.sectorEl?.querySelector('[data-sector-id]');
            this.selectedSectorId = initialSector?.dataset.sectorId
                ? Number(initialSector.dataset.sectorId)
                : null;

            this.bindEvents();
        }

        bindEvents() {
            this.panel.querySelector('#saveContactNotes')?.addEventListener('click', () => this.saveNotes());
            this.panel.querySelector('#openTagsModalBtn')?.addEventListener('click', () => this.openTagsModal());
            this.panel.querySelector('#openSectorModalBtn')?.addEventListener('click', () => this.openSectorModal());

            this.modalEl?.querySelectorAll('[data-close-tags-modal]').forEach((el) => {
                el.addEventListener('click', () => this.closeTagsModal());
            });

            this.sectorModalEl?.querySelectorAll('[data-close-sector-modal]').forEach((el) => {
                el.addEventListener('click', () => this.closeSectorModal());
            });

            this.tagsListEl?.addEventListener('click', (e) => {
                const btn = e.target.closest('.contact-panel__tag-remove');
                if (!btn) return;
                this.removeTag(Number(btn.dataset.tagId));
            });

            this.notesEl?.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    this.saveNotes();
                }
            });

            this.notesEl?.addEventListener('input', () => this.syncNotesUi());
            this.notesEl?.addEventListener('focus', () => {
                this.notesBoxEl?.classList.add('contact-panel__notes-box--focus');
                this.syncNotesUi();
            });
            this.notesEl?.addEventListener('blur', () => {
                this.notesBoxEl?.classList.remove('contact-panel__notes-box--focus');
                this.syncNotesUi();
            });

            this.syncNotesUi();
        }

        syncNotesUi() {
            const value = this.notesEl?.value ?? '';
            const trimmed = value.trim();
            const dirty = value !== this._savedNotes;
            const max = Number(this.notesEl?.maxLength) || 5000;

            this.notesBoxEl?.classList.toggle('contact-panel__notes-box--filled', trimmed.length > 0);
            this.notesBoxEl?.classList.toggle('contact-panel__notes-box--open', trimmed.length === 0);
            this.notesBoxEl?.classList.toggle('contact-panel__notes-box--dirty', dirty);
            this.notesPreviewEl?.classList.toggle('hidden', trimmed.length === 0 || dirty);

            if (this.notesStateEl) {
                const isFocus = this.notesBoxEl?.classList.contains('contact-panel__notes-box--focus');
                this.notesStateEl.textContent = isFocus
                    ? 'Editando'
                    : (trimmed.length > 0 ? 'Preenchido' : 'Aberto');
                this.notesStateEl.classList.toggle('contact-panel__notes-state--filled', trimmed.length > 0 && !isFocus);
                this.notesStateEl.classList.toggle('contact-panel__notes-state--open', trimmed.length === 0 && !isFocus);
                this.notesStateEl.classList.toggle('contact-panel__notes-state--focus', !!isFocus);
            }

            if (this.notesCounterEl) {
                this.notesCounterEl.textContent = `${value.length}/${max}`;
            }

            if (this.saveBtnEl) {
                this.saveBtnEl.disabled = !dirty || this._savingNotes;
                this.saveBtnEl.classList.toggle('contact-panel__save-btn--dirty', dirty);
            }

            if (this.saveBtnLabelEl && !this._savingNotes) {
                this.saveBtnLabelEl.textContent = dirty ? 'Salvar alterações' : 'Salvar nota';
            }

            if (this.improveBtnEl) {
                this.improveBtnEl.disabled = trimmed.length === 0;
                this.improveBtnEl.classList.toggle('contact-panel__notes-ai-btn--disabled', trimmed.length === 0);
            }
        }

        setNotesStatus(text, type) {
            if (!this.notesStatusEl) return;
            this.notesStatusEl.textContent = text || '';
            this.notesStatusEl.className = 'contact-panel__save-status';
            if (type) this.notesStatusEl.classList.add('contact-panel__save-status--' + type);
        }

        async saveNotes() {
            if (this._savingNotes || !this.notesUrl) return;
            this._savingNotes = true;
            this.setNotesStatus('Salvando...', 'pending');

            try {
                const response = await fetch(this.notesUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ notes: this.notesEl?.value ?? '' }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Falha ao salvar nota');
                }

                this._savedNotes = this.notesEl?.value ?? '';
                this.syncNotesUi();
                this.setNotesStatus('Salvo', 'ok');
                setTimeout(() => this.setNotesStatus('', ''), 2000);
            } catch (error) {
                this.setNotesStatus('Erro ao salvar', 'error');
                alert('Erro ao salvar nota: ' + error.message);
            } finally {
                this._savingNotes = false;
            }
        }

        renderConversationTags(tags) {
            if (!this.tagsListEl) return;

            if (!tags || tags.length === 0) {
                this.tagsListEl.innerHTML = '<span class="contact-panel__tag-empty">Nenhuma etiqueta</span>';
                this.updateChatListTags([]);
                return;
            }

            this.tagsListEl.innerHTML = tags
                .map(
                    (tag) => `
                <div class="contact-panel__tag-wrap" data-tag-id="${tag.id}">
                    <span class="contact-panel__tag-pill" style="--tag-color: ${escapeHtml(tag.color)}">
                        <span class="contact-panel__tag-dot"></span>
                        <span class="contact-panel__tag-name">${escapeHtml(tag.name)}</span>
                    </span>
                    <button type="button" class="contact-panel__tag-remove" title="Remover etiqueta" data-tag-id="${tag.id}" aria-label="Remover ${escapeHtml(tag.name)}">
                        <span class="material-symbols-outlined text-[14px]">close</span>
                    </button>
                </div>`
                )
                .join('');
        }

        updateChatListTags(tags) {
            const listItem = document.querySelector(`[data-conversation-id="${this.conversationId}"]`);
            const tagsEl = listItem?.querySelector('.chat-list-item__tags');
            if (!tagsEl) return;

            const tagChips = (tags || [])
                .map(
                    (tag) =>
                        `<span class="chat-list-chip chat-list-chip--tag" style="--tag-color: ${escapeHtml(tag.color || '#4353E8')}">${escapeHtml(tag.name)}</span>`
                )
                .join('');

            let statusChip = '';
            if (listItem.querySelector('.chat-list-item__unread')) {
                statusChip = '<span class="chat-list-chip chat-list-chip--warning">Aguardando</span>';
            } else if (listItem.classList.contains('chat-list-item--resolved')) {
                statusChip = '<span class="chat-list-chip chat-list-chip--muted">Encerrado</span>';
            }

            tagsEl.innerHTML = tagChips + statusChip;
        }

        async fetchCurrentTagIds() {
            const response = await fetch(this.conversationTagsUrl, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error('Não foi possível carregar etiquetas da conversa');
            }
            return tagIdSet(data.tag_ids);
        }

        async openTagsModal() {
            if (!this.modalEl || !this.tagsContainerEl) return;

            try {
                const [tagsResponse, currentIds] = await Promise.all([
                    fetch(this.tagsUrl, { headers: { Accept: 'application/json' } }),
                    this.fetchCurrentTagIds(),
                ]);

                const tagsData = await tagsResponse.json();
                if (!tagsResponse.ok || !tagsData.success) {
                    throw new Error('Não foi possível carregar etiquetas');
                }

                this.selectedTagIds = currentIds;
                this.tagsContainerEl.innerHTML = this.buildTagsModalHtml(tagsData.tags || []);
                this.tagsContainerEl.querySelectorAll('.tags-modal__chip').forEach((chip) => {
                    chip.addEventListener('click', () => this.toggleModalTag(chip));
                });

                this.modalEl.classList.remove('hidden');
            } catch (error) {
                alert('Erro ao carregar etiquetas: ' + error.message);
            }
        }

        buildTagsModalHtml(tags) {
            const grouped = {};
            tags.forEach((tag) => {
                const cat = tag.category || 'custom';
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(tag);
            });

            const order = ['custom', 'priority', 'status', 'outcome'];
            const categories = [...order.filter((c) => grouped[c]), ...Object.keys(grouped).filter((c) => !order.includes(c))];

            return categories
                .map((category) => {
                    const chips = grouped[category]
                        .map((tag) => {
                            const selected = this.selectedTagIds.has(Number(tag.id));
                            return `
                            <button
                                type="button"
                                class="tags-modal__chip${selected ? ' tags-modal__chip--selected' : ''}"
                                data-tag-id="${tag.id}"
                                style="--tag-color: ${escapeHtml(tag.color)}"
                            >
                                <span class="tags-modal__chip-dot"></span>
                                <span class="tags-modal__chip-label">${escapeHtml(tag.name)}</span>
                                ${selected ? '<span class="material-symbols-outlined tags-modal__chip-check">check</span>' : ''}
                            </button>`;
                        })
                        .join('');

                    return `
                    <section class="tags-modal__group">
                        <h4 class="tags-modal__group-title">${escapeHtml(CATEGORY_LABELS[category] || category)}</h4>
                        <div class="tags-modal__chips">${chips}</div>
                    </section>`;
                })
                .join('');
        }

        async toggleModalTag(chip) {
            const tagId = Number(chip.dataset.tagId);
            if (this.selectedTagIds.has(tagId)) {
                this.selectedTagIds.delete(tagId);
            } else {
                this.selectedTagIds.add(tagId);
            }

            const selected = this.selectedTagIds.has(tagId);
            chip.classList.toggle('tags-modal__chip--selected', selected);

            let check = chip.querySelector('.tags-modal__chip-check');
            if (selected && !check) {
                check = document.createElement('span');
                check.className = 'material-symbols-outlined tags-modal__chip-check';
                check.textContent = 'check';
                chip.appendChild(check);
            } else if (!selected && check) {
                check.remove();
            }

            await this.syncTags();
        }

        async syncTags() {
            try {
                const tagIds = Array.from(this.selectedTagIds);

                const response = await fetch(this.attachTagsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ tag_ids: tagIds }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Falha ao atualizar etiquetas');
                }

                this.renderConversationTags(data.tags || []);
            } catch (error) {
                alert('Erro ao atualizar etiquetas: ' + error.message);
                throw error;
            }
        }

        async removeTag(tagId) {
            if (!confirm('Remover esta etiqueta?')) return;

            try {
                const response = await fetch(`/conversations/${this.conversationId}/tags/${tagId}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Falha ao remover etiqueta');
                }

                this.renderConversationTags(data.tags || []);
                if (this.selectedTagIds.has(tagId)) {
                    this.selectedTagIds.delete(tagId);
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }

        closeTagsModal() {
            this.modalEl?.classList.add('hidden');
        }

        renderConversationSector(sector) {
            if (!this.sectorEl || !sector) return;

            this.sectorEl.innerHTML = `
                <span
                    class="contact-panel__tag-pill contact-panel__sector-pill"
                    style="--tag-color: ${escapeHtml(sector.color)}"
                    data-sector-id="${sector.id ?? ''}"
                >
                    <span class="contact-panel__tag-dot"></span>
                    <span class="contact-panel__tag-name">${escapeHtml(sector.name)}</span>
                </span>`;

            this.selectedSectorId = sector.id ? Number(sector.id) : null;
        }

        updateChatListSector(sector) {
            window.dispatchEvent(
                new CustomEvent('conversation-sector-updated', {
                    detail: {
                        conversation_id: this.conversationId,
                        sector,
                    },
                })
            );
        }

        async fetchCurrentSectorId() {
            const response = await fetch(this.conversationSectorUrl, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error('Não foi possível carregar o setor da conversa');
            }
            this.selectedSectorId = data.sector_id ? Number(data.sector_id) : null;
            return this.selectedSectorId;
        }

        async openSectorModal() {
            if (!this.sectorModalEl || !this.sectorsContainerEl) return;

            try {
                const [sectorsResponse] = await Promise.all([
                    fetch(this.sectorsUrl, { headers: { Accept: 'application/json' } }),
                    this.fetchCurrentSectorId(),
                ]);

                const sectorsData = await sectorsResponse.json();
                if (!sectorsResponse.ok || !sectorsData.success) {
                    throw new Error('Não foi possível carregar setores');
                }

                this.sectorsContainerEl.innerHTML = this.buildSectorModalHtml(sectorsData.sectors || []);
                this.sectorsContainerEl.querySelectorAll('.tags-modal__chip').forEach((chip) => {
                    chip.addEventListener('click', () => this.selectModalSector(chip));
                });

                this.sectorModalEl.classList.remove('hidden');
            } catch (error) {
                alert('Erro ao carregar setores: ' + error.message);
            }
        }

        buildSectorModalHtml(sectors) {
            const chips = (sectors || [])
                .map((sector) => {
                    const selected = this.selectedSectorId === Number(sector.id);
                    return `
                    <button
                        type="button"
                        class="tags-modal__chip${selected ? ' tags-modal__chip--selected' : ''}"
                        data-sector-id="${sector.id}"
                        style="--tag-color: ${escapeHtml(sector.color)}"
                    >
                        <span class="tags-modal__chip-dot"></span>
                        <span class="tags-modal__chip-label">${escapeHtml(sector.name)}</span>
                        ${selected ? '<span class="material-symbols-outlined tags-modal__chip-check">check</span>' : ''}
                    </button>`;
                })
                .join('');

            const generalSelected = this.selectedSectorId === null;
            const generalChip = `
                <button
                    type="button"
                    class="tags-modal__chip${generalSelected ? ' tags-modal__chip--selected' : ''}"
                    data-sector-id=""
                    style="--tag-color: #9CA3AF"
                >
                    <span class="tags-modal__chip-dot"></span>
                    <span class="tags-modal__chip-label">Geral</span>
                    ${generalSelected ? '<span class="material-symbols-outlined tags-modal__chip-check">check</span>' : ''}
                </button>`;

            return `
                <section class="tags-modal__group">
                    <h4 class="tags-modal__group-title">Setores disponíveis</h4>
                    <div class="tags-modal__chips">${generalChip}${chips}</div>
                </section>`;
        }

        async selectModalSector(chip) {
            const sectorIdRaw = chip.dataset.sectorId;
            const sectorId = sectorIdRaw ? Number(sectorIdRaw) : null;

            if (this.selectedSectorId === sectorId) {
                return;
            }

            this.sectorsContainerEl?.querySelectorAll('.tags-modal__chip').forEach((el) => {
                const selected = el === chip;
                el.classList.toggle('tags-modal__chip--selected', selected);
                const check = el.querySelector('.tags-modal__chip-check');
                if (selected && !check) {
                    const icon = document.createElement('span');
                    icon.className = 'material-symbols-outlined tags-modal__chip-check';
                    icon.textContent = 'check';
                    el.appendChild(icon);
                } else if (!selected && check) {
                    check.remove();
                }
            });

            await this.syncSector(sectorId);
        }

        async syncSector(sectorId) {
            try {
                const response = await fetch(this.updateSectorUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ sector_id: sectorId }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Falha ao atualizar setor');
                }

                this.renderConversationSector(data.sector);
            } catch (error) {
                alert('Erro ao atualizar setor: ' + error.message);
                throw error;
            }
        }

        closeSectorModal() {
            this.sectorModalEl?.classList.add('hidden');
        }
    }

    function init() {
        const panel = document.querySelector('.contact-panel[data-conversation-id]');
        if (!panel) return null;
        const helper = new ContactPanelHelper(panel);
        global.ContactPanelHelper = helper;
        return helper;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window);
