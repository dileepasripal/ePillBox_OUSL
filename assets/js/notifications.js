class NotificationHandler {
    constructor() {
        this.lastCheck = new Date().toISOString();
        this.checkInterval = 60000; // Check every minute
        this.init();
    }

    init() {
        this.requestNotificationPermission();
        this.startPolling();
        this.setupAudioAlert();
    }

    async requestNotificationPermission() {
        if ("Notification" in window) {
            try {
                const permission = await Notification.requestPermission();
                console.log("Notification permission:", permission);
            } catch (error) {
                console.error("Error requesting notification permission:", error);
            }
        }
    }

    setupAudioAlert() {
        this.audioAlert = new Audio('/assets/notification.mp3');
        this.audioAlert.load();
    }

    startPolling() {
        this.checkNotifications();
        setInterval(() => this.checkNotifications(), this.checkInterval);
    }

    async checkNotifications() {
        try {
            const response = await fetch(`/includes/check_notifications.php?last_check=${encodeURIComponent(this.lastCheck)}`);
            const data = await response.json();

            if (data.hasNewNotifications) {
                this.handleNewNotifications(data.notifications);
                this.updateNotificationCount(data.count);
            }
            
            this.lastCheck = data.timestamp;
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    handleNewNotifications(notifications) {
        notifications.forEach(notification => {
            // Show browser notification
            if (Notification.permission === "granted") {
                this.showBrowserNotification(notification);
            }

            // Show in-app toast notification
            this.showToastNotification(notification);

            // Play sound for medication reminders
            if (notification.type === 'medication_reminder') {
                this.audioAlert.play();
            }
        });
    }

    showBrowserNotification(notification) {
        const browserNotification = new Notification(this.getNotificationTitle(notification), {
            body: notification.formatted_message || notification.message,
            icon: '/assets/icons/medicine-icon.png',
            badge: '/assets/icons/badge-icon.png',
            tag: `notification-${notification.id}`,
            requireInteraction: notification.type === 'medication_reminder'
        });

        browserNotification.onclick = () => {
            window.focus();
            window.location.href = `/pages/view_notification?id=${notification.id}`;
        };
    }

    showToastNotification(notification) {
        const toastHTML = `
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="${notification.type !== 'medication_reminder'}">
                <div class="toast-header">
                    <i class="${notification.icon || 'fas fa-bell'} mr-2"></i>
                    <strong class="mr-auto">${this.getNotificationTitle(notification)}</strong>
                    <small class="text-muted">just now</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${notification.formatted_message || notification.message}
                </div>
            </div>`;

        const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = toastContainer.lastElementChild;
        $(toastElement).toast({
            autohide: notification.type !== 'medication_reminder',
            delay: 5000
        }).toast('show');
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 right-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }

    getNotificationTitle(notification) {
        switch (notification.type) {
            case 'medication_reminder':
                return 'Medication Reminder';
            case 'prescription':
                return 'New Prescription';
            case 'refill_request':
                return 'Refill Request';
            default:
                return 'Notification';
        }
    }

    updateNotificationCount(count) {
        const counter = document.getElementById('notification-counter');
        if (counter) {
            counter.textContent = count;
            counter.style.display = count > 0 ? 'inline' : 'none';
        }
    }
}

// Initialize notification handler
document.addEventListener('DOMContentLoaded', () => {
    window.notificationHandler = new NotificationHandler();
});