/**
 * SSE Manager - Real-time status updates via Server-Sent Events
 * Handles message and conversation status updates
 * Falls back to polling if Redis/SSE is unavailable
 */

class SSEManager {
    constructor() {
        this.connections = new Map();
        this.reconnectAttempts = new Map();
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000; // ms
        this.pollingIntervals = new Map();
        this.pollingDelay = 5000; // 5 seconds for polling
        this.lastStatuses = new Map();
        this.sseAvailable = true;
    }

    /**
     * Connect to conversation - uses polling only (SSE disabled due to PHP timeout issues)
     */
    connectToConversation(conversationId) {
        console.log(`[POLLING] Starting poll for conversation ${conversationId}`);
        this.startPollingConversation(conversationId);
    }

    /**
     * Connect to messages - uses polling only (SSE disabled)
     */
    connectToMessages() {
        console.log('[POLLING] Starting poll for messages');
        this.startPollingMessages();
    }

    /**
     * Connect to conversations - uses polling only (SSE disabled)
     */
    connectToConversations() {
        console.log('[POLLING] Starting poll for conversations');
        this.startPollingConversations();
    }

    /**
     * Handle message status change event
     */
    handleMessageStatusChange(data) {
        const { message_id, status, conversation_id } = data;

        // Update message in DOM
        const messageEl = document.querySelector(`[data-message-id="${message_id}"]`);
        if (messageEl) {
            this.updateMessageStatus(messageEl, status);
        }

        // Dispatch custom event for other components
        window.dispatchEvent(
            new CustomEvent('message-status-changed', { detail: data })
        );
    }

    /**
     * Handle conversation status change event
     */
    handleConversationStatusChange(data) {
        const { conversation_id, status, claimed_by_name } = data;

        // Update conversation in list
        const conversationEl = document.querySelector(`[data-conversation-id="${conversation_id}"]`);
        if (conversationEl) {
            this.updateConversationStatus(conversationEl, status, claimed_by_name);
        }

        // Dispatch custom event for other components
        window.dispatchEvent(
            new CustomEvent('conversation-status-changed', { detail: data })
        );
    }

    /**
     * Update message status in DOM
     */
    updateMessageStatus(messageEl, status) {
        const statusEl = messageEl.querySelector('[data-status]');
        if (!statusEl) return;

        let icon = '⏳'; // pending
        let title = 'Pendente';

        switch (status) {
            case 'sent':
                icon = '✓';
                title = 'Enviado';
                break;
            case 'delivered':
                icon = '✓✓';
                title = 'Entregue';
                break;
            case 'read':
                icon = '✓✓';
                title = 'Lido';
                statusEl.style.color = '#007bff'; // blue
                break;
            case 'failed':
                icon = '✗';
                title = 'Falha ao enviar';
                statusEl.style.color = '#dc3545'; // red
                break;
        }

        statusEl.textContent = icon;
        statusEl.title = title;
        statusEl.setAttribute('data-status', status);

        console.log(`[SSE] Updated message ${messageEl.dataset.messageId} status to ${status}`);
    }

    /**
     * Update conversation status in DOM
     */
    updateConversationStatus(conversationEl, status, claimedByName) {
        const statusEl = conversationEl.querySelector('[data-conv-status]');
        if (!statusEl) return;

        let icon = '📌';
        let text = 'Novo';
        let className = 'badge-info';

        switch (status) {
            case 'new':
                icon = '📌';
                text = 'Novo';
                className = 'badge-info';
                break;
            case 'in_attendance':
                icon = '👤';
                text = claimedByName ? `${claimedByName}` : 'Em atendimento';
                className = 'badge-warning';
                break;
            case 'resolved':
                icon = '✓';
                text = 'Resolvido';
                className = 'badge-success';
                break;
        }

        statusEl.innerHTML = `<span class="badge ${className}">${icon} ${text}</span>`;
        statusEl.setAttribute('data-conv-status', status);

        console.log(`[SSE] Updated conversation ${conversationEl.dataset.conversationId} status to ${status}`);
    }

    /**
     * Attempt to reconnect with exponential backoff
     */
    attemptReconnect(key, reconnectFn) {
        const attempts = (this.reconnectAttempts.get(key) || 0) + 1;

        if (attempts > this.maxReconnectAttempts) {
            console.error(`[SSE] Max reconnect attempts reached for ${key}`);
            return;
        }

        const delay = this.reconnectDelay * attempts;
        console.log(`[SSE] Attempting reconnection in ${delay}ms (attempt ${attempts}/${this.maxReconnectAttempts})`);

        this.reconnectAttempts.set(key, attempts);

        setTimeout(() => {
            reconnectFn();
        }, delay);
    }

