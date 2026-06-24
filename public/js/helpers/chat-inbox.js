/**
 * Chat inbox: envio AJAX, poll e deduplica????o (texto, anexo, ??udio).
 */
(function (global) {
    class ChatInboxHelper {
        constructor(options) {
            this.chatEl = options.chatEl || null;
            this.contactName = options.contactName || '';
            this.pollUrl = options.pollUrl || null;
            this.pollIntervalMs = options.pollIntervalMs || 3000;
            this.seenKeys = new Set(options.initialKeys || []);
            this.lastMessageId = options.lastMessageId || 0;
            this._sending = false;
            this._pollTimer = null;

            (options.initialMessageIds || []).forEach((id) => {
                this.seenKeys.add('id:' + id);
            });
        }

        static dedupeKey(msg) {
            if (!msg) return '';
            if (msg.dedupe_key) return String(msg.dedupe_key);
            if (msg.wa_message_id) return 'wa:' + msg.wa_message_id;
            if (msg.id) return 'id:' + msg.id;
            return [
                'tmp',
                msg.direction || '',
                msg.type || '',
                msg.content || '',
                msg.media_url || '',
                msg.created_at || '',
            ].join(':');
        }

        static escapeAttr(value) {
            return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
        }

        hasSeen(msg) {
            const key = ChatInboxHelper.dedupeKey(msg);
            if (!key) return false;
            if (this.seenKeys.has(key)) return true;
            if (this.chatEl) {
                const found = this.chatEl.querySelector(
                    '[data-dedupe-key="' + ChatInboxHelper.escapeAttr(key) + '"]'
                );
                if (found) return true;
            }
            return false;
        }

        markSeen(msg) {
            const key = ChatInboxHelper.dedupeKey(msg);
            if (key) this.seenKeys.add(key);
            if (msg.id) {
                this.lastMessageId = Math.max(this.lastMessageId, Number(msg.id));
            }
        }

        appendIfNew(msg) {
            if (!msg || !this.chatEl || this.hasSeen(msg)) {
                return false;
            }
            this.markSeen(msg);
            const row = msg.direction === 'inbound'
                ? this._renderInbound(msg)
                : this._renderOutbound(msg);
            row.dataset.dedupeKey = ChatInboxHelper.dedupeKey(msg);
            this.chatEl.appendChild(row);
            this.chatEl.scrollTop = this.chatEl.scrollHeight;
            return true;
        }

        appendMany(messages) {
            if (!Array.isArray(messages)) return;
            messages.forEach((m) => this.appendIfNew(m));
        }

        bindSendForm(formEl, onSuccess) {
            if (!formEl) return;

            formEl.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (this._sending) return;
                this._sending = true;

                const sendBtn = formEl.querySelector('button[type="submit"]');
                const originalHTML = sendBtn ? sendBtn.innerHTML : '';
                if (sendBtn) {
                    sendBtn.innerHTML =
                        '<span class="material-symbols-outlined animate-spin">progress_activity</span>';
                    sendBtn.disabled = true;
                }

                try {
                    const response = await fetch(formEl.action, {
                        method: 'POST',
                        body: new FormData(formEl),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch (_) {}

                    if (response.ok && data.success) {
                        this.appendIfNew(data.message);
                        window.dispatchEvent(new CustomEvent('chat-message-sent', {
                            detail: {
                                conversation_id: data.conversation_id,
                                content: data.message?.content,
                                preview: data.message?.content || 'M??dia',
                            },
                        }));
                        if (typeof onSuccess === 'function') {
                            onSuccess(data);
                        }
                    } else {
                        alert(data.message || 'Erro ao enviar (HTTP ' + response.status + ')');
                    }
                } catch (err) {
                    alert('Erro ao enviar: ' + err.message);
                } finally {
                    if (sendBtn) {
                        sendBtn.innerHTML = originalHTML;
                        sendBtn.disabled = false;
                    }
                    this._sending = false;
                }
            });
        }

        startPolling() {
            if (!this.pollUrl || this._pollTimer) return;

            this._pollTimer = setInterval(async () => {
                try {
                    const res = await fetch(
                        this.pollUrl + '?last_id=' + encodeURIComponent(this.lastMessageId),
                        {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }
                    );
                    const data = await res.json();
                    this.appendMany(data.messages || []);
                } catch (_) {}
            }, this.pollIntervalMs);
        }

        _formatTime(iso) {
            if (!iso) return '';
            return new Date(iso).toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        _isAudioMessage(msg) {
            const mime = (msg.mime_type || '').toLowerCase();
            if (mime.startsWith('audio/')) return true;
            const name = (msg.media_filename || '').toLowerCase();
            return /\.(m4a|mp3|aac|amr|ogg|opus|webm|wav)$/.test(name);
        }

        _mediaHtml(msg) {
            if (!msg.media_url) return '';
            const url = msg.media_url.startsWith('http')
                ? msg.media_url
                : '/storage/' + msg.media_url;
            const mime = msg.mime_type || '';
            const isAudio = this._isAudioMessage(msg);

            if (mime.startsWith('image/')) {
                return `<a href="${url}" target="_blank"><img src="${url}" class="max-w-[280px] max-h-[240px] rounded-lg object-cover cursor-pointer hover:opacity-90 transition-opacity"></a>`;
            }
            if (isAudio) {
                const audioMime = mime.startsWith('audio/') ? mime : 'audio/mpeg';
                return `<div class="bg-white border border-outline-variant rounded-lg p-3 min-w-[260px]">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary text-lg">mic</span>
                        <p class="text-xs font-bold truncate flex-1">${msg.media_filename || 'Audio'}</p>
                    </div>
                    <audio controls class="w-full h-8" preload="metadata"><source src="${url}" type="${audioMime}"></audio>
                </div>`;
            }
            if (mime.startsWith('video/')) {
                return `<div class="bg-white border border-outline-variant rounded-lg overflow-hidden max-w-[300px]">
                    <video controls class="w-full max-h-[200px]" preload="metadata"><source src="${url}" type="${mime}"></video>
                </div>`;
            }
            const download = url
                ? `<a href="${url}" download class="material-symbols-outlined text-on-surface-variant text-lg hover:text-primary shrink-0">download</a>`
                : '';
            return `<div class="bg-white border border-outline-variant rounded-lg p-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold truncate">${msg.media_filename || 'Arquivo'}</p>
                    <p class="text-[10px] text-on-surface-variant">${mime}</p>
                </div>
                ${download}
            </div>`;
        }

        _renderOutbound(msg) {
            const div = document.createElement('div');
            div.className = 'flex flex-col items-end';
            const time = this._formatTime(msg.created_at);
            const mediaHtml = this._mediaHtml(msg);
            const contentHtml = msg.content && !(msg.media_url && this._isAudioMessage(msg) && msg.content === msg.media_filename)
                ? `<div class="bg-primary-container text-on-primary p-4 rounded-xl rounded-br-none shadow-md"><p class="text-sm leading-relaxed whitespace-pre-wrap">${msg.content}</p></div>`
                : '';

            div.innerHTML = `<div class="max-w-[80%]">
                ${mediaHtml ? '<div class="mb-1 rounded-lg overflow-hidden">' + mediaHtml + '</div>' : ''}
                ${contentHtml}
                <div class="flex justify-end items-center gap-1 mt-1">
                    <span class="text-[10px] text-on-surface-variant">${time}</span>
                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant">check</span>
                </div>
            </div>`;
            return div;
        }

        _renderInbound(msg) {
            const initials = (this.contactName || '?')
                .split(' ')
                .map((w) => w[0])
                .join('')
                .substring(0, 2)
                .toUpperCase();
            const div = document.createElement('div');
            div.className = 'flex items-end gap-3 max-w-[80%]';
            const time = this._formatTime(msg.created_at);
            const mediaHtml = this._mediaHtml(msg);
            const contentHtml = msg.content
                ? `<div class="bg-white p-4 rounded-xl rounded-bl-none border border-outline-variant shadow-sm"><p class="text-sm text-on-surface leading-relaxed whitespace-pre-wrap">${msg.content}</p></div>`
                : '';

            div.innerHTML = `<div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-[10px] text-on-primary-fixed shrink-0">${initials}</div>
                <div>
                    ${mediaHtml ? '<div class="mb-1 rounded-lg overflow-hidden">' + mediaHtml + '</div>' : ''}
                    ${contentHtml}
                    <span class="text-[10px] text-on-surface-variant mt-1 block">${time}</span>
                </div>`;
            return div;
        }
    }

    global.ChatInboxHelper = ChatInboxHelper;
})(window);

