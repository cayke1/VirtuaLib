<div class="container">
    <div class="header-section">
        <h1>Resultados da Busca</h1>
        <?php if (!empty($searchTerm)): ?>
            <p class="search-info">
                Buscando por: <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong>
            </p>
            <div class="stats">
                <span class="badge"><?php echo $totalResults; ?> resultado(s) encontrado(s)</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Barra de pesquisa -->
    <div class="search-section">
        <form action="/books/search" method="GET" class="search-form">
            <div class="search-input-group">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Buscar por título ou autor..." 
                    class="search-input"
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                >
                <button type="submit" class="search-button">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Navegação -->
    <div class="navigation">
        <a href="/books" class="nav-link">
            ← Voltar à Lista Completa
        </a>
    </div>

    <!-- Resultados da busca -->
    <div class="results-section">
        <?php if (empty($searchTerm)): ?>
            <div class="empty-search">
                <div class="empty-icon">🔍</div>
                <h3>Digite um termo para buscar</h3>
                <p>Use a barra de pesquisa acima para encontrar livros por título ou autor.</p>
            </div>
        <?php elseif (empty($books)): ?>
            <div class="no-results">
                <div class="empty-icon">📖</div>
                <h3>Nenhum resultado encontrado</h3>
                <p>Não encontramos livros que correspondam à sua busca por <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong>.</p>
                <div class="suggestions">
                    <h4>Sugestões:</h4>
                    <ul>
                        <li>Verifique se a ortografia está correta</li>
                        <li>Tente usar termos mais gerais</li>
                        <li>Use apenas o sobrenome do autor</li>
                        <li>Experimente buscar por palavras-chave do título</li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="Capa do livro <?php echo htmlspecialchars($book['title']); ?>"
                                     class="cover-image">
                            <?php else: ?>
                                <div class="default-cover">
                                    <span class="book-icon">📚</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="book-info">
                            <h3 class="book-title">
                                <a href="/books/<?php echo $book['id']; ?>" class="book-link">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="book-author">
                                <strong>Autor:</strong> <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            
                            <?php if (!empty($book['isbn'])): ?>
                                <p class="book-isbn">
                                    <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['publication_year'])): ?>
                                <p class="book-year">
                                    <strong>Ano:</strong> <?php echo htmlspecialchars($book['publication_year']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['description'])): ?>
                                <p class="book-description">
                                    <?php echo htmlspecialchars(substr($book['description'], 0, 150)); ?>
                                    <?php if (strlen($book['description']) > 150): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="book-actions">
                                <a href="/books/<?php echo $book['id']; ?>" class="btn btn-primary">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header-section {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}

.header-section h1 {
    font-size: 2.5rem;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.search-info {
    font-size: 1.1rem;
    margin: 0 0 20px 0;
    opacity: 0.9;
}

.stats {
    margin-top: 20px;
}

.badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.search-section {
    margin-bottom: 30px;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-input-group {
    display: flex;
    gap: 10px;
    background: white;
    padding: 5px;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.search-input {
    flex: 1;
    border: none;
    padding: 15px 20px;
    font-size: 16px;
    border-radius: 20px;
    outline: none;
}

.search-button {
    background: #667eea;
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s ease;
}

.search-button:hover {
    background: #5a6fd8;
}

.navigation {
    margin-bottom: 30px;
}

.nav-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.nav-link:hover {
    color: #5a6fd8;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.book-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.book-cover {
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.cover-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-cover {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.book-icon {
    font-size: 4rem;
    opacity: 0.7;
}

.book-info {
    padding: 20px;
}

.book-title {
    margin: 0 0 10px 0;
    font-size: 1.3rem;
    line-height: 1.4;
}

.book-link {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.book-link:hover {
    color: #667eea;
}

.book-author, .book-isbn, .book-year {
    margin: 8px 0;
    color: #666;
    font-size: 0.95rem;
}

.book-description {
    margin: 15px 0;
    color: #555;
    line-height: 1.5;
    font-size: 0.9rem;
}

.book-actions {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.empty-search, .no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-search h3, .no-results h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.empty-search p, .no-results p {
    margin: 0 0 20px 0;
    font-size: 1.1rem;
}

.suggestions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: left;
    max-width: 400px;
    margin: 0 auto;
}

.suggestions h4 {
    margin: 0 0 15px 0;
    color: #333;
}

.suggestions ul {
    margin: 0;
    padding-left: 20px;
}

.suggestions li {
    margin: 8px 0;
    color: #555;
}

@media (max-width: 768px) {
    .books-grid {
        grid-template-columns: 1fr;
    }
    
    .search-input-group {
        flex-direction: column;
    }
    
    .search-input, .search-button {
        border-radius: 10px;
    }
    
    .header-section h1 {
        font-size: 2rem;
    }
}
</style>
