/**
 * Active Conversation Polling - Polls active conversation every 2-3 seconds
 * Handles: new messages, message updates, conversation status
 */

/**
 * Escape HTML special characters to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, char => map[char]);
}

/**
 * Format timestamp to readable format
 * @param {string} timestamp - ISO timestamp
 * @returns {string} Formatted time
 */
function formatTime(timestamp) {
    try {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'agora';
        if (diffMins < 60) return `${diffMins}m`;
        if (diffHours < 24) return `${diffHours}h`;
        if (diffDays < 7) return `${diffDays}d`;

        return date.toLocaleDateString('pt-BR');
    } catch (error) {
        console.error('[ConversationPolling] Error formatting time:', error);
        return '';
    }
}

class ActiveConversationPoller {
    constructor(conversationId, options = {}) {
        this.conversationId = conversationId;
        this.pollInterval = options.interval || 2500; // 2.5 seconds
        this.pollerId = null;
        this.isRunning = false;
        this.lastMessageId = null;
    }

    /**
     * Start polling conversation
     */
    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        console.log(`[ConversationPolling] Starting polling for conversation ${this.conversationId}...`);
        // First poll immediately, then set interval
        this.poll();
    }

    /**
     * Stop polling conversation
     */
    stop() {
        if (this.pollerId) {
            clearTimeout(this.pollerId);
            this.pollerId = null;
        }
        this.isRunning = false;
        console.log(`[ConversationPolling] Stopped polling for conversation ${this.conversationId}`);
    }

    /**
     * Perform poll request to get new messages
     */
    async poll() {
        try {
            const url = `/conversations/${this.conversationId}/poll`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            // Handle new messages
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    this.addMessageToUI(message);
                    this.lastMessageId = message.id;
                    window.lastMessageId = message.id;
                });
                // Auto-scroll after adding messages
                this.scrollToBottom();
            }

            // Handle conversation status updates
            if (data.status) {
                this.updateConversationStatus(data.status);
            }

        } catch (error) {
            console.error('[ConversationPolling] Error polling conversation:', error);
        } finally {
            if (this.isRunning) {
                this.pollerId = setTimeout(() => this.poll(), this.pollInterval);
            }
        }
    }

    /**
     * Add message to chat UI
     * @param {Object} message - Message object with id, text, sender, timestamp
     */
    addMessageToUI(message) {
        try {
            const chatMessages = document.querySelector('[data-chat-messages]');
            if (!chatMessages) {
                console.warn('[ConversationPolling] Chat messages container not found');
                return;
            }

            // Check if message already exists
            const existingMessage = document.querySelector(`[data-message-id="${message.id}"]`);
            if (existingMessage) {
                return; // Message already displayed
            }

            // Create message element
            const messageElement = document.createElement('div');
            messageElement.dataset.messageId = message.id;
            messageElement.className = 'mb-4 flex ' + (message.is_user ? 'justify-end' : 'justify-start');

            // Message bubble
            const bubble = document.createElement('div');
            bubble.className = message.is_user
                ? 'max-w-xs bg-primary text-on-primary rounded-lg px-4 py-2'
                : 'max-w-xs bg-surface-variant text-on-surface rounded-lg px-4 py-2';

            // Message text (use textContent to safely escape)
            const textElement = document.createElement('p');
            textElement.className = 'text-sm';
            textElement.textContent = message.text || '';
            bubble.appendChild(textElement);

            // Message time (optional)
            if (message.created_at) {
                const timeElement = document.createElement('p');
                timeElement.className = 'text-xs mt-1 opacity-70';
                timeElement.textContent = formatTime(message.created_at);
                bubble.appendChild(timeElement);
            }

            messageElement.appendChild(bubble);
            chatMessages.appendChild(messageElement);

            // Show notification if not user message
            if (!message.is_user && window.Feedback) {
                window.Feedback.info('Nova mensagem recebida', 2000);
            }

        } catch (error) {
            console.error('[ConversationPolling] Error adding message to UI:', error);
        }
    }

    /**
     * Scroll chat to bottom to show latest messages
     */
    scrollToBottom() {
        try {
            const chatMessages = document.querySelector('[data-chat-messages]');
            if (chatMessages) {
                setTimeout(() => {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 0);
            }
        } catch (error) {
            console.error('[ConversationPolling] Error scrolling to bottom:', error);
        }
    }

    /**
     * Update conversation status
     * @param {Object} status - Status object
     */
    updateConversationStatus(status) {
        try {
            // Update status badge
            const statusBadge = document.querySelector('[data-conversation-status]');
            if (statusBadge && status.label) {
                statusBadge.textContent = status.label;
                statusBadge.className = `px-2 py-1 rounded text-xs font-semibold ${status.class || ''}`;
            }

            // Update claimed info
            const claimedElement = document.querySelector('[data-claimed-by]');
            if (claimedElement && status.claimed_by) {
                claimedElement.textContent = `Reivindicado por: ${status.claimed_by}`;
            }

        } catch (error) {
            console.error('[ConversationPolling] Error updating conversation status:', error);
        }
    }
}

// Initialize and start on page load
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = window.activeConversationId;
    if (conversationId) {
        window.activeConversationPoller = new ActiveConversationPoller(conversationId, {
            interval: 2500 // 2.5 seconds
        });
        window.activeConversationPoller.start();
        console.log('[ConversationPolling] Initialized with interval:', 2500);
    }
});

// Stop polling when page unloads
window.addEventListener('beforeunload', () => {
    if (window.activeConversationPoller) {
        window.activeConversationPoller.stop();
    }
});

// Stop polling when navigating away from conversation view
window.addEventListener('navigate', () => {
    if (window.activeConversationPoller) {
        window.activeConversationPoller.stop();
    }
});
