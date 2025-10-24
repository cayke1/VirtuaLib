<?php

/**
 * Componente de Card de Livro
 * 
 * @param array $book - Array com dados do livro
 */

// Incluir utilitários de texto e imagem
require_once __DIR__ . '/../../../utils/TextUtils.php';
require_once __DIR__ . '/../../../utils/ImageUrlHelper.php';
?>
<style>
    a {
        text-decoration: none;
        color: inherit;
    }
</style>

<?php
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

<div class="book-card" data-book-id="<?php echo $book['id']; ?>">
  <a href="/details/<?php echo $book['id']; ?>" class="book-link">
    <div class="book-cover-container">
      <?php if (!empty($book['cover_image'])): ?>
        <?php echo ImageUrlHelper::getImageTag(
          $book['cover_image'], 
          'Capa de ' . $book['title'], 
          'book-cover-image'
        ); ?>
      <?php else: ?>
        <div class="book-cover-placeholder">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
      <?php endif; ?>
    </div>

    <div class="book-info">
      <div class="book-status">
        <span class="status-dot <?php echo $isAvailable ? 'available' : 'borrowed'; ?>"></span>
        <span class="status-text"><?php echo $isAvailable ? 'Disponível' : 'Emprestado'; ?></span>
      </div>

      <h3 class="book-title"><?php echo TextUtils::formatText($book['title'], 40); ?></h3>
      <p class="book-author"><?php echo TextUtils::formatText($book['author'], 30); ?></p>
      <p class="book-genre-year"><?php echo TextUtils::formatText($book['genre'], 15); ?> • <?php echo $book['year']; ?></p>
      <p class="book-description"><?php echo TextUtils::formatText($book['description'], 120); ?></p>
    </div>
  </a>

  <div class="book-actions">
    <button class="action-button <?php echo $buttonClass; ?>"
      data-book-id="<?php echo $book['id']; ?>"
      <?php echo $buttonDisabled ? 'disabled' : ''; ?>
      onclick="<?php echo $buttonClass === 'return' ? 'returnBook' : 'requestBook'; ?>(<?php echo $book['id']; ?>)">
      <?php echo $buttonLabel; ?>
    </button>

    <div class="bookmark-icon" onclick="toggleBookmark(this)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M19 21L12 16L5 21V5C5 3.9 5.9 3 7 3H17C18.1 3 19 3.9 19 5V21Z" stroke="currentColor" stroke-width="2" />
      </svg>
    </div>
  </div>
</div>


<style>
    .book-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
    height: 100%;
  }
  
  .book-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }
  
  /* === TOPO COM IMAGEM === */
  .book-cover-container {
    width: 100%;
    height: 240px;
    overflow: hidden;
    position: relative;
  }

  .book-card img {
    width: auto;
    height: auto;
    object-fit: cover;
    min-width: 100%;
    min-height: 240px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }
  

  
  .book-cover-container:hover .book-cover-image {
    transform: scale(1.05);
  }
  
  /* Fade escuro suave pra destacar o texto abaixo */
  .book-cover-container::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 80px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.25), transparent);
    pointer-events: none;
  }
  
  /* === INFORMAÇÕES === */
  .book-info {
    flex: 1;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
  }
  
  .book-status {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.3rem;
  }
  
  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }
  
  .status-dot.available {
    background-color: #10b981;
  }
  .status-dot.borrowed {
    background-color: #ef4444;
  }
  
  .status-text {
    font-size: 0.8rem;
    color: #64748b;
  }
  
  .book-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    margin: 0.3rem 0 0.2rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  .book-author {
    font-size: 0.95rem;
    color: #475569;
    margin-bottom: 0.25rem;
  }
  
  .book-genre-year {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 0.75rem;
  }
  
  .book-description {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  /* === RODAPÉ FIXO DENTRO DO CARD === */
  .book-actions {
    border-top: 1px solid #f1f5f9;
    background: #f9fafb;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }
  
  .action-button {
    flex: 1;
    padding: 0.7rem 1.4rem;
    border: none;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .action-button.borrow {
    background: #2563eb;
    color: #fff;
  }
  .action-button.borrow:hover {
    background: #1d4ed8;
  }
  
  .action-button.return {
    background: #1e293b;
    color: #fff;
  }
  .action-button.return:hover {
    background: #334155;
  }
  
  .action-button.pending {
    background: #f59e0b;
    color: #fff;
    cursor: not-allowed;
  }
  
  /* === ÍCONE FAVORITO === */
  .bookmark-icon {
    padding: 8px;
    border-radius: 50%;
    background: #fff;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
  }
  
  .bookmark-icon:hover {
    color: #2563eb;
    background: #eff6ff;
  }
  
  /* === GRID === */
  .books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
  }
  
  /* === MOBILE === */
  @media (max-width: 768px) {
    .book-cover-container {
      height: 200px;
    }
    .book-info {
      padding: 1rem;
    }
    .book-actions {
      padding: 0.8rem 1rem;
    }
  }
  
</style>