    /**
     * Disconnect from a channel
     */
    disconnect(key) {
        const eventSource = this.connections.get(key);
        if (eventSource) {
            eventSource.close();
            this.connections.delete(key);
            this.reconnectAttempts.delete(key);
            console.log(`[SSE] Disconnected from ${key}`);
        }
    }

    /**
     * Disconnect all connections
     */
    disconnectAll() {
        this.connections.forEach((eventSource) => {
            eventSource.close();
        });
        this.connections.clear();
        this.reconnectAttempts.clear();
        this.pollingIntervals.forEach((interval) => clearInterval(interval));
        this.pollingIntervals.clear();
        console.log('[SSE] Disconnected from all channels');
    }

    /**
     * Poll for conversation status changes (fallback when SSE unavailable)
     */
    startPollingConversation(conversationId) {
        const key = `poll_conversation_${conversationId}`;

        if (this.pollingIntervals.has(key)) {
            console.log(`[POLL] Already polling conversation ${conversationId}`);
            return;
        }

        console.log(`[POLL] Starting poll for conversation ${conversationId}`);

        const interval = setInterval(() => {
            fetch(`/conversations/${conversationId}/poll`)
                .then(response => response.json())
                .then(data => {
                    const conversationKey = `conv_status_${conversationId}`;
                    const lastStatus = this.lastStatuses.get(conversationKey);

                    if (data.conversation && data.conversation.status !== lastStatus) {
                        this.lastStatuses.set(conversationKey, data.conversation.status);
                        this.handleConversationStatusChange({
                            conversation_id: conversationId,
                            status: data.conversation.status,
                            claimed_by_name: data.conversation.claimed_by_name
                        });
                    }

                    if (data.messages) {
                        data.messages.forEach(msg => {
                            const msgKey = `msg_status_${msg.id}`;
                            const lastMsgStatus = this.lastStatuses.get(msgKey);
                            if (msg.status !== lastMsgStatus) {
                                this.lastStatuses.set(msgKey, msg.status);
                                this.handleMessageStatusChange({
                                    message_id: msg.id,
                                    status: msg.status,
                                    conversation_id: conversationId
                                });
                            }
                        });
                    }
                })
                .catch(error => console.error(`[POLL] Error polling conversation ${conversationId}:`, error));
        }, this.pollingDelay);

        this.pollingIntervals.set(key, interval);
    }

    /**
     * Poll for messages status changes (fallback when SSE unavailable)
     */
    startPollingMessages() {
        const key = 'poll_messages';

        if (this.pollingIntervals.has(key)) {
            console.log('[POLL] Already polling messages');
            return;
        }

        console.log('[POLL] Starting poll for messages');

        const interval = setInterval(() => {
            const messageElements = document.querySelectorAll('[data-message-id]');
            if (messageElements.length === 0) return;

            messageElements.forEach(el => {
                const messageId = el.dataset.messageId;
                fetch(`/api/messages/${messageId}/status`)
                    .then(response => response.json())
                    .then(data => {
                        const lastStatus = this.lastStatuses.get(`msg_${messageId}`);
                        if (data.status !== lastStatus) {
                            this.lastStatuses.set(`msg_${messageId}`, data.status);
                            this.handleMessageStatusChange({
                                message_id: messageId,
                                status: data.status
                            });
                        }
                    })
                    .catch(() => {}); // Silent fail for individual message status
            });
        }, this.pollingDelay);

        this.pollingIntervals.set(key, interval);
    }

    /**
     * Poll for conversations list status (fallback when SSE unavailable)
     */
    startPollingConversations() {
        const key = 'poll_conversations';

        if (this.pollingIntervals.has(key)) {
            console.log('[POLL] Already polling conversations');
            return;
        }

        console.log('[POLL] Starting poll for conversations');

        const interval = setInterval(() => {
            fetch('/api/conversations/status')
                .then(response => response.json())
                .then(data => {
                    if (data.conversations) {
                        data.conversations.forEach(conv => {
                            const convKey = `conv_${conv.id}`;
                            const lastStatus = this.lastStatuses.get(convKey);
                            if (conv.status !== lastStatus) {
                                this.lastStatuses.set(convKey, conv.status);
                                this.handleConversationStatusChange({
                                    conversation_id: conv.id,
                                    status: conv.status,
                                    claimed_by_name: conv.claimed_by_name
                                });
                            }
                        });
                    }
                })
                .catch(error => console.error('[POLL] Error polling conversations:', error));
        }, this.pollingDelay);

        this.pollingIntervals.set(key, interval);
    }
}

// Export globally
window.SSEManager = new SSEManager();
