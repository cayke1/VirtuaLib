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
    $stats = [
        'total_livros' => ['valor' => '...', 'descricao' => 'Carregando...', 'icon' => '📖', 'color' => '#3b82f6'],
        'livros_emprestados' => ['valor' => '...', 'descricao' => 'Carregando...', 'icon' => '📚', 'color' => '#f59e0b'],
        'usuarios_ativos' => ['valor' => '...', 'descricao' => 'Carregando...', 'icon' => '👥', 'color' => '#10b981'],
        'emprestimos_hoje' => ['valor' => '...', 'descricao' => 'Carregando...', 'icon' => '📅', 'color' => '#6366f1']
    ];
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
                            <h2 class="stat-value"><?php echo $stats['total_livros']['valor']; ?></h2>
                            <p class="stat-desc"><?php echo $stats['total_livros']['descricao']; ?></p>
                        </div>
                        <div class="stat-icon" style="background: <?php echo $stats['total_livros']['color']; ?>20; color: <?php echo $stats['total_livros']['color']; ?>">
                            <?php echo $stats['total_livros']['icon']; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Livros Emprestados</p>
                            <h2 class="stat-value"><?php echo $stats['livros_emprestados']['valor']; ?></h2>
                            <p class="stat-desc"><?php echo $stats['livros_emprestados']['descricao']; ?></p>
                        </div>
                        <div class="stat-icon" style="background: <?php echo $stats['livros_emprestados']['color']; ?>20; color: <?php echo $stats['livros_emprestados']['color']; ?>">
                            <?php echo $stats['livros_emprestados']['icon']; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Usuários Ativos</p>
                            <h2 class="stat-value"><?php echo $stats['usuarios_ativos']['valor']; ?></h2>
                            <p class="stat-desc"><?php echo $stats['usuarios_ativos']['descricao']; ?></p>
                        </div>
                        <div class="stat-icon" style="background: <?php echo $stats['usuarios_ativos']['color']; ?>20; color: <?php echo $stats['usuarios_ativos']['color']; ?>">
                            <?php echo $stats['usuarios_ativos']['icon']; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <p class="stat-label">Empréstimos Hoje</p>
                            <h2 class="stat-value"><?php echo $stats['emprestimos_hoje']['valor']; ?></h2>
                            <p class="stat-desc"><?php echo $stats['emprestimos_hoje']['descricao']; ?></p>
                        </div>
                        <div class="stat-icon" style="background: <?php echo $stats['emprestimos_hoje']['color']; ?>20; color: <?php echo $stats['emprestimos_hoje']['color']; ?>">
                            <?php echo $stats['emprestimos_hoje']['icon']; ?>
                        </div>
                    </div>
                </div>

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