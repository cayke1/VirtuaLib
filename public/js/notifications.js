class NotificationManager {
    constructor() {
        this.isLoading = false;
        this.notifications = [];
        this.unreadCount = 0;

        this.init();
    }

    /* ========================
     * 🧩 Inicialização
     * ======================== */
    init() {
        console.log('🔔 NotificationManager: Inicializando...');
        console.log('Usuário logado:', window.AuthService?.currentUser);

        this.setupEventListeners();
        this.loadUnreadCount();
        this.startPolling();

        console.log('✅ NotificationManager: Inicializado com sucesso');
    }

    setupEventListeners() {
        console.log('🧠 Configurando listeners...');

        // Desktop
        const notificationBtn = document.getElementById('notification-btn');
        const markAllReadBtn = document.getElementById('mark-all-read-btn');

        if (notificationBtn)
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown('desktop');
            });

        if (markAllReadBtn)
            markAllReadBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.markAllAsRead();
            });

        // Mobile
        const notificationBtnMobile = document.getElementById('notification-btn-mobile');
        const markAllReadBtnMobile = document.getElementById('mark-all-read-btn-mobile');

        if (notificationBtnMobile)
            notificationBtnMobile.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown('mobile');
            });

        if (markAllReadBtnMobile)
            markAllReadBtnMobile.addEventListener('click', (e) => {
                e.stopPropagation();
                this.markAllAsRead();
            });

        // Fechar dropdowns clicando fora
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

        // Botão voltar no mobile
        const mobileHeader = document.querySelector('.notification-header-mobile');
        if (mobileHeader) {
            mobileHeader.addEventListener('click', (e) => {
                if (['DIV', 'H3'].includes(e.target.tagName)) {
                    this.closeDropdown('mobile');
                }
            });
        }
    }

    /* ========================
     * 📡 Requisições
     * ======================== */
    async loadUnreadCount() {
        try {
            const url = '/notifications/api/notifications/unread-count';
            const response = await fetch(url, { credentials: 'same-origin' });

            if (!response.ok) throw new Error(await response.text());

            const data = await response.json();
            this.unreadCount = data.unread || 0;
            this.updateBadges();
        } catch (error) {
            console.error('Erro ao carregar contagem de não lidas:', error);
        }
    }

    async loadNotifications() {
        if (this.isLoading) return;
        this.isLoading = true;
        this.showLoading(true);

        try {
            const response = await fetch('/notifications/api/notifications', { credentials: 'same-origin' });

            if (!response.ok) throw new Error(await response.text());

            const data = await response.json();
            this.notifications = data.notifications || [];
            this.renderNotifications();
            this.updateUnreadCount();
        } catch (error) {
            console.error('Erro ao carregar notificações:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/api/notifications/${notificationId}/read`, {
                method: 'POST',
                credentials: 'same-origin'
            });
            if (!response.ok) return;

            const notification = this.notifications.find(n => n.id == notificationId);
            if (notification) notification.is_read = 1;

            this.updateUnreadCount();
        } catch (error) {
            console.error('Erro ao marcar como lida:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/api/notifications/mark-all-read', {
                method: 'POST',
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            this.notifications.forEach(n => (n.is_read = 1));
            this.updateUnreadCount();
            this.renderNotifications();
        } catch (error) {
            console.error('Erro ao marcar todas como lidas:', error);
        }
    }

    /* ========================
     * 🖼️ Renderização
     * ======================== */
    renderNotifications() {
        const hasNotifications = this.notifications.length > 0;
        this.showEmpty(!hasNotifications);

        if (!hasNotifications) return;

        const desktopList = document.getElementById('notification-list');
        const mobileList = document.getElementById('notification-list-mobile');

        const render = (type) =>
            this.notifications.map(n => this.createNotificationHTML(n, type)).join('');

        if (desktopList) desktopList.innerHTML = render('desktop');
        if (mobileList) mobileList.innerHTML = render('mobile');

        this.setupNotificationClickListeners();
    }

    createNotificationHTML(notification, type) {
        const isUnread = !notification.is_read;
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const prefix = type === 'mobile' ? '-mobile' : '';
        const notificationType = this.parseNotificationType(notification.data);

        const icons = {
            requested: 'fas fa-clock',
            approved: 'fas fa-check',
            rejected: 'fas fa-times',
            borrowed: 'fas fa-book',
            returned: 'fas fa-undo',
            default: 'fas fa-bell'
        };

        return `
            <div class="notification-item${prefix} ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="notification-icon${prefix} ${notificationType}">
                    <i class="${icons[notificationType] || icons.default}"></i>
                </div>
                <div class="notification-content${prefix}">
                    <div class="notification-title${prefix}">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message${prefix}">${this.escapeHtml(notification.message)}</div>
                    <div class="notification-time${prefix}">${timeAgo}</div>
                </div>
            </div>
        `;
    }

    setupNotificationClickListeners() {
        document.querySelectorAll('.notification-item, .notification-item-mobile').forEach(item => {
            item.addEventListener('click', () => {
                this.markAsRead(item.dataset.id);
                item.classList.remove('unread');
            });
        });
    }

    /* ========================
     * 🧮 Atualizações de estado
     * ======================== */
    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        this.updateBadges();
    }

    updateBadges() {
        const count = this.unreadCount;
        const text = count > 99 ? '99+' : count;
        const desktopBadge = document.getElementById('notification-badge');
        const mobileBadge = document.getElementById('notification-badge-mobile');

        [desktopBadge, mobileBadge].forEach(badge => {
            if (!badge) return;
            if (count > 0) {
                badge.textContent = text;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    /* ========================
     * 🪄 Utilitários visuais
     * ======================== */
    toggleDropdown(type) {
        const dropdown = document.getElementById(`notification-dropdown${type === 'mobile' ? '-mobile' : ''}`);
        if (!dropdown) return;

        dropdown.classList.toggle('show');
        if (dropdown.classList.contains('show')) this.loadNotifications();
    }

    closeDropdown(type) {
        const dropdown = document.getElementById(`notification-dropdown${type === 'mobile' ? '-mobile' : ''}`);
        if (dropdown) dropdown.classList.remove('show');
    }

    showLoading(show) {
        const selectors = ['.notification-loading', '.notification-loading-mobile'];
        selectors.forEach(sel => {
            const el = document.querySelector(sel);
            if (el) el.style.display = show ? 'block' : 'none';
        });
    }

    showEmpty(show) {
        const pairs = [
            ['notification-empty', 'notification-list'],
            ['notification-empty-mobile', 'notification-list-mobile']
        ];

        pairs.forEach(([emptyId, listId]) => {
            const empty = document.getElementById(emptyId);
            const list = document.getElementById(listId);
            if (empty) empty.style.display = show ? 'block' : 'none';
            if (list) list.style.display = show ? 'none' : 'block';
        });
    }

    showError() {
        const template = `
            <div class="notification-loading">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Erro ao carregar notificações</span>
            </div>
        `;
        const desktopList = document.getElementById('notification-list');
        const mobileList = document.getElementById('notification-list-mobile');

        if (desktopList) desktopList.innerHTML = template;
        if (mobileList) mobileList.innerHTML = template;
    }

    /* ========================
     * 🧰 Funções auxiliares
     * ======================== */
    parseNotificationType(data) {
        try {
            return JSON.parse(data)?.type || 'default';
        } catch {
            return 'default';
        }
    }

    formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return 'Agora mesmo';
        if (diff < 3600) return `Há ${Math.floor(diff / 60)} minuto${diff > 120 ? 's' : ''}`;
        if (diff < 86400) return `Há ${Math.floor(diff / 3600)} hora${diff > 7200 ? 's' : ''}`;
        if (diff < 2592000) return `Há ${Math.floor(diff / 86400)} dia${diff > 172800 ? 's' : ''}`;
        return date.toLocaleDateString('pt-BR');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    startPolling() {
        setInterval(() => this.loadUnreadCount(), 30000);
    }

    refresh() {
        this.loadUnreadCount();
        const openDropdowns = ['notification-dropdown', 'notification-dropdown-mobile']
            .map(id => document.getElementById(id))
            .filter(el => el?.classList.contains('show'));

        if (openDropdowns.length > 0) this.loadNotifications();
    }
}

/* ========================
 * 🚀 Inicialização global
 * ======================== */
document.addEventListener('DOMContentLoaded', () => {
    const initNotifications = async () => {
        if (window.AuthService) {
            await new Promise(r => setTimeout(r, 500));
            window.NotificationManager = new NotificationManager();
        } else {
            setTimeout(initNotifications, 100);
        }
    };

    initNotifications();
});

// Exporta globalmente
window.NotificationManager = window.NotificationManager || NotificationManager;
