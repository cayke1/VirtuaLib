<?php
$totalBooks = count($books);

$availableBooks = count(array_filter($books, function($book) {
    return isset($book['available']) && $book['available']; // livros disponíveis
}));

$borrowedBooks = $totalBooks - $availableBooks;
?>

<div class="container">
    <!-- Título e subtítulo -->
    <h1 class="page-title">Biblioteca Digital</h1>
    <p class="page-subtitle">Gerencie empréstimos e devoluções de livros</p>
    
    <!-- Estatísticas e controles -->
    <div class="stats-controls">
        <div class="stats">
            <div class="stat-item available">
                <i class="fas fa-book-open"></i>
                <span><?php echo $availableBooks; ?> livros disponíveis</span>
            </div>
            <div class="stat-item borrowed">
                <i class="fas fa-book"></i>
                <span><?php echo $borrowedBooks; ?> emprestados</span>
            </div>
        </div>
        
        <div class="controls">
            <button class="control-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 3H2L10 12.46V19L14 21V12.46L22 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Filtros
            </button>
            <button class="control-button" onclick="toggleSort(this)">
                <svg class="sort-icon-default" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 6H21M7 12H17M10 18H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg class="sort-icon-active" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path d="M6 9L12 3L18 9M18 15L12 21L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Ordenar
            </button>
        </div>
    </div>
    
    <!-- Grid de livros -->
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <?php include __DIR__ . '/components/book-card.php'; ?>
        <?php endforeach; ?>
    </div>
    
</div>
