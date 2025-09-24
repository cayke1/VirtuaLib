<?php

/**
 * Componente de Card de Livro
 * 
 * @param array $book - Array com dados do livro
 */

// Incluir utilitários de texto
require_once __DIR__ . '/../../utils/TextUtils.php';
?>
<style>
    a {
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="book-card" data-book-id="<?php echo $book['id']; ?>">
    <a href="/details/<?php echo $book['id']; ?>" class="book-link">
        <div class="book-card-header">
            <div class="book-status">
                <span class="status-dot <?php echo $book['borrowed'] ? 'borrowed' : 'available'; ?>"></span>
                <span class="status-text"><?php echo $book['borrowed'] ? 'Emprestado' : 'Disponível'; ?></span>
            </div>
            <div class="book-menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="5" r="2" fill="currentColor" />
                    <circle cx="12" cy="12" r="2" fill="currentColor" />
                    <circle cx="12" cy="19" r="2" fill="currentColor" />
                </svg>
            </div>
        </div>

        <div class="book-content">
            <h3 class="book-title"><?php echo TextUtils::formatText($book['title'], 40); ?></h3>
            <p class="book-author"><?php echo TextUtils::formatText($book['author'], 30); ?></p>
            <p class="book-genre-year"><?php echo TextUtils::formatText($book['genre'], 15); ?> • <?php echo $book['year']; ?></p>
            <p class="book-description"><?php echo TextUtils::formatText($book['description'], 120); ?></p>
        </div>
    </a>

    <div class="book-actions">
        <button class="action-button <?php echo $book['borrowed'] ? 'return' : 'borrow'; ?>" data-book-id="<?php echo $book['id']; ?>">
            <?php echo $book['borrowed'] ? 'Devolver' : 'Emprestar'; ?>
        </button>
        <div class="bookmark-icon" onclick="toggleBookmark(this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 21L12 16L5 21V5C5 3.89543 5.89543 3 7 3H17C18.1046 3 19 3.89543 19 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</div>

<script src="/public/js/button.js"></script>