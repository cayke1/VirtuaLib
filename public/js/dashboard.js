// Funções de API consolidadas
async function fetchJson(url, opts = {}) {
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.statusText}`);
    return res.json();
}

async function loadGeneralStats() {
    return fetchJson('/dashboard/api/stats/general');
}

async function loadBorrowsByMonth() {
    return fetchJson('/dashboard/api/stats/borrows-by-month');
}

async function loadTopBooks() {
    return fetchJson('/dashboard/api/stats/top-books');
}

async function loadBooksByCategory() {
    return fetchJson('/dashboard/api/stats/books-by-category');
}

async function loadRecentActivities() {
    return fetchJson('/dashboard/api/stats/recent-activities');
}

async function loadUserProfile() {
    return fetchJson('/dashboard/api/stats/user-profile');
}

async function loadFallbackStats() {
    return fetchJson('/dashboard/api/stats/fallback');
}

async function loadPendingRequests() {
    return fetchJson('/dashboard/api/pending-requests?limit=20');
}

class DashboardStats {
    constructor() {
        this.init();
    }

    async init() {
        try {
            await this.loadAllStats();
            this.setupRefreshInterval();
        } catch (error) {
            console.error('Erro ao inicializar dashboard:', error);
        }
    }

    async loadAllStats() {
        const promises = [
            this.loadGeneralStats(),
            this.loadBorrowsByMonth(),
            this.loadTopBooks(),
            this.loadBooksByCategory(),
            this.loadRecentActivities(),
            this.loadPendingRequests()
        ];

        await Promise.allSettled(promises);
    }

    async loadGeneralStats() {
        try {
            const data = await loadGeneralStats();
            this.updateGeneralStats(data.stats);
        } catch (error) {
            console.error('Erro ao carregar estatísticas gerais:', error);
            this.showError('Erro ao carregar estatísticas gerais');
            // Tentar carregar dados de fallback
            const fallback = await loadFallbackStats();
            this.updateGeneralStats(fallback.stats);
        }
    }

    async loadBorrowsByMonth() {
        try {
            const data = await loadBorrowsByMonth();
            this.updateBorrowsByMonthChart(data.data);
        } catch (error) {
            console.error('Erro ao carregar empréstimos por mês:', error);
        }
    }

    async loadTopBooks() {
        try {
            const data = await loadTopBooks();
            this.updateTopBooks(data.books);
        } catch (error) {
            console.error('Erro ao carregar top livros:', error);
        }
    }

    async loadBooksByCategory() {
        try {
            const data = await loadBooksByCategory();
            this.updateBooksByCategoryChart(data.categories);
        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
        }
    }

    async loadRecentActivities() {
        try {
            const data = await loadRecentActivities();
            this.updateRecentActivities(data.activities);
        } catch (error) {
            console.error('Erro ao carregar atividades recentes:', error);
        }
    }

    async loadPendingRequests() {
        try {
            const data = await loadPendingRequests();
            this.updatePendingRequests(data.requests);
        } catch (error) {
            console.error('Erro ao carregar solicitações pendentes:', error);
        }
    }

    updateGeneralStats(stats) {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            const statKeys = ['total_livros', 'livros_emprestados', 'usuarios_ativos', 'solicitacoes_pendentes'];
            const statKey = statKeys[index];
            
            if (stats[statKey]) {
                const statValue = card.querySelector('.stat-value');
                const statDesc = card.querySelector('.stat-desc');
                
                if (statValue) statValue.textContent = stats[statKey].valor;
                if (statDesc) statDesc.textContent = stats[statKey].descricao;
            }
        });
    }

    updateBorrowsByMonthChart(data) {
        const chartContainer = document.querySelector('.bar-chart');
        if (!chartContainer || !data || data.length === 0) return;

        // Converter array de objetos para objeto com chave-valor
        const chartData = {};
        data.forEach(item => {
            const monthKey = item.month;
            chartData[monthKey] = parseInt(item.total_borrows);
        });

        const maxValue = Math.max(...Object.values(chartData));
        
        chartContainer.innerHTML = '';
        
        Object.entries(chartData).forEach(([month, value]) => {
            const barContainer = document.createElement('div');
            barContainer.className = 'bar-container';
            
            const height = (value / maxValue) * 100;
            
            barContainer.innerHTML = `
                <div class="bar" style="height: ${height}%">
                    <span class="bar-value">${value}</span>
                </div>
                <span class="bar-label">${month}</span>
            `;
            
            chartContainer.appendChild(barContainer);
        });
    }

    updateTopBooks(books) {
        const topBooksContainer = document.querySelector('.top-books');
        if (!topBooksContainer || !books) return;

        topBooksContainer.innerHTML = '';
        
        books.forEach((book, index) => {
            const bookItem = document.createElement('div');
            bookItem.className = 'book-item';
            
            bookItem.innerHTML = `
                <span class="book-rank">${index + 1}</span>
                <div class="book-info">
                    <p class="book-title">${book.title}</p>
                    <p class="book-author">${book.author}</p>
                </div>
                <span class="book-count">${book.borrow_count}x</span>
            `;
            
            topBooksContainer.appendChild(bookItem);
        });
    }

    updateBooksByCategoryChart(categories) {
        const pieChartContainer = document.querySelector('.pie-chart-container');
        if (!pieChartContainer || !categories) return;

        const svg = pieChartContainer.querySelector('.pie-chart');
        const legend = pieChartContainer.querySelector('.pie-legend');
        
        if (!svg || !legend) return;

        svg.innerHTML = '';
        legend.innerHTML = '';

        let startAngle = 0;
        categories.forEach(category => {
            const angle = (category.percentual / 100) * 360;
            const endAngle = startAngle + angle;
            
            // Criar path do SVG
            const x1 = 100 + 80 * Math.cos((startAngle - 90) * Math.PI / 180);
            const y1 = 100 + 80 * Math.sin((startAngle - 90) * Math.PI / 180);
            const x2 = 100 + 80 * Math.cos((endAngle - 90) * Math.PI / 180);
            const y2 = 100 + 80 * Math.sin((endAngle - 90) * Math.PI / 180);
            
            const largeArc = angle > 180 ? 1 : 0;
            
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', `M 100 100 L ${x1} ${y1} A 80 80 0 ${largeArc} 1 ${x2} ${y2} Z`);
            path.setAttribute('fill', category.color);
            
            svg.appendChild(path);
            
            // Adicionar à legenda
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `
                <span class="legend-color" style="background: ${category.color}"></span>
                <span class="legend-label">${category.nome} (${category.percentual}%)</span>
            `;
            legend.appendChild(legendItem);
            
            startAngle = endAngle;
        });
    }

    updateRecentActivities(activities) {
        const activitiesContainer = document.querySelector('.activities');
        if (!activitiesContainer || !activities) return;

        // Limpar atividades existentes
        activitiesContainer.innerHTML = '';
        
        // Criar nova lista de atividades
        activities.forEach(activity => {
            const activityItem = document.createElement('div');
            activityItem.className = 'activity-item';
            
            // Determinar tipo de atividade e cor baseado no status
            let activityText = '';
            let activityDetail = '';
            let color = '#3b82f6';
            
            switch(activity.status) {
                case 'pending':
                    activityText = 'Solicitação de empréstimo';
                    activityDetail = `${activity.book_title} - ${activity.user_name}`;
                    color = '#f59e0b';
                    break;
                case 'approved':
                case 'borrowed':
                    activityText = 'Livro emprestado';
                    activityDetail = `${activity.book_title} - ${activity.user_name}`;
                    color = '#10b981';
                    break;
                case 'returned':
                    activityText = 'Livro devolvido';
                    activityDetail = `${activity.book_title} - ${activity.user_name}`;
                    color = '#6366f1';
                    break;
                default:
                    activityText = 'Atividade no sistema';
                    activityDetail = `${activity.book_title} - ${activity.user_name}`;
                    color = '#6b7280';
            }
            
            activityItem.innerHTML = `
                <span class="activity-dot" style="background: ${color}"></span>
                <div class="activity-info">
                    <p class="activity-text">${activityText}</p>
                    <p class="activity-detail">${activityDetail}</p>
                </div>
            `;
            
            activitiesContainer.appendChild(activityItem);
        });
    }

    updatePendingRequests(requests) {
        const section = document.querySelector('#pending-requests-section');
        const grid = document.querySelector('#requests-grid');
        const count = document.querySelector('#request-count');
        
        if (!section || !grid || !count) return;

        if (!requests || requests.length === 0) {
            section.style.display = 'none';
            return;
        }

        // Mostrar seção
        section.style.display = 'block';
        
        // Atualizar contador
        count.textContent = `${requests.length} solicitação(ões)`;
        
        // Limpar grid
        grid.innerHTML = '';
        
        // Criar cards das solicitações
        requests.forEach(request => {
            const requestCard = document.createElement('div');
            requestCard.className = 'request-card';
            requestCard.setAttribute('data-request-id', request.id);
            
            const timeAgo = this.formatRequestDate(request.requested_at);
            
            requestCard.innerHTML = `
                <div class="request-info">
                    <div class="request-user">
                        <span class="user-name">${request.user_name}</span>
                        <span class="user-email">${request.user_email}</span>
                    </div>
                    <div class="request-book">
                        <h4>${request.book_title}</h4>
                        <p>${request.book_author}</p>
                    </div>
                    <div class="request-time">
                        <span class="time-badge">${timeAgo}</span>
                    </div>
                </div>
                <div class="request-actions">
                    <button class="approve-btn" onclick="approveRequest(${request.id})">
                        ✅ Aprovar
                    </button>
                    <button class="reject-btn" onclick="rejectRequest(${request.id})">
                        ❌ Rejeitar
                    </button>
                </div>
            `;
            
            grid.appendChild(requestCard);
        });
    }

    formatRequestDate(dateString) {
        if (!dateString) return '—';
        
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor(diffMs / (1000 * 60));
            
            if (diffDays > 0) {
                return `${diffDays} dia${diffDays > 1 ? 's' : ''} atrás`;
            } else if (diffHours > 0) {
                return `${diffHours} hora${diffHours > 1 ? 's' : ''} atrás`;
            } else {
                return `${diffMinutes} minuto${diffMinutes > 1 ? 's' : ''} atrás`;
            }
        } catch (error) {
            return '—';
        }
    }

    setupRefreshInterval() {
        // Atualizar dados a cada 5 minutos
        setInterval(() => {
            this.loadAllStats();
        }, 5 * 60 * 1000);
    }

    showError(message) {
        // Implementar notificação de erro se necessário
        console.warn(message);
    }

    // Métodos de aprovação/rejeição removidos - não são responsabilidade do serviço de dashboard
}

// Funções globais para aprovação/rejeição de empréstimos
window.approveRequest = async function(requestId) {
    try {
        const response = await fetch(`/dashboard/api/approve/${requestId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Solicitação aprovada com sucesso!', 'success');
            // Remover o card da solicitação
            const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
            if (requestCard) {
                requestCard.remove();
            }
            // Atualizar contador
            updateRequestCount();
        } else {
            showToast(result.message || 'Erro ao aprovar solicitação', 'error');
        }
    } catch (error) {
        console.error('Erro ao aprovar solicitação:', error);
        showToast('Erro ao aprovar solicitação', 'error');
    }
};

