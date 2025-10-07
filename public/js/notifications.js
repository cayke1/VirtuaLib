class NotificationManager {
    constructor() {
        this.isLoading = false;
        this.notifications = [];
        this.unreadCount = 0;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadUnreadCount();
        this.startPolling();
    }

    setupEventListeners() {
        // Desktop
        const notificationBtn = document.getElementById('notification-btn');
        const markAllReadBtn = document.getElementById('mark-all-read-btn');
        
        if (notificationBtn) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown('desktop');
            });
        }

        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        // Mobile
        const notificationBtnMobile = document.getElementById('notification-btn-mobile');
        const markAllReadBtnMobile = document.getElementById('mark-all-read-btn-mobile');
        
        if (notificationBtnMobile) {
            notificationBtnMobile.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown('mobile');
            });
        }

        if (markAllReadBtnMobile) {
            markAllReadBtnMobile.addEventListener('click', (e) => {
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            const desktopDropdown = document.getElementById('notification-dropdown');
            const mobileDropdown = document.getElementById('notification-dropdown-mobile');
            
            if (desktopDropdown && !desktopDropdown.contains(e.target) && 
                notificationBtn && !notificationBtn.contains(e.target)) {
                this.closeDropdown('desktop');
            }

            if (mobileDropdown && !mobileDropdown.contains(e.target) && 
                notificationBtnMobile && !notificationBtnMobile.contains(e.target)) {
                this.closeDropdown('mobile');
            }
        });

        // Mobile back button
        const mobileHeader = document.querySelector('.notification-header-mobile');
        if (mobileHeader) {
            mobileHeader.addEventListener('click', (e) => {
                if (e.target.tagName === 'DIV' || e.target.tagName === 'H3') {
                    this.closeDropdown('mobile');
                }
            });
        }
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            if (response.ok) {
                const data = await response.json();
                this.unreadCount = data.unread || 0;
                this.updateBadges();
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    async loadNotifications() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);

        try {
            const response = await fetch('/api/notifications');
            if (response.ok) {
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.renderNotifications();
                this.updateUnreadCount();
            } else {
                this.showError();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }

    renderNotifications() {
        const desktopList = document.getElementById('notification-list');
        const mobileList = document.getElementById('notification-list-mobile');
        const desktopEmpty = document.getElementById('notification-empty');
        const mobileEmpty = document.getElementById('notification-empty-mobile');

        if (this.notifications.length === 0) {
            this.showEmpty(true);
            return;
        }

        this.showEmpty(false);

        // Desktop
        if (desktopList) {
            desktopList.innerHTML = this.notifications.map(notification => 
                this.createNotificationHTML(notification, 'desktop')
            ).join('');
        }

        // Mobile
        if (mobileList) {
            mobileList.innerHTML = this.notifications.map(notification => 
                this.createNotificationHTML(notification, 'mobile')
            ).join('');
        }

        // Add click listeners to notification items
        this.setupNotificationClickListeners();
    }

    createNotificationHTML(notification, type) {
        const isUnread = !notification.is_read;
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const itemClass = type === 'mobile' ? 'notification-item-mobile' : 'notification-item';
        const titleClass = type === 'mobile' ? 'notification-title-mobile' : 'notification-title';
        const messageClass = type === 'mobile' ? 'notification-message-mobile' : 'notification-message';
        const timeClass = type === 'mobile' ? 'notification-time-mobile' : 'notification-time';

        return `
            <div class="${itemClass} ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="${titleClass}">${this.escapeHtml(notification.title)}</div>
                <div class="${messageClass}">${this.escapeHtml(notification.message)}</div>
                <div class="${timeClass}">${timeAgo}</div>
            </div>
        `;
    }

    setupNotificationClickListeners() {
        // Desktop
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.id;
                this.markAsRead(notificationId);
                item.classList.remove('unread');
            });
        });

        // Mobile
        document.querySelectorAll('.notification-item-mobile').forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.id;
                this.markAsRead(notificationId);
                item.classList.remove('unread');
            });
        });
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST'
            });
            
            if (response.ok) {
                // Update local state
                const notification = this.notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.is_read = 1;
                }
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST'
            });
            
            if (response.ok) {
                // Update local state
                this.notifications.forEach(notification => {
                    notification.is_read = 1;
                });
                this.updateUnreadCount();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        this.updateBadges();
    }

    updateBadges() {
        const desktopBadge = document.getElementById('notification-badge');
        const mobileBadge = document.getElementById('notification-badge-mobile');

        if (desktopBadge) {
            if (this.unreadCount > 0) {
                desktopBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                desktopBadge.classList.remove('hidden');
            } else {
                desktopBadge.classList.add('hidden');
            }
        }

        if (mobileBadge) {
            if (this.unreadCount > 0) {
                mobileBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                mobileBadge.classList.remove('hidden');
            } else {
                mobileBadge.classList.add('hidden');
            }
        }
    }

    toggleDropdown(type) {
        const dropdown = document.getElementById(`notification-dropdown${type === 'mobile' ? '-mobile' : ''}`);
        
        if (dropdown) {
            if (dropdown.classList.contains('show')) {
                this.closeDropdown(type);
            } else {
                this.openDropdown(type);
            }
        }
    }

    openDropdown(type) {
        const dropdown = document.getElementById(`notification-dropdown${type === 'mobile' ? '-mobile' : ''}`);
        
        if (dropdown) {
            dropdown.classList.add('show');
            this.loadNotifications();
        }
    }

    closeDropdown(type) {
        const dropdown = document.getElementById(`notification-dropdown${type === 'mobile' ? '-mobile' : ''}`);
        
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    }

    showLoading(show) {
        const desktopLoading = document.querySelector('.notification-loading');
        const mobileLoading = document.querySelector('.notification-loading-mobile');

        if (desktopLoading) {
            desktopLoading.style.display = show ? 'block' : 'none';
        }

        if (mobileLoading) {
            mobileLoading.style.display = show ? 'block' : 'none';
        }
    }

    showEmpty(show) {
        const desktopEmpty = document.getElementById('notification-empty');
        const mobileEmpty = document.getElementById('notification-empty-mobile');
        const desktopList = document.getElementById('notification-list');
        const mobileList = document.getElementById('notification-list-mobile');

        if (desktopEmpty) {
            desktopEmpty.style.display = show ? 'block' : 'none';
        }

        if (mobileEmpty) {
            mobileEmpty.style.display = show ? 'block' : 'none';
        }

        if (desktopList) {
            desktopList.style.display = show ? 'none' : 'block';
        }

        if (mobileList) {
            mobileList.style.display = show ? 'none' : 'block';
        }
    }

    showError() {
        const desktopList = document.getElementById('notification-list');
        const mobileList = document.getElementById('notification-list-mobile');

        if (desktopList) {
            desktopList.innerHTML = `
                <div class="notification-loading">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Erro ao carregar notificações</span>
                </div>
            `;
        }

        if (mobileList) {
            mobileList.innerHTML = `
                <div class="notification-loading-mobile">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Erro ao carregar notificações</span>
                </div>
            `;
        }
    }

    formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Agora mesmo';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `Há ${minutes} minuto${minutes > 1 ? 's' : ''}`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `Há ${hours} hora${hours > 1 ? 's' : ''}`;
        } else if (diffInSeconds < 2592000) {
            const days = Math.floor(diffInSeconds / 86400);
            return `Há ${days} dia${days > 1 ? 's' : ''}`;
        } else {
            return date.toLocaleDateString('pt-BR');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    startPolling() {
        // Poll for new notifications every 30 seconds
        setInterval(() => {
            this.loadUnreadCount();
        }, 30000);
    }

    // Public method to refresh notifications (can be called from other scripts)
    refresh() {
        this.loadUnreadCount();
        const desktopDropdown = document.getElementById('notification-dropdown');
        const mobileDropdown = document.getElementById('notification-dropdown-mobile');
        
        if ((desktopDropdown && desktopDropdown.classList.contains('show')) ||
            (mobileDropdown && mobileDropdown.classList.contains('show'))) {
            this.loadNotifications();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.NotificationManager = new NotificationManager();
});

// Export for use in other scripts
window.NotificationManager = window.NotificationManager || NotificationManager;
