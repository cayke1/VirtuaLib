class NotificationManager {
  constructor() {
    this.isLoading = false;
    this.notifications = [];
    this.unreadCount = 0;
    this.userId = window.AuthService?.currentUser?.id || null;

    this.selectors = {
      dropdown: {
        desktop: 'notification-dropdown',
        mobile: 'notification-dropdown-mobile'
      },
      list: {
        desktop: 'notification-list',
        mobile: 'notification-list-mobile'
      },
      badge: {
        desktop: 'notification-badge',
        mobile: 'notification-badge-mobile'
      },
      empty: {
        desktop: 'notification-empty',
        mobile: 'notification-empty-mobile'
      },
      loading: {
        desktop: '.notification-loading',
        mobile: '.notification-loading-mobile'
      }
    };

    this.init();
  }

  /* ========================
   * 🧩 Inicialização
   * ======================== */
  init() {
    console.log('🔔 NotificationManager inicializado');
    this.setupListeners();
    this.loadUnreadCount();
    this.startPolling();
  }

  setupListeners() {
    const bindClick = (id, fn) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('click', e => { e.stopPropagation(); fn(); });
    };

    // Botões principais
    bindClick('notification-btn', () => this.toggleDropdown('desktop'));
    bindClick('notification-btn-mobile', () => this.toggleDropdown('mobile'));
    bindClick('mark-all-read-btn', () => this.markAllAsRead());
    bindClick('mark-all-read-btn-mobile', () => this.markAllAsRead());

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', e => {
      ['desktop', 'mobile'].forEach(type => {
        const dropdown = document.getElementById(this.selectors.dropdown[type]);
        const btn = document.getElementById(`notification-btn${type === 'mobile' ? '-mobile' : ''}`);
        if (dropdown && !dropdown.contains(e.target) && !btn?.contains(e.target)) this.closeDropdown(type);
      });
    });

    // Cabeçalho mobile fecha dropdown
    const mobileHeader = document.querySelector('.notification-header-mobile');
    if (mobileHeader) {
      mobileHeader.addEventListener('click', e => {
        if (['DIV', 'H3'].includes(e.target.tagName)) this.closeDropdown('mobile');
      });
    }
  }

  /* ========================
   * 📡 Requisições
   * ======================== */
  async loadUnreadCount() {
    try {
      const res = await fetch('/notifications/api/notifications/unread-count', { credentials: 'same-origin' });
      if (!res.ok) throw new Error(await res.text());
      const data = await res.json();
      this.unreadCount = data.unread || 0;
      this.updateBadges();
    } catch (err) {
      console.error('Erro ao carregar contagem de não lidas:', err);
    }
  }

  async loadNotifications() {
    if (this.isLoading || !this.userId) return;
    this.isLoading = true;
    this.toggleLoading(true);

    try {
      const [notificationsData, overdueData] = await Promise.all([
        this.fetchJson('/notifications/api/notifications'),
        this.fetchJson(`/dashboard/api/overdue/user/${this.userId}`)
      ]);

      this.notifications = notificationsData.notifications || [];

      const overdueNotifications = (overdueData.overdue_borrows || []).map(b => ({
        id: `overdue-${b.id}`,
        title: 'Livro Atrasado',
        message: `O livro "${b.book_title}" está atrasado. Data de devolução: ${new Date(b.due_date).toLocaleDateString('pt-BR')}`,
        created_at: b.due_date,
        is_read: 0,
        data: JSON.stringify({ type: 'overdue', book_id: b.book_id, borrow_id: b.id, due_date: b.due_date })
      }));

      // Evita duplicatas
      const existing = new Set(
        this.notifications
          .map(n => { try { return JSON.parse(n.data)?.borrow_id; } catch { return null; } })
          .filter(Boolean)
      );

      this.notifications.push(...overdueNotifications.filter(n => {
        const id = JSON.parse(n.data)?.borrow_id;
        return !existing.has(id);
      }));

      // Ordenar por data
      this.notifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

      this.renderNotifications();
      this.updateUnreadCount();

    } catch (err) {
      console.error('Erro ao carregar notificações:', err);
      this.showError();
    } finally {
      this.isLoading = false;
      this.toggleLoading(false);
    }
  }

  async fetchJson(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`Falha ao carregar: ${url}`);
    return res.json();
  }

  async markAllAsRead() {
    try {
      const res = await fetch('/notifications/api/notifications/mark-all-read', {
        method: 'POST',
        credentials: 'same-origin'
      });
      if (!res.ok) return;
      this.notifications.forEach(n => (n.is_read = 1));
      this.updateUnreadCount();
      this.renderNotifications();
    } catch (err) {
      console.error('Erro ao marcar todas como lidas:', err);
    }
  }

  async markAsRead(id) {
    try {
      const res = await fetch(`/notifications/api/notifications/${id}/read`, {
        method: 'POST',
        credentials: 'same-origin'
      });
      if (res.ok) {
        const n = this.notifications.find(n => n.id == id);
        if (n) n.is_read = 1;
        this.updateUnreadCount();
      }
    } catch (err) {
      console.error('Erro ao marcar como lida:', err);
    }
  }

  /* ========================
   * 🖼️ Renderização
   * ======================== */
  renderNotifications() {
    const hasNotifications = this.notifications.length > 0;
    this.toggleEmpty(!hasNotifications);
    if (!hasNotifications) return;

    ['desktop', 'mobile'].forEach(type => {
      const list = document.getElementById(this.selectors.list[type]);
      if (list) list.innerHTML = this.notifications.map(n => this.createNotificationHTML(n, type)).join('');
    });

    this.bindNotificationClicks();
  }

  createNotificationHTML(notification, type) {
    const isUnread = !notification.is_read;
    const timeAgo = this.formatTimeAgo(notification.created_at);
    const notificationType = this.parseType(notification.data);
    const prefix = type === 'mobile' ? '-mobile' : '';

    const icons = {
      requested: 'fas fa-clock',
      approved: 'fas fa-check',
      rejected: 'fas fa-times',
      borrowed: 'fas fa-book',
      returned: 'fas fa-undo',
      overdue: 'fas fa-exclamation-circle',
      default: 'fas fa-bell'
    };

    return `
      <div class="notification-item${prefix} ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
        <div class="notification-icon${prefix} ${notificationType}">
          <i class="${icons[notificationType] || icons.default}"></i>
        </div>
        <div class="notification-content${prefix}">
          <div class="notification-title${prefix}">${this.escape(notification.title)}</div>
          <div class="notification-message${prefix}">${this.escape(notification.message)}</div>
          <div class="notification-time${prefix}">${timeAgo}</div>
        </div>
      </div>
    `;
  }

  bindNotificationClicks() {
    document.querySelectorAll('.notification-item, .notification-item-mobile').forEach(el => {
      el.addEventListener('click', () => {
        this.markAsRead(el.dataset.id);
        el.classList.remove('unread');
      });
    });
  }

  /* ========================
   * 🧮 Atualizações visuais
   * ======================== */
  updateUnreadCount() {
    this.unreadCount = this.notifications.filter(n => !n.is_read).length;
    this.updateBadges();
  }

  updateBadges() {
    const text = this.unreadCount > 99 ? '99+' : this.unreadCount;
    Object.values(this.selectors.badge).forEach(id => {
      const badge = document.getElementById(id);
      if (!badge) return;
      badge.textContent = text;
      badge.classList.toggle('hidden', this.unreadCount === 0);
    });
  }

  toggleDropdown(type) {
    const dropdown = document.getElementById(this.selectors.dropdown[type]);
    if (!dropdown) return;
    dropdown.classList.toggle('show');
    if (dropdown.classList.contains('show')) this.loadNotifications();
  }

  closeDropdown(type) {
    document.getElementById(this.selectors.dropdown[type])?.classList.remove('show');
  }

  toggleLoading(show) {
    Object.values(this.selectors.loading).forEach(sel => {
      const el = document.querySelector(sel);
      if (el) el.style.display = show ? 'block' : 'none';
    });
  }

  toggleEmpty(show) {
    Object.entries(this.selectors.empty).forEach(([type, id]) => {
      const empty = document.getElementById(id);
      const list = document.getElementById(this.selectors.list[type]);
      if (empty) empty.style.display = show ? 'block' : 'none';
      if (list) list.style.display = show ? 'none' : 'block';
    });
  }

  showError() {
    const html = `<div class="notification-loading">
      <i class="fas fa-exclamation-triangle"></i>
      <span>Erro ao carregar notificações</span>
    </div>`;
    Object.values(this.selectors.list).forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html;
    });
  }

  /* ========================
   * 🧰 Utilitários
   * ======================== */
  parseType(data) {
    try { return JSON.parse(data)?.type || 'default'; }
    catch { return 'default'; }
  }

  formatTimeAgo(dateStr) {
    const diff = (Date.now() - new Date(dateStr)) / 1000;
    if (diff < 60) return 'Agora mesmo';
    if (diff < 3600) return `Há ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Há ${Math.floor(diff / 3600)} h`;
    if (diff < 2592000) return `Há ${Math.floor(diff / 86400)} dia(s)`;
    return new Date(dateStr).toLocaleDateString('pt-BR');
  }

  escape(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  startPolling() {
    setInterval(() => this.loadUnreadCount(), 30000);
  }

  refresh() {
    this.loadUnreadCount();
    ['desktop', 'mobile'].forEach(type => {
      const el = document.getElementById(this.selectors.dropdown[type]);
      if (el?.classList.contains('show')) this.loadNotifications();
    });
  }
}

/* ========================
 * 🚀 Inicialização global
 * ======================== */
document.addEventListener('DOMContentLoaded', () => {
  const init = async () => {
    if (window.AuthService?.currentUser) {
      await new Promise(r => setTimeout(r, 300));
      window.NotificationManager = new NotificationManager();
    } else {
      setTimeout(init, 200);
    }
  };
  init();
});
