<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - VirtuaLib</title>
    <link rel="stylesheet" href="/public/css/history.css">
</head>
<body>
    <?php
    
    $historico = [
        [
            'id' => 1,
            'usuario' => 'Ana Botelho',
            'livro' => 'O Pequeno Príncipe',
            'data_emprestimo' => '15/03/2024',
            'data_devolucao' => '29/03/2024',
            'status' => 'devolvido'
        ],
        [
            'id' => 2,
            'usuario' => 'Carlos Mendes',
            'livro' => '1984 - George Orwell',
            'data_emprestimo' => '20/03/2024',
            'data_devolucao' => '03/04/2024',
            'status' => 'ativo'
        ],
        [
            'id' => 3,
            'usuario' => 'Maria Santos',
            'livro' => 'Dom Casmurro',
            'data_emprestimo' => '10/03/2024',
            'data_devolucao' => '24/03/2024',
            'status' => 'atrasado'
        ],
        [
            'id' => 4,
            'usuario' => 'João Oliveira',
            'livro' => 'A Arte da Guerra',
            'data_emprestimo' => '25/03/2024',
            'data_devolucao' => '08/04/2024',
            'status' => 'ativo'
        ],
        [
            'id' => 5,
            'usuario' => 'Lucia Ferreira',
            'livro' => 'O Cortiço',
            'data_emprestimo' => '12/03/2024',
            'data_devolucao' => '26/03/2024',
            'status' => 'devolvido'
        ],
        [
            'id' => 6,
            'usuario' => 'Pedro Costa',
            'livro' => 'Cem Anos de Solidão',
            'data_emprestimo' => '05/03/2024',
            'data_devolucao' => '19/03/2024',
            'status' => 'atrasado'
        ],
        [
            'id' => 7,
            'usuario' => 'Sofia Lima',
            'livro' => 'Orgulho e Preconceito',
            'data_emprestimo' => '28/03/2024',
            'data_devolucao' => '11/04/2024',
            'status' => 'atrasado'
        ],
        [
            'id' => 8,
            'usuario' => 'Rafael Barbosa',
            'livro' => 'O Alquimista',
            'data_emprestimo' => '18/03/2024',
            'data_devolucao' => '01/04/2024',
            'status' => 'devolvido'
        ]
    ];

    // Configuração dos status
    $statusConfig = [
        'devolvido' => [
            'icon' => '✓',
            'text' => 'Devolvido',
            'class' => 'status-devolvido'
        ],
        'ativo' => [
            'icon' => '📖',
            'text' => 'Ativo',
            'class' => 'status-ativo'
        ],
        'atrasado' => [
            'icon' => '⚠',
            'text' => 'Atrasado',
            'class' => 'status-atrasado'
        ]
    ];

    // Variáveis de paginação (mockadas)
    $totalEmprestimos = 156;
    $emprestimosPorPagina = 8;
    $paginaAtual = 1;
    $totalPaginas = ceil($totalEmprestimos / $emprestimosPorPagina);
    ?>

    <!-- Sidebar -->
    <aside class="sidebar">
        <?php include __DIR__ . '/components/sidebar.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <h1>Histórico</h1>
            <div class="user-info">
                <span>👤 Admin: João Silva</span>
            </div>
        </header>

        <div class="content">
            <div class="controls">
                <div class="search-box">
                    <input type="text" placeholder="Buscar por usuário ou título do livro...">
                </div>
                <select class="filter-select">
                    <option value="">Filtrar por status</option>
                    <option value="ativo">Ativo</option>
                    <option value="devolvido">Devolvido</option>
                    <option value="atrasado">Atrasado</option>
                </select>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Usuário</th>
                            <th>Livro Emprestado</th>
                            <th>Data de Empréstimo</th>
                            <th>Data de Devolução</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historico)): ?>
                            <tr>
                                <td colspan="5" class="no-data">
                                    Nenhum empréstimo encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historico as $emprestimo): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emprestimo['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($emprestimo['livro']); ?></td>
                                    <td><?php echo htmlspecialchars($emprestimo['data_emprestimo']); ?></td>
                                    <td><?php echo htmlspecialchars($emprestimo['data_devolucao']); ?></td>
                                    <td>
                                        <?php 
                                        $status = $statusConfig[$emprestimo['status']];
                                        ?>
                                        <span class="status <?php echo $status['class']; ?>">
                                            <?php echo $status['icon']; ?> <?php echo $status['text']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Mostrando <?php echo count($historico); ?> de <?php echo $totalEmprestimos; ?> empréstimos
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" <?php echo $paginaAtual <= 1 ? 'disabled' : ''; ?>>
                        ❮ Previous
                    </button>
                    
                    <?php for ($i = 1; $i <= min(3, $totalPaginas); $i++): ?>
                        <button class="pagination-btn <?php echo $i === $paginaAtual ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                    
                    <button class="pagination-btn" <?php echo $paginaAtual >= $totalPaginas ? 'disabled' : ''; ?>>
                        Next ❯
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>