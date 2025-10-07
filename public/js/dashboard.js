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
        if (!chartContainer || !data) return;

        const maxValue = Math.max(...Object.values(data));
        
        chartContainer.innerHTML = '';
        
        Object.entries(data).forEach(([month, value]) => {
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
                    <p class="book-title">${book.titulo}</p>
                    <p class="book-author">${book.autor}</p>
                </div>
                <span class="book-count">${book.emprestimos}x</span>
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
            
            activityItem.innerHTML = `
                <span class="activity-dot" style="background: ${activity.color}"></span>
                <div class="activity-info">
                    <p class="activity-text">${activity.texto}</p>
                    <p class="activity-detail">${activity.detalhe}</p>
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
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/dashboard')) {
        new DashboardStats();
    }
});
