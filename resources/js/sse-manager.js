/**
 * SSE Manager - Real-time status updates via Server-Sent Events
 * Handles message and conversation status updates
 */

class SSEManager {
    constructor() {
        this.connections = new Map();
        this.reconnectAttempts = new Map();
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000; // ms
    }

    /**
     * Connect to SSE stream for a conversation
     */
    connectToConversation(conversationId) {
        const key = `conversation_${conversationId}`;

        if (this.connections.has(key)) {
            console.log(`[SSE] Already connected to conversation ${conversationId}`);
            return;
        }

        console.log(`[SSE] Connecting to conversation ${conversationId}`);

        const url = `/api/sse/conversation/${conversationId}`;
        const eventSource = new EventSource(url);

        eventSource.addEventListener('connected', (e) => {
            console.log('[SSE] Connected to conversation channel', JSON.parse(e.data));
            this.reconnectAttempts.delete(key);
        });

        eventSource.addEventListener('message.status_changed', (e) => {
            const data = JSON.parse(e.data);
            console.log('[SSE] Message status changed', data);
            this.handleMessageStatusChange(data);
        });

        eventSource.addEventListener('conversation.status_changed', (e) => {
            const data = JSON.parse(e.data);
            console.log('[SSE] Conversation status changed', data);
            this.handleConversationStatusChange(data);
        });

        eventSource.addEventListener('error', (e) => {
            console.error('[SSE] Connection error', e);
            eventSource.close();
            this.connections.delete(key);
            this.attemptReconnect(key, () => this.connectToConversation(conversationId));
        });

        eventSource.onerror = () => {
            if (eventSource.readyState === EventSource.CLOSED) {
                console.warn(`[SSE] Connection closed for conversation ${conversationId}`);
                eventSource.close();
                this.connections.delete(key);
                this.attemptReconnect(key, () => this.connectToConversation(conversationId));
            }
        };

        this.connections.set(key, eventSource);
    }

    /**
     * Connect to global messages status channel
     */
    connectToMessages() {
        const key = 'messages_status';

        if (this.connections.has(key)) {
            console.log('[SSE] Already connected to messages channel');
            return;
        }

        console.log('[SSE] Connecting to messages status channel');

        const url = `/api/sse/messages`;
        const eventSource = new EventSource(url);

        eventSource.addEventListener('connected', (e) => {
            console.log('[SSE] Connected to messages channel', JSON.parse(e.data));
            this.reconnectAttempts.delete(key);
        });

        eventSource.addEventListener('message.status_changed', (e) => {
            const data = JSON.parse(e.data);
            console.log('[SSE] Message status changed (global)', data);
            this.handleMessageStatusChange(data);
        });

        eventSource.addEventListener('error', (e) => {
            console.error('[SSE] Connection error on messages channel', e);
            eventSource.close();
            this.connections.delete(key);
            this.attemptReconnect(key, () => this.connectToMessages());
        });

        this.connections.set(key, eventSource);
    }

    /**
     * Connect to global conversations status channel
     */
    connectToConversations() {
        const key = 'conversations_status';

        if (this.connections.has(key)) {
            console.log('[SSE] Already connected to conversations channel');
            return;
        }

        console.log('[SSE] Connecting to conversations status channel');

        const url = `/api/sse/conversations`;
        const eventSource = new EventSource(url);

        eventSource.addEventListener('connected', (e) => {
            console.log('[SSE] Connected to conversations channel', JSON.parse(e.data));
            this.reconnectAttempts.delete(key);
        });

        eventSource.addEventListener('conversation.status_changed', (e) => {
            const data = JSON.parse(e.data);
            console.log('[SSE] Conversation status changed (global)', data);
            this.handleConversationStatusChange(data);
        });

        eventSource.addEventListener('error', (e) => {
            console.error('[SSE] Connection error on conversations channel', e);
            eventSource.close();
            this.connections.delete(key);
            this.attemptReconnect(key, () => this.connectToConversations());
        });

        this.connections.set(key, eventSource);
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
        console.log('[SSE] Disconnected from all channels');
    }
}

// Export globally
window.SSEManager = new SSEManager();
