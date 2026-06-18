/**
 * Atualiza a lista lateral de conversas via polling (novas msgs, novos contatos, contagens).
 */
(function (global) {
    class ChatListPoller {
        constructor(options) {
            this.url = options.url;
            this.intervalMs = options.intervalMs || 4000;
            this.activeConversationId = options.activeConversationId
                ? String(options.activeConversationId)
                : null;
            this._timer = null;
            this._knownIds = new Set(options.initialIds || []);
            this._polling = false;
        }

        start() {
            if (this._timer) return;
            this.poll();
            this._timer = setInterval(() => this.poll(), this.intervalMs);
            window.addEventListener('chat-message-sent', (e) => this.onMessageSent(e.detail));
        }

        stop() {
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }
        }

        onMessageSent(detail) {
            if (!detail?.conversation_id) return;
            const id = String(detail.conversation_id);
            const el = document.querySelector(`[data-conversation-id="${id}"]`);
            if (el) {
                this.updateItem(el, {
                    id: detail.conversation_id,
                    last_preview: detail.preview || detail.content || '',
                    last_time: 'agora',
                    pending: false,
                });
                this.moveToTop(el);
            } else {
                this.poll();
            }
        }

        async poll() {
            if (this._polling || !this.url) return;
            this._polling = true;
            try {
                const res = await fetch(this.url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                this.apply(data);
            } catch (_) {
                /* silencioso */
            } finally {
                this._polling = false;
            }
        }

        apply(data) {
            this.updateTabCounts(data);
            const container = document.querySelector('.chat-list-items');
            if (!container || !Array.isArray(data.conversations)) return;

            const existing = new Map();
            container.querySelectorAll('[data-conversation-id]').forEach((el) => {
                existing.set(el.dataset.conversationId, el);
            });

            const newItems = [];
            const ordered = [];

            data.conversations.forEach((conv) => {
                const id = String(conv.id);
                let el = existing.get(id);
                if (!el) {
                    el = this.createItem(conv);
                    newItems.push(conv);
                    this._knownIds.add(id);
                } else {
                    this.updateItem(el, conv);
                }
                ordered.push(el);
            });

            ordered.forEach((el) => container.appendChild(el));

            if (newItems.length > 0) {
                this.notifyNewConversations(newItems);
            }
        }

        updateTabCounts(data) {
            document.querySelectorAll('[data-tab-count]').forEach((el) => {
                const key = el.dataset.tabCount;
                if (key === 'total' && data.total_count != null) {
                    el.textContent = data.total_count;
                }
                if (key === 'pending' && data.pending_count != null) {
                    el.textContent = data.pending_count;
                }
            });
        }

        notifyNewConversations(items) {
            const pending = items.filter((c) => c.pending);
            if (pending.length === 0) return;

            const badge = document.getElementById('pendingBadge');
            const nameEl = document.getElementById('pendingName');
            if (badge && nameEl) {
                nameEl.textContent = pending[0].contact_name || 'Aguardando sua a????o';
                badge.classList.remove('hidden');
            }

            window.dispatchEvent(
                new CustomEvent('chat-list-new', { detail: { conversations: items } })
            );
        }

        createItem(conv) {
            const a = document.createElement('a');
            a.href = conv.url;
            a.className = 'chat-list-item' + (conv.active ? ' chat-list-item--active' : '');
            if (conv.resolved) a.classList.add('chat-list-item--resolved');
            a.dataset.conversationId = String(conv.id);
            a.dataset.chatName = (conv.contact_name || '').toLowerCase();
            a.dataset.chatPhone = conv.contact_phone || '';
            a.innerHTML = this.itemHtml(conv);
            return a;
        }

        updateItem(el, conv) {
            el.href = conv.url || el.href;
            el.classList.toggle('chat-list-item--active', !!conv.active);
            el.classList.toggle('chat-list-item--resolved', !!conv.resolved);
            if (conv.contact_name) {
                el.dataset.chatName = conv.contact_name.toLowerCase();
            }
            if (conv.contact_phone) {
                el.dataset.chatPhone = conv.contact_phone;
            }

            const preview = el.querySelector('.chat-list-item__preview');
            if (preview && conv.last_preview != null) {
                preview.textContent = conv.last_preview;
            }
            const time = el.querySelector('.chat-list-item__time');
            if (time && conv.last_time != null) {
                time.textContent = conv.last_time;
            }
            const name = el.querySelector('.chat-list-item__name');
            if (name && conv.contact_name) {
                name.textContent = conv.contact_name;
            }

            const avatar = el.querySelector('.contact-avatar');
            if (avatar) {
                avatar.textContent = conv.contact_initials || avatar.textContent;
                avatar.classList.toggle('contact-avatar--pending', !!conv.pending);
            }

            const unread = el.querySelector('.chat-list-item__unread');
            if (conv.pending) {
                if (!unread) {
                    const row = el.querySelector('.chat-list-item__preview-row');
                    if (row) {
                        const span = document.createElement('span');
                        span.className = 'chat-list-item__unread';
                        span.title = 'Aguardando';
                        span.textContent = '!';
                        row.appendChild(span);
                    }
                }
            } else if (unread) {
                unread.remove();
            }

            const tags = el.querySelector('.chat-list-item__tags');
            if (tags && conv.sector != null) {
                const pendingChip = conv.pending
                    ? '<span class="chat-list-chip chat-list-chip--warning">Aguardando</span>'
                    : conv.resolved
                      ? '<span class="chat-list-chip chat-list-chip--muted">Encerrado</span>'
                      : '';
                tags.innerHTML =
                    `<span class="chat-list-chip chat-list-chip--neutral">${this.escape(conv.sector)}</span>` +
                    pendingChip;
            }
        }

        moveToTop(el) {
            const container = document.querySelector('.chat-list-items');
            if (container && el.parentNode === container) {
                container.prepend(el);
            }
        }

        itemHtml(conv) {
            const pending = conv.pending
                ? '<span class="chat-list-item__unread" title="Aguardando">!</span>'
                : '';
            const pendingChip = conv.pending
                ? '<span class="chat-list-chip chat-list-chip--warning">Aguardando</span>'
                : conv.resolved
                  ? '<span class="chat-list-chip chat-list-chip--muted">Encerrado</span>'
                  : '';
            const avatarClass =
                'contact-avatar' + (conv.pending ? ' contact-avatar--pending' : '');

            return `<div class="chat-list-item__row">
                <div class="${avatarClass}">${this.escape(conv.contact_initials)}</div>
                <div class="chat-list-item__body">
                    <div class="chat-list-item__top">
                        <h3 class="chat-list-item__name">${this.escape(conv.contact_name)}</h3>
                        <time class="chat-list-item__time">${this.escape(conv.last_time)}</time>
                    </div>
                    <div class="chat-list-item__preview-row">
                        <p class="chat-list-item__preview">${this.escape(conv.last_preview)}</p>
                        ${pending}
                    </div>
                    <div class="chat-list-item__tags">
                        <span class="chat-list-chip chat-list-chip--neutral">${this.escape(conv.sector)}</span>
                        ${pendingChip}
                    </div>
                </div>
            </div>`;
        }

        escape(text) {
            const d = document.createElement('div');
            d.textContent = text == null ? '' : String(text);
            return d.innerHTML;
        }
    }

    global.ChatListPoller = ChatListPoller;
})(window);

