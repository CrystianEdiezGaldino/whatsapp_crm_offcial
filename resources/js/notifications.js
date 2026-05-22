/**
 * Browser Notifications and Sound System
 * Requests permission, plays sound, shows browser notifications
 */

class NotificationManager {
    constructor() {
        this.soundUrl = '/sounds/notification.mp3';
        this.permissionGranted = false;
        this.permissionDenied = false;
    }

    init() {
        this.checkPermission();
        this.requestPermissionIfNeeded();
    }

    checkPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                this.permissionGranted = true;
            } else if (Notification.permission === 'denied') {
                this.permissionDenied = true;
            }
        }
    }

    async requestPermissionIfNeeded() {
        if (!('Notification' in window)) {
            console.log('[Notifications] Notification API not supported');
            return;
        }

        // If permission already granted or denied, don't ask again
        if (Notification.permission !== 'default') {
            return;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                this.permissionGranted = true;
                console.log('[Notifications] Permission granted');
            } else if (permission === 'denied') {
                this.permissionDenied = true;
                console.log('[Notifications] Permission denied');
            }
        } catch (error) {
            console.error('[Notifications] Error requesting permission:', error);
        }
    }

    playSound() {
        try {
            const audio = new Audio(this.soundUrl);
            audio.volume = 0.5;
            audio.play().catch(err => {
                console.log('[Notifications] Audio autoplay blocked:', err);
            });
        } catch (error) {
            console.error('[Notifications] Error playing sound:', error);
        }
    }

    showBrowserNotification(title, options = {}) {
        if (!this.permissionGranted) {
            return;
        }

        try {
            const notification = new Notification(title, {
                icon: '/images/whatsapp-icon.png',
                badge: '/images/badge.png',
                tag: 'whatsapp-notification',
                ...options
            });

            // Auto-close after 5 seconds
            setTimeout(() => notification.close(), 5000);

            // Focus window on click
            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        } catch (error) {
            console.error('[Notifications] Error showing notification:', error);
        }
    }

    notify(title, options = {}) {
        // Always play sound
        this.playSound();

        // Show browser notification if permission granted
        this.showBrowserNotification(title, options);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
    window.notificationManager.init();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
