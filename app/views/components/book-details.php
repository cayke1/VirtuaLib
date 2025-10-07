<head>
    <link rel="stylesheet" href="/public/css/book-details.css">
</head>
<div class="container">
    <a href="/" class="back-btn">&larr; Voltar</a>
    <div class="book-card">
        <div class="book-cover">📖</div>

        <div>
            <div class="book-tags">
                <span class="book-badge outline"><?php echo htmlspecialchars($book['genre']); ?></span>
                <?php if (isset($book['available']) && $book['available']): ?>
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
                <button
                    class="action-button borrow"
                    data-book-id="<?php echo $book['id']; ?>">
                    <?php 
                    if (isset($book['available']) && $book['available']) {
                        echo 'Solicitar';
                    } else {
                        echo 'Devolver';
                    }
                    ?>
                </button>
            </div>
        </div>
    </div>
</div>