window.rejectRequest = async function(requestId) {
    if (!confirm('Tem certeza que deseja rejeitar esta solicitação?')) {
        return;
    }
    
    try {
        const response = await fetch(`/dashboard/api/reject/${requestId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Solicitação rejeitada com sucesso!', 'success');
            // Remover o card da solicitação
            const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
            if (requestCard) {
                requestCard.remove();
            }
            // Atualizar contador
            updateRequestCount();
        } else {
            showToast(result.message || 'Erro ao rejeitar solicitação', 'error');
        }
    } catch (error) {
        console.error('Erro ao rejeitar solicitação:', error);
        showToast('Erro ao rejeitar solicitação', 'error');
    }
};

window.updateRequestCount = function() {
    const requestCount = document.querySelector('#request-count');
    const requestCards = document.querySelectorAll('.request-card');
    
    if (requestCount) {
        const count = requestCards.length;
        requestCount.textContent = `${count} solicitação(ões)`;
        
        // Se não há mais solicitações, esconder a seção
        if (count === 0) {
            const section = document.querySelector('#pending-requests-section');
            if (section) {
                section.style.display = 'none';
            }
        }
    }
}

window.showToast = function(message, type = 'info') {
    // Implementação simples de toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Adicionar estilos básicos
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `;
    
    // Cores baseadas no tipo
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6'
    };
    
    toast.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(toast);
    
    // Remover após 3 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Adicionar estilos CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/dashboard')) {
        new DashboardStats();
    }
});
