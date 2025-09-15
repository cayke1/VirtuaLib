<div class="container">
    <div class="breadcrumb">
        <a href="/books" class="breadcrumb-link">Livros</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current"><?php echo htmlspecialchars($book['title']); ?></span>
    </div>

    <div class="book-detail">
        <div class="book-detail-content">
            <div class="book-cover-large">
                <?php if (!empty($book['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                         alt="Capa do livro <?php echo htmlspecialchars($book['title']); ?>"
                         class="cover-image-large">
                <?php else: ?>
                    <div class="default-cover-large">
                        <span class="book-icon-large">📖</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="book-details">
                <h1 class="book-title-large"><?php echo htmlspecialchars($book['title']); ?></h1>
                
                <div class="book-meta">
                    <div class="meta-item">
                        <strong>Autor:</strong> <?php echo htmlspecialchars($book['author']); ?>
                    </div>
                    
                    <?php if (!empty($book['isbn'])): ?>
                        <div class="meta-item">
                            <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['publication_year'])): ?>
                        <div class="meta-item">
                            <strong>Ano de Publicação:</strong> <?php echo htmlspecialchars($book['publication_year']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['publisher'])): ?>
                        <div class="meta-item">
                            <strong>Editora:</strong> <?php echo htmlspecialchars($book['publisher']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['pages'])): ?>
                        <div class="meta-item">
                            <strong>Páginas:</strong> <?php echo htmlspecialchars($book['pages']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['language'])): ?>
                        <div class="meta-item">
                            <strong>Idioma:</strong> <?php echo htmlspecialchars($book['language']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="meta-item status">
                        <strong>Status:</strong> 
                        <span class="status-available">Disponível</span>
                    </div>
                </div>
                
                <?php if (!empty($book['description'])): ?>
                    <div class="book-description-full">
                        <h3>Sinopse</h3>
                        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="book-actions">
                    <a href="/books" class="btn btn-secondary">
                        Voltar à Lista
                    </a>
                    <button class="btn btn-primary" onclick="alert('Funcionalidade de empréstimo em desenvolvimento!')">
                        Solicitar Empréstimo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.breadcrumb {
    margin-bottom: 30px;
    font-size: 0.9rem;
    color: #666;
}

.breadcrumb-link {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-link:hover {
    color: #5a6fd8;
}

.breadcrumb-separator {
    margin: 0 10px;
    color: #999;
}

.breadcrumb-current {
    color: #333;
    font-weight: 600;
}

.book-detail {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.book-detail-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 40px;
    padding: 40px;
}

.book-cover-large {
    height: 400px;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cover-image-large {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-cover-large {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.book-icon-large {
    font-size: 6rem;
    opacity: 0.7;
}

.book-details {
    display: flex;
    flex-direction: column;
}

.book-title-large {
    font-size: 2.2rem;
    margin: 0 0 20px 0;
    color: #333;
    line-height: 1.3;
}

.book-meta {
    margin-bottom: 30px;
}

.meta-item {
    margin: 12px 0;
    font-size: 1rem;
    color: #555;
}

.meta-item strong {
    color: #333;
    font-weight: 600;
}

.status-available {
    color: #28a745;
    font-weight: 600;
}

.book-description-full {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.book-description-full h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 1.3rem;
}

.book-description-full p {
    margin: 0;
    line-height: 1.6;
    color: #555;
}

.book-actions {
    display: flex;
    gap: 15px;
    margin-top: auto;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .book-detail-content {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 20px;
    }
    
    .book-cover-large {
        height: 300px;
    }
    
    .book-title-large {
        font-size: 1.8rem;
    }
    
    .book-actions {
        flex-direction: column;
    }
    
    .btn {
        text-align: center;
    }
}
</style>
