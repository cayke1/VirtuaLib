<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VirtuaLib</title>
        <link rel="stylesheet" href="/public/css/dashboard.css">
</head>
<body>
    <?php
    // ============================================
    // DADOS MOCKADOS
    // ============================================
    
    // Cards principais
    $stats = [
        'total_livros' => ['valor' => '2,847', 'descricao' => 'Disponíveis no acervo', 'icon' => '📖', 'color' => '#3b82f6'],
        'livros_emprestados' => ['valor' => '342', 'descricao' => '+12% este mês', 'icon' => '📚', 'color' => '#f59e0b'],
        'usuarios_ativos' => ['valor' => '1,234', 'descricao' => '+8% este mês', 'icon' => '👥', 'color' => '#10b981'],
        'emprestimos_hoje' => ['valor' => '28', 'descricao' => 'Meta: 30/dia', 'icon' => '📅', 'color' => '#6366f1']
    ];

    // Dados do gráfico de empréstimos
    $emprestimos_mes = [
        'Jan' => 180,
        'Fev' => 320,
        'Mar' => 240,
        'Abr' => 270,
        'Mai' => 190,
        'Jun' => 350
    ];
    $max_valor = max($emprestimos_mes);

    // Top 5 livros mais emprestados
    $top_livros = [
        ['titulo' => 'Dom Casmurro', 'autor' => 'Machado de Assis', 'emprestimos' => 474],
        ['titulo' => 'O Cortiço', 'autor' => 'Aluísio Azevedo', 'emprestimos' => 435],
        ['titulo' => '1984', 'autor' => 'George Orwell', 'emprestimos' => 392],
        ['titulo' => 'O Pequeno Príncipe', 'autor' => 'Antoine de Saint-Exupéry', 'emprestimos' => 351],
        ['titulo' => 'Harry Potter', 'autor' => 'J.K. Rowling', 'emprestimos' => 324]
    ];

    // Distribuição por categoria (para gráfico pizza)
    $categorias = [
        ['nome' => 'Ficção', 'percentual' => 35, 'color' => '#059669'],
        ['nome' => 'Literatura', 'percentual' => 25, 'color' => '#3b82f6'],
        ['nome' => 'Técnicos', 'percentual' => 20, 'color' => '#14b8a6'],
        ['nome' => 'História', 'percentual' => 12, 'color' => '#8b5cf6'],
        ['nome' => 'Outros', 'percentual' => 8, 'color' => '#f59e0b']
    ];

    // Atividades recentes
    $atividades = [
        ['tipo' => 'cadastro', 'texto' => 'Novo usuário cadastrado', 'detalhe' => 'Maria Santos - há 5 min', 'color' => '#10b981'],
        ['tipo' => 'emprestimo', 'texto' => 'Livro emprestado', 'detalhe' => '1984 - George Orwell - há 12 min', 'color' => '#3b82f6'],
        ['tipo' => 'devolucao', 'texto' => 'Livro devolvido', 'detalhe' => 'Dom Casmurro - há 1h', 'color' => '#f59e0b'],
        ['tipo' => 'atraso', 'texto' => 'Empréstimo em atraso', 'detalhe' => 'O Cortiço - João Silva - há 2h', 'color' => '#ef4444'],
        ['tipo' => 'novo_livro', 'texto' => 'Novo livro adicionado', 'detalhe' => 'Clean Code - Robert Martin - há 3h', 'color' => '#10b981']
    ];
    ?>

    <aside class="sidebar">
        <?php include __DIR__."/components/sidebar.php"?>
    </aside>
    
    <div class="container">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>👤 Admin: João Silva</span>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Cards de Estatísticas -->
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

                <!-- Gráficos e Dados -->
                <div class="charts-grid">
                    <!-- Gráfico de Empréstimos por Mês -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3>📈 Empréstimos por Mês</h3>
                                <p class="chart-subtitle">Estatísticas de empréstimos dos últimos 6 meses</p>
                            </div>
                        </div>
                        <div class="bar-chart">
                            <?php foreach ($emprestimos_mes as $mes => $valor): ?>
                                <div class="bar-container">
                                    <div class="bar" style="height: <?php echo ($valor / $max_valor) * 100; ?>%">
                                        <span class="bar-value"><?php echo $valor; ?></span>
                                    </div>
                                    <span class="bar-label"><?php echo $mes; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top 5 Livros Mais Emprestados -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3>⭐ Livros Mais Emprestados</h3>
                                <p class="chart-subtitle">Top 5 livros mais populares</p>
                            </div>
                        </div>
                        <div class="top-books">
                            <?php foreach ($top_livros as $index => $livro): ?>
                                <div class="book-item">
                                    <span class="book-rank"><?php echo $index + 1; ?></span>
                                    <div class="book-info">
                                        <p class="book-title"><?php echo $livro['titulo']; ?></p>
                                        <p class="book-author"><?php echo $livro['autor']; ?></p>
                                    </div>
                                    <span class="book-count"><?php echo $livro['emprestimos']; ?>x</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Segunda linha de gráficos -->
                <div class="charts-grid">
                    <!-- Gráfico de Pizza - Distribuição por Categoria -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3>📊 Distribuição por Categoria</h3>
                                <p class="chart-subtitle">Livros por categoria no acervo</p>
                            </div>
                        </div>
                        <div class="pie-chart-container">
                            <svg class="pie-chart" viewBox="0 0 200 200">
                                <?php
                                $startAngle = 0;
                                foreach ($categorias as $cat) {
                                    $angle = ($cat['percentual'] / 100) * 360;
                                    $endAngle = $startAngle + $angle;
                                    
                                    $x1 = 100 + 80 * cos(deg2rad($startAngle - 90));
                                    $y1 = 100 + 80 * sin(deg2rad($startAngle - 90));
                                    $x2 = 100 + 80 * cos(deg2rad($endAngle - 90));
                                    $y2 = 100 + 80 * sin(deg2rad($endAngle - 90));
                                    
                                    $largeArc = $angle > 180 ? 1 : 0;
                                    
                                    echo "<path d='M 100 100 L $x1 $y1 A 80 80 0 $largeArc 1 $x2 $y2 Z' fill='{$cat['color']}' />";
                                    
                                    $startAngle = $endAngle;
                                }
                                ?>
                            </svg>
                            <div class="pie-legend">
                                <?php foreach ($categorias as $cat): ?>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background: <?php echo $cat['color']; ?>"></span>
                                        <span class="legend-label"><?php echo $cat['nome']; ?> (<?php echo $cat['percentual']; ?>%)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Atividades Recentes -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div>
                                <h3>⚡ Atividades Recentes</h3>
                                <p class="chart-subtitle">Últimas ações no sistema</p>
                            </div>
                        </div>
                        <div class="activities">
                            <?php foreach ($atividades as $atividade): ?>
                                <div class="activity-item">
                                    <span class="activity-dot" style="background: <?php echo $atividade['color']; ?>"></span>
                                    <div class="activity-info">
                                        <p class="activity-text"><?php echo $atividade['texto']; ?></p>
                                        <p class="activity-detail"><?php echo $atividade['detalhe']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>