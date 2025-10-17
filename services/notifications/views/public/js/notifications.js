/**
 * Notifications Service JavaScript
 */

// Função para marcar notificação como lida
function markAsRead(notificationId) {
    fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // Atualizar UI
            const notification = document.querySelector(`[data-id="${notificationId}"]`);
            if (notification) {
                notification.classList.remove('notification-unread');
                notification.classList.add('notification-read');
            }
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Função para deletar notificação
function deleteNotification(notificationId) {
    if (confirm('Tem certeza que deseja deletar esta notificação?')) {
        fetch(`/api/notifications/${notificationId}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Remover elemento da UI
                const notification = document.querySelector(`[data-id="${notificationId}"]`);
                if (notification) {
                    notification.remove();
                }
                updateUnreadCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Função para marcar todas como lidas
function markAllAsRead() {
    fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // Atualizar todas as notificações
            document.querySelectorAll('.notification-unread').forEach(notification => {
                notification.classList.remove('notification-unread');
                notification.classList.add('notification-read');
            });
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Função para atualizar contador de não lidas
function updateUnreadCount() {
    fetch('/api/notifications/unread-count')
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('.unread-badge');
        if (data.unread > 0) {
            if (badge) {
                badge.textContent = data.unread;
            } else {
                // Criar badge se não existir
                const title = document.querySelector('.notification-title');
                if (title) {
                    title.innerHTML += `<span class="unread-badge">${data.unread}</span>`;
                }
            }
        } else {
            if (badge) {
                badge.remove();
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadCount();
    
    // Adicionar event listeners para botões
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            markAsRead(notificationId);
        });
    });
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            deleteNotification(notificationId);
        });
    });
    
    const markAllBtn = document.querySelector('.mark-all-read-btn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', markAllAsRead);
    }
});
