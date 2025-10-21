<head>
    <link rel="stylesheet" href="/public/css/book-details.css">
</head>

<?php
// Lógica para determinar o estado do botão
$isAvailable = isset($book['available']) && (int)$book['available'] === 1;
$borrowedByCurrentUser = !empty($book['borrowed_by_current_user']);
$requestedByCurrentUser = !empty($book['requested_by_current_user']);

$buttonClass = 'borrow';
$buttonDisabled = false;
$buttonLabel = 'Solicitar';

if ($borrowedByCurrentUser) {
    $buttonClass = 'return';
    $buttonLabel = 'Devolver';
} elseif ($requestedByCurrentUser) {
    $buttonClass = 'pending';
    $buttonDisabled = true;
    $buttonLabel = 'Pendente';
} elseif (!$isAvailable) {
    $buttonDisabled = true;
    $buttonLabel = 'Emprestado';
}
?>

<div class="container">
    <a href="/" class="back-btn">&larr; Voltar</a>
    <div class="book-card">
        <div class="book-cover">📖</div>

        <div>
            <div class="book-tags">
                <span class="book-badge outline"><?php echo htmlspecialchars($book['genre']); ?></span>
                <?php if ($isAvailable): ?>
                    <span class="book-badge available">Disponível</span>
                <?php else: ?>
                    <span class="book-badge unavailable">Indisponível</span>
                <?php endif; ?>
            </div>

            <h2 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h2>
            <p class="book-meta">👤 <?php echo htmlspecialchars($book['author']); ?></p>
            <p class="book-meta">📅 <?php echo htmlspecialchars($book['year']); ?></p>

            <div class="book-description">
                <h2>Sinopse</h2>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>

            <div class="book-actions">
                <?php if ($buttonClass === 'return'): ?>
                    <button
                        class="action-button <?php echo $buttonClass; ?>"
                        data-book-id="<?php echo $book['id']; ?>"
                        <?php echo $buttonDisabled ? 'disabled' : ''; ?>
                        onclick="returnBook(<?php echo $book['id']; ?>)">
                        <?php echo $buttonLabel; ?>
                    </button>
                <?php else: ?>
                    <button
                        class="action-button <?php echo $buttonClass; ?>"
                        data-book-id="<?php echo $book['id']; ?>"
                        <?php echo $buttonDisabled ? 'disabled' : ''; ?>
                        onclick="requestBook(<?php echo $book['id']; ?>)">
                        <?php echo $buttonLabel; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
