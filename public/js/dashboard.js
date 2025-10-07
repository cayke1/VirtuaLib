class DashboardStats {
    constructor() {
        this.init();
    }

    async init() {
        try {
            await this.loadAllStats();
            this.setupRefreshInterval();
            this.setupRequestHandlers();
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
            this.loadRecentActivities()
        ];

        await Promise.allSettled(promises);
    }

    async loadGeneralStats() {
        try {
            const response = await fetch('/api/stats/general');
            if (!response.ok) throw new Error('Erro ao carregar estatísticas gerais');
            
            const data = await response.json();
            this.updateGeneralStats(data.stats);
        } catch (error) {
            console.error('Erro ao carregar estatísticas gerais:', error);
            this.showError('Erro ao carregar estatísticas gerais');
        }
    }

    async loadBorrowsByMonth() {
        try {
            const response = await fetch('/api/stats/borrows-by-month');
            if (!response.ok) throw new Error('Erro ao carregar empréstimos por mês');
            
            const data = await response.json();
            this.updateBorrowsByMonthChart(data.data);
        } catch (error) {
            console.error('Erro ao carregar empréstimos por mês:', error);
        }
    }

    async loadTopBooks() {
        try {
            const response = await fetch('/api/stats/top-books');
            if (!response.ok) throw new Error('Erro ao carregar top livros');
            
            const data = await response.json();
            this.updateTopBooks(data.books);
        } catch (error) {
            console.error('Erro ao carregar top livros:', error);
        }
    }

    async loadBooksByCategory() {
        try {
            const response = await fetch('/api/stats/books-by-category');
            if (!response.ok) throw new Error('Erro ao carregar categorias');
            
            const data = await response.json();
            this.updateBooksByCategoryChart(data.categories);
        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
        }
    }

    async loadRecentActivities() {
        try {
            const response = await fetch('/api/stats/recent-activities');
            if (!response.ok) throw new Error('Erro ao carregar atividades recentes');
            
            const data = await response.json();
            this.updateRecentActivities(data.activities);
        } catch (error) {
            console.error('Erro ao carregar atividades recentes:', error);
        }
    }

    updateGeneralStats(stats) {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            const statKeys = ['total_livros', 'livros_emprestados', 'usuarios_ativos', 'emprestimos_hoje'];
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

    // Métodos para gerenciar solicitações pendentes
    setupRequestHandlers() {
        // Tornar as funções globais para uso nos botões onclick
        window.approveRequest = (requestId) => this.approveRequest(requestId);
        window.rejectRequest = (requestId) => this.rejectRequest(requestId);
    }

    async approveRequest(requestId) {
        if (!confirm('Tem certeza que deseja aprovar esta solicitação?')) {
            return;
        }

        try {
            const response = await fetch(`/api/approve/${requestId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showSuccessMessage('Solicitação aprovada com sucesso!');
                this.removeRequestCard(requestId);
            } else {
                this.showErrorMessage(result.message || 'Erro ao aprovar solicitação');
            }
        } catch (error) {
            this.showErrorMessage('Erro ao conectar com o servidor');
            console.error('Error:', error);
        }
    }

    async rejectRequest(requestId) {
        if (!confirm('Tem certeza que deseja rejeitar esta solicitação?')) {
            return;
        }

        try {
            const response = await fetch(`/api/reject/${requestId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showSuccessMessage('Solicitação rejeitada com sucesso!');
                this.removeRequestCard(requestId);
            } else {
                this.showErrorMessage(result.message || 'Erro ao rejeitar solicitação');
            }
        } catch (error) {
            this.showErrorMessage('Erro ao conectar com o servidor');
            console.error('Error:', error);
        }
    }

    removeRequestCard(requestId) {
        const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
        if (requestCard) {
            requestCard.remove();
            this.updateRequestCount();
        }
    }

    updateRequestCount() {
        const requestCount = document.querySelector('.request-count');
        if (requestCount) {
            const remainingRequests = document.querySelectorAll('.request-card').length;
            requestCount.textContent = `${remainingRequests} solicitação(ões)`;
        }
    }

    showSuccessMessage(message) {
        // Implementar notificação de sucesso
        alert(message); // Por enquanto usando alert, pode ser melhorado com toast notifications
    }

    showErrorMessage(message) {
        // Implementar notificação de erro
        alert(message); // Por enquanto usando alert, pode ser melhorado com toast notifications
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/dashboard')) {
        new DashboardStats();
    }
});
