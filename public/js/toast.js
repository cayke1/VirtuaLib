/**
 * Sistema de Toast Notifications
 * Substitui os alerts simples por notificações visuais mais elegantes
 */

class ToastManager {
    constructor() {
        this.container = null;
        this.toasts = new Map();
        this.init();
    }

    init() {
        // Criar container se não existir
        if (!document.querySelector('.toast-container')) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.toast-container');
        }
    }

    /**
     * Mostra um toast de sucesso
     * @param {string} message - Mensagem a ser exibida
     * @param {string} title - Título opcional
     * @param {number} duration - Duração em ms (padrão: 4000)
     */
    success(message, title = 'Sucesso', duration = 4000) {
        return this.show('success', message, title, duration);
    }

    /**
     * Mostra um toast de erro
     * @param {string} message - Mensagem a ser exibida
     * @param {string} title - Título opcional
     * @param {number} duration - Duração em ms (padrão: 6000)
     */
    error(message, title = 'Erro', duration = 6000) {
        return this.show('error', message, title, duration);
    }

    /**
     * Mostra um toast de aviso
     * @param {string} message - Mensagem a ser exibida
     * @param {string} title - Título opcional
     * @param {number} duration - Duração em ms (padrão: 5000)
     */
    warning(message, title = 'Aviso', duration = 5000) {
        return this.show('warning', message, title, duration);
    }

    /**
     * Mostra um toast informativo
     * @param {string} message - Mensagem a ser exibida
     * @param {string} title - Título opcional
     * @param {number} duration - Duração em ms (padrão: 4000)
     */
    info(message, title = 'Informação', duration = 4000) {
        return this.show('info', message, title, duration);
    }

    /**
     * Mostra um toast
     * @param {string} type - Tipo do toast (success, error, warning, info)
     * @param {string} message - Mensagem a ser exibida
     * @param {string} title - Título opcional
     * @param {number} duration - Duração em ms (0 = não remove automaticamente)
     */
    show(type, message, title, duration = 4000) {
        const toastId = this.generateId();
        const toast = this.createToast(toastId, type, message, title);
        
        this.container.appendChild(toast);
        this.toasts.set(toastId, toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto dismiss
        if (duration > 0) {
            const progressBar = toast.querySelector('.toast-progress');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.style.transition = `width ${duration}ms linear`;
                progressBar.style.width = '0%';
            }

            setTimeout(() => {
                this.dismiss(toastId);
            }, duration);
        }

        return toastId;
    }

    /**
     * Remove um toast específico
     * @param {string} toastId - ID do toast
     */
    dismiss(toastId) {
        const toast = this.toasts.get(toastId);
        if (!toast) return;

        toast.classList.add('removing');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts.delete(toastId);
        }, 300);
    }

    /**
     * Remove todos os toasts
     */
    dismissAll() {
        this.toasts.forEach((toast, toastId) => {
            this.dismiss(toastId);
        });
    }

    /**
     * Cria o elemento HTML do toast
     * @param {string} id - ID único do toast
     * @param {string} type - Tipo do toast
     * @param {string} message - Mensagem
     * @param {string} title - Título
     */
    createToast(id, type, message, title) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.dataset.toastId = id;

        const icon = this.getIcon(type);
        const closeIcon = this.getCloseIcon();

        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${this.escapeHtml(title)}</div>
                <div class="toast-message">${this.escapeHtml(message)}</div>
            </div>
            <button class="toast-close" onclick="toastManager.dismiss('${id}')">
                ${closeIcon}
            </button>
            <div class="toast-progress"></div>
        `;

        return toast;
    }

    /**
     * Retorna o ícone baseado no tipo
     * @param {string} type - Tipo do toast
     */
    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || 'ℹ';
    }

    /**
     * Retorna o ícone de fechar
     */
    getCloseIcon() {
        return `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
    }

    /**
     * Escapa HTML para prevenir XSS
     * @param {string} text - Texto a ser escapado
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Gera um ID único
     */
    generateId() {
        return 'toast_' + Math.random().toString(36).substr(2, 9);
    }
}

// Instância global
const toastManager = new ToastManager();

// Funções de conveniência
function showToast(type, message, title, duration) {
    return toastManager.show(type, message, title, duration);
}

function showSuccess(message, title, duration) {
    return toastManager.success(message, title, duration);
}

function showError(message, title, duration) {
    return toastManager.error(message, title, duration);
}

function showWarning(message, title, duration) {
    return toastManager.warning(message, title, duration);
}

function showInfo(message, title, duration) {
    return toastManager.info(message, title, duration);
}

// Exportar para uso global
window.toastManager = toastManager;
window.showToast = showToast;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;
