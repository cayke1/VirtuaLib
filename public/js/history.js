// Carrega e renderiza o histórico de empréstimos via API

document.addEventListener('DOMContentLoaded', async () => {
    const tableBody = document.querySelector('tbody');
    if (!tableBody) return;

    // Mensagem de carregamento
    tableBody.innerHTML = `<tr><td colspan="5" class="no-data">Carregando histórico...</td></tr>`;

    try {
        const res = await fetch('/api/stats/history');
        if (!res.ok) throw new Error('Erro ao buscar histórico');
        const data = await res.json();
        const history = data.history || [];

        if (history.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="no-data">Nenhum empréstimo encontrado</td></tr>`;
            return;
        }

        // Status aliases e configs (espelhando o PHP)
        const statusAliases = {
            'pending': 'Pendente',
            'approved': 'Aprovado',
            'returned': 'Devolvido',
            'late': 'Atrasado',
        };
        const statusConfig = {
            'Pendente': { icon: '⏳', text: 'Pendente', class: 'status-pendente' },
            'Aprovado': { icon: '📖', text: 'Emprestado', class: 'status-ativo' },
            'Devolvido': { icon: '✓', text: 'Devolvido', class: 'status-devolvido' },
            'Atrasado': { icon: '⚠', text: 'Atrasado', class: 'status-atrasado' }
        };

        tableBody.innerHTML = '';
        history.forEach(loan => {
            const rawStatus = loan.status || 'Emprestado';
            const normalizedKey = String(rawStatus).toLowerCase();
            const normalizedStatus = statusAliases[normalizedKey] || rawStatus;
            const status = statusConfig[normalizedStatus] || statusConfig['Aprovado'];

            tableBody.innerHTML += `
                <tr>
                    <td>${loan.user_name || ''}</td>
                    <td>${loan.book_title || ''}</td>
                    <td>${formatHistoryDate(loan.requested_at)}</td>
                    <td>${formatHistoryDate(loan.returned_at)}</td>
                    <td><span class="status ${status.class}">${status.icon} ${status.text}</span></td>
                </tr>
            `;
        });
    } catch (e) {
        tableBody.innerHTML = `<tr><td colspan="5" class="no-data">Erro ao carregar histórico</td></tr>`;
    }
});

function formatHistoryDate(value, format = 'd/m/Y') {
    if (!value) return '—';
    const date = new Date(value);
    if (isNaN(date)) return value;
    // Formato dd/mm/yyyy
    return date.toLocaleDateString('pt-BR');
}
