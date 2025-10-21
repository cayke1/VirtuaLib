class NotificationManager {
    constructor() {
        this.isLoading = false;
        this.notifications = [];
        this.unreadCount = 0;
        
        this.init();
    }

    init() {
        console.log('NotificationManager: Initializing...');
        console.log('NotificationManager: Session user:', window.AuthService?.currentUser);
        this.setupEventListeners();
        this.loadUnreadCount();
        this.startPolling();
        console.log('NotificationManager: Initialized successfully');
    }

    setupEventListeners() {
        console.log('NotificationManager: Setting up event listeners...');
        // Desktop
        const notificationBtn = document.getElementById('notification-btn');
        const markAllReadBtn = document.getElementById('mark-all-read-btn');
        
        console.log('NotificationManager: Desktop notification button found:', !!notificationBtn);
        console.log('NotificationManager: Desktop mark all read button found:', !!markAllReadBtn);
        
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
        
        console.log('NotificationManager: Mobile notification button found:', !!notificationBtnMobile);
        console.log('NotificationManager: Mobile mark all read button found:', !!markAllReadBtnMobile);
        
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
            console.log('NotificationManager: Loading unread count...');
            console.log('NotificationManager: Making request to:', '/notifications/api/notifications/unread-count');
            console.log('NotificationManager: Current URL:', window.location.href);
            console.log('NotificationManager: Session cookie:', document.cookie);
            
            const response = await fetch('/notifications/api/notifications/unread-count', {
                credentials: 'same-origin' // Incluir cookies de sessão
            });
            console.log('NotificationManager: Response status:', response.status);
            console.log('NotificationManager: Response headers:', Object.fromEntries(response.headers.entries()));
            
            if (response.ok) {
                const data = await response.json();
                console.log('NotificationManager: Unread count data:', data);
                this.unreadCount = data.unread || 0;
                this.updateBadges();
            } else {
                const errorText = await response.text();
                console.error('NotificationManager: Failed to load unread count:', response.status, response.statusText, errorText);
            }
        } catch (error) {
            console.error('NotificationManager: Error loading unread count:', error);
        }
    }

    async loadNotifications() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);

        try {
            console.log('NotificationManager: Loading notifications...');
            console.log('NotificationManager: Making request to:', '/notifications/api/notifications');
            const response = await fetch('/notifications/api/notifications', {
                credentials: 'same-origin' // Incluir cookies de sessão
            });
            console.log('NotificationManager: Notifications response status:', response.status);
            console.log('NotificationManager: Response headers:', Object.fromEntries(response.headers.entries()));
            
            if (response.ok) {
                const data = await response.json();
                console.log('NotificationManager: Notifications data:', data);
                this.notifications = data.notifications || [];
                this.renderNotifications();
                this.updateUnreadCount();
            } else {
                const errorText = await response.text();
                console.error('NotificationManager: Failed to load notifications:', response.status, response.statusText, errorText);
                this.showError();
            }
        } catch (error) {
            console.error('NotificationManager: Error loading notifications:', error);
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
        const iconClass = type === 'mobile' ? 'notification-icon-mobile' : 'notification-icon';
        const contentClass = type === 'mobile' ? 'notification-content-mobile' : 'notification-content';

        // Parse notification data to get type
        let notificationType = 'default';
        let icon = 'fas fa-bell';
        
        try {
            const data = notification.data ? JSON.parse(notification.data) : {};
            notificationType = data.type || 'default';
        } catch (e) {
            // If parsing fails, use default
        }

        // Set icon based on type
        switch (notificationType) {
            case 'requested':
                icon = 'fas fa-clock';
                break;
            case 'approved':
                icon = 'fas fa-check';
                break;
            case 'rejected':
                icon = 'fas fa-times';
                break;
            case 'borrowed':
                icon = 'fas fa-book';
                break;
            case 'returned':
                icon = 'fas fa-undo';
                break;
            default:
                icon = 'fas fa-bell';
        }

        return `
            <div class="${itemClass} ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="${iconClass} ${notificationType}">
                    <i class="${icon}"></i>
                </div>
                <div class="${contentClass}">
                    <div class="${titleClass}">${this.escapeHtml(notification.title)}</div>
                    <div class="${messageClass}">${this.escapeHtml(notification.message)}</div>
                    <div class="${timeClass}">${timeAgo}</div>
                </div>
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
            const response = await fetch(`/notifications/api/notifications/${notificationId}/read`, {
                method: 'POST',
                credentials: 'same-origin'
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
            const response = await fetch('/notifications/api/notifications/mark-all-read', {
                method: 'POST',
                credentials: 'same-origin'
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
        console.log('NotificationManager: Updating badges, unread count:', this.unreadCount);
        const desktopBadge = document.getElementById('notification-badge');
        const mobileBadge = document.getElementById('notification-badge-mobile');

        console.log('NotificationManager: Desktop badge found:', !!desktopBadge);
        console.log('NotificationManager: Mobile badge found:', !!mobileBadge);

        if (desktopBadge) {
            if (this.unreadCount > 0) {
                desktopBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                desktopBadge.classList.remove('hidden');
                console.log('NotificationManager: Desktop badge updated to:', desktopBadge.textContent);
            } else {
                desktopBadge.classList.add('hidden');
                console.log('NotificationManager: Desktop badge hidden');
            }
        }

        if (mobileBadge) {
            if (this.unreadCount > 0) {
                mobileBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                mobileBadge.classList.remove('hidden');
                console.log('NotificationManager: Mobile badge updated to:', mobileBadge.textContent);
            } else {
                mobileBadge.classList.add('hidden');
                console.log('NotificationManager: Mobile badge hidden');
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

// Initialize when DOM is loaded and AuthService is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait for AuthService to be ready and initialized
    const initNotifications = async () => {
        if (window.AuthService) {
            console.log('NotificationManager: AuthService is ready, waiting for initialization...');
            
            // Wait a bit for AuthService to complete its async init
            await new Promise(resolve => setTimeout(resolve, 500));
            
            console.log('NotificationManager: Initializing NotificationManager...');
            window.NotificationManager = new NotificationManager();
        } else {
            console.log('NotificationManager: AuthService not ready, retrying in 100ms...');
            setTimeout(initNotifications, 100);
        }
    };
    
    initNotifications();
});

// Export for use in other scripts
window.NotificationManager = window.NotificationManager || NotificationManager;
