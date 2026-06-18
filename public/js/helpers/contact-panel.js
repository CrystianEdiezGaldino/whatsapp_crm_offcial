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
            this.selectedTagIds = new Set();
            this._savingNotes = false;

            this.notesEl = panel.querySelector('#contactNotes');
            this.notesStatusEl = panel.querySelector('#contactNotesStatus');
            this.tagsListEl = panel.querySelector('#conversationTags');
            this.modalEl = panel.querySelector('#tagsModal');
            this.tagsContainerEl = panel.querySelector('#tagsContainer');

            this.bindEvents();
        }

        bindEvents() {
            this.panel.querySelector('#saveContactNotes')?.addEventListener('click', () => this.saveNotes());
            this.panel.querySelector('#openTagsModalBtn')?.addEventListener('click', () => this.openTagsModal());

            this.modalEl?.querySelectorAll('[data-close-tags-modal]').forEach((el) => {
                el.addEventListener('click', () => this.closeTagsModal());
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
