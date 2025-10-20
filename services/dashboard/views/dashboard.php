<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VirtuaLib</title>
    <link rel="stylesheet" href="/public/css/dashboard.css">
    <link rel="stylesheet" href="/public/css/stats.css">
</head>
<body>
    <?php
    // Dados serão carregados via JavaScript das APIs

    // Dados das solicitações pendentes (passados pelo controller)
    $pendingRequests = $pendingRequests ?? [];
    $isAdmin = $isAdmin ?? false;

    function formatRequestDate(?string $value): string
    {
        if (!$value) {
            return '—';
        }

        try {
            $date = new DateTimeImmutable($value);
            $now = new DateTimeImmutable();
            $diff = $now->diff($date);
            
            if ($diff->days > 0) {
                return $diff->days . ' dia' . ($diff->days > 1 ? 's' : '') . ' atrás';
            } elseif ($diff->h > 0) {
                return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
            } else {
                return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
            }
        } catch (Exception $exception) {
            return htmlspecialchars($value);
        }
    }
    ?>

    <aside class="sidebar">
        <?php include __DIR__."/components/sidebar.php"?>
    </aside>
    
    <div class="container">
        <main class="main-content">
            <header class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>👤 Admin: João Silva</span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Total de Livros</p>
                            <h2 class="stat-value"><?php echo $stats['total_books'] ?></h2>
                            <!-- <p class="stat-desc">Carregando...</p> -->
                        </div>
                        <div class="stat-icon" style="background: #3b82f620; color: #3b82f6">
                            📖
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Livros Emprestados</p>
                            <h2 class="stat-value"><?php echo $stats['borrowed_books'] ?></h2>
                            <!-- <p class="stat-desc">Carregando...</p> -->
                        </div>
                        <div class="stat-icon" style="background: #f59e0b20; color: #f59e0b">
                            📚
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Usuários Ativos</p>
                            <h2 class="stat-value"><?php echo $stats['total_users'] ?></h2>
                            <!-- <p class="stat-desc">Carregando...</p> -->
                        </div>
                        <div class="stat-icon" style="background: #10b98120; color: #10b981">
                            👥
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Empréstimos Hoje</p>
                            <h2 class="stat-value"><?php echo $stats['pending_requests'] ?></h2>
                            <!-- <p class="stat-desc">Carregando...</p> -->
                        </div>
                        <div class="stat-icon" style="background: #6366f120; color: #6366f1">
                            📅
                        </div>
                    </div>
                </div>

                <!-- Seção de Solicitações Pendentes removida - não é responsabilidade do serviço de dashboard -->

                <!-- Gráficos e Dados -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3> Empréstimos por Mês</h3>
                                <p class="chart-subtitle">Estatísticas de empréstimos dos últimos 6 meses</p>
                            </div>
                        </div>
                        <div class="bar-chart">
                            <div class="loading-message">Carregando dados...</div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3> Livros Mais Emprestados</h3>
                                <p class="chart-subtitle">Top 5 livros mais populares</p>
                            </div>
                        </div>
                        <div class="top-books">
                            <div class="loading-message">Carregando dados...</div>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3> Distribuição por Categoria</h3>
                                <p class="chart-subtitle">Livros por categoria no acervo</p>
                            </div>
                        </div>
                        <div class="pie-chart-container">
                            <svg class="pie-chart" viewBox="0 0 200 200">
                            </svg>
                            <div class="pie-legend">
                                <div class="loading-message">Carregando dados...</div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3> Atividades Recentes</h3>
                                <p class="chart-subtitle">Últimas ações no sistema</p>
                            </div>
                        </div>
                        <div class="activities">
                            <div class="loading-message">Carregando dados...</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script type="module" src="/public/js/dashboard.js"></script>
</body>
</html>