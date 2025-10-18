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
                            <p class="stat-desc">Carregando...</p>
                        </div>
                        <div class="stat-icon" style="background: #3b82f620; color: #3b82f6">
                            📖
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Livros Emprestados</p>
                            <h2 class="stat-value"><?php echo $stats['borrowed_books'] ?></h2>
                            <p class="stat-desc">Carregando...</p>
                        </div>
                        <div class="stat-icon" style="background: #f59e0b20; color: #f59e0b">
                            📚
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Usuários Ativos</p>
                            <h2 class="stat-value"><?php echo $stats['total_users'] ?></h2>
                            <p class="stat-desc">Carregando...</p>
                        </div>
                        <div class="stat-icon" style="background: #10b98120; color: #10b981">
                            👥
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Empréstimos Hoje</p>
                            <h2 class="stat-value"><?php echo $stats['pending_requests'] ?></h2>
                            <p class="stat-desc">Carregando...</p>
                        </div>
                        <div class="stat-icon" style="background: #6366f120; color: #6366f1">
                            📅
                        </div>
                    </div>
                </div>

                <?php if ($isAdmin && !empty($pendingRequests)): ?>
                <!-- Seção de Solicitações Pendentes -->
                <div class="pending-requests-section">
                    <div class="section-header">
                        <h2>⏳ Solicitações Pendentes</h2>
                        <span class="request-count"><?php echo count($pendingRequests); ?> solicitação(ões)</span>
                    </div>
                    
                    <div class="requests-grid">
                        <?php foreach ($pendingRequests as $request): ?>
                            <div class="request-card" data-request-id="<?php echo $request['id']; ?>">
                                <div class="request-info">
                                    <div class="request-user">
                                        <span class="user-name"><?php echo htmlspecialchars($request['user_name']); ?></span>
                                        <span class="user-email"><?php echo htmlspecialchars($request['user_email']); ?></span>
                                    </div>
                                    <div class="request-book">
                                        <h4><?php echo htmlspecialchars($request['book_title']); ?></h4>
                                        <p><?php echo htmlspecialchars($request['book_author']); ?></p>
                                    </div>
                                    <div class="request-time">
                                        <span class="time-badge"><?php echo formatRequestDate($request['requested_at']); ?></span>
                                    </div>
                                </div>
                                <div class="request-actions">
                                    <button class="approve-btn" onclick="approveRequest(<?php echo $request['id']; ?>)">
                                        ✅ Aprovar
                                    </button>
                                    <button class="reject-btn" onclick="rejectRequest(<?php echo $request['id']; ?>)">
                                        ❌ Rejeitar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

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

    <script src="/public/js/dashboard.js"></script>
</body>
</html>