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
        <div class="book-cover-container">
            <?php if (!empty($book['cover_image'])): ?>
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Capa de <?php echo htmlspecialchars($book['title']); ?>" class="book-cover-image">
            <?php else: ?>
                <div class="book-cover-placeholder">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

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
