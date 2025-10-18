<?php

$title = $title ?? 'Histórico - VirtuaLib';
$history = $history ?? [];
$currentUser = $currentUser ?? null;

$statusAliases = [
    'pending' => 'Pendente',
    'approved' => 'Aprovado',
    'returned' => 'Devolvido',
    'late' => 'Atrasado',
];

$statusConfig = [
    'Pendente' => [
        'icon' => '⏳',
        'text' => 'Pendente',
        'class' => 'status-pendente'
    ],
    'Aprovado' => [
        'icon' => '📖',
        'text' => 'Emprestado',
        'class' => 'status-ativo'
    ],
    'Devolvido' => [
        'icon' => '✓',
        'text' => 'Devolvido',
        'class' => 'status-devolvido'
    ],
    'Atrasado' => [
        'icon' => '⚠',
        'text' => 'Atrasado',
        'class' => 'status-atrasado'
    ]
];

function formatHistoryDate(?string $value, string $format = 'd/m/Y'): string
{
    if (!$value) {
        return '—';
    }

    try {
        $date = new DateTimeImmutable($value);
        return $date->format($format);
    } catch (Exception $exception) {
        error_log('Date parse error in history view: ' . $exception->getMessage());
        return htmlspecialchars($value);
    }
}

$totalLoans = count($history);
$currentUserName = $currentUser['name'] ?? 'Usuário';
$roleLabel = $currentUser['role'] ?? null;
$userDisplay = $roleLabel
    ? sprintf('%s: %s', ucfirst($roleLabel), $currentUserName)
    : $currentUserName;
$currentPage = 1;
$totalPages = max($totalLoans > 0 ? (int)ceil($totalLoans / max($totalLoans, 1)) : 1, 1);

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/public/css/history.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <h1>Histórico</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($userDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </header>

        <div class="content">
            <div class="controls">
                <div class="search-box">
                    <input type="text" placeholder="Buscar por usuário ou título do livro...">
                </div>
                <select class="filter-select">
                    <option value="">Filtrar por status</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Aprovado">Aprovado</option>
                    <option value="Devolvido">Devolvido</option>
                    <option value="Atrasado">Atrasado</option>
                </select>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Usuário</th>
                            <th>Livro Emprestado</th>
                            <th>Data de Solicitação</th>
                            <th>Data de Devolução</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="no-data">
                                    Nenhum empréstimo encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $loan): ?>
                                <?php
                                    $rawStatus = $loan['status'] ?? 'Emprestado';
                                    $normalizedKey = strtolower($rawStatus);
                                    $normalizedStatus = $statusAliases[$normalizedKey] ?? $rawStatus;
                                    $status = $statusConfig[$normalizedStatus] ?? $statusConfig['Emprestado'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($loan['book_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo formatHistoryDate($loan['requested_at']); ?></td>
                                    <td><?php echo formatHistoryDate($loan['returned_at']); ?></td>
                                    <td>
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
                    <?php if ($totalLoans > 0): ?>
                        Mostrando <?php echo $totalLoans; ?> empréstimo(s)
                    <?php else: ?>
                        Nenhum empréstimo cadastrado
                    <?php endif; ?>
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" disabled>
                        ❮ Previous
                    </button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn" disabled>
                        Next ❯
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>