<div class="home-container">
    <div class="welcome-section">
        <h1>Bem-vindo ao VirtuaLib</h1>
        <p class="welcome-text">Sua biblioteca virtual completa com milhares de livros disponíveis para empréstimo.</p>
        
        <div class="cta-buttons">
            <a href="/books" class="btn btn-primary">
                Ver Livros Disponíveis
            </a>
            <a href="/books/search" class="btn btn-secondary">
                Buscar Livros
            </a>
        </div>
    </div>

    <div class="features-section">
        <h2>Recursos da Biblioteca</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📖</div>
                <h3>Catálogo Completo</h3>
                <p>Explore nossa vasta coleção de livros de diversos gêneros e autores.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Busca Inteligente</h3>
                <p>Encontre rapidamente o livro que você procura por título, autor ou palavra-chave.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Empréstimo Fácil</h3>
                <p>Solicite empréstimos de forma simples e rápida através da nossa plataforma.</p>
            </div>
        </div>
    </div>

    <?php if (!empty($users)): ?>
    <div class="users-section">
        <h2>Usuários Cadastrados</h2>
        <div class="users-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-avatar">👤</div>
                    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.home-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.welcome-section {
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    margin-bottom: 60px;
}

.welcome-section h1 {
    font-size: 3rem;
    margin: 0 0 20px 0;
    font-weight: 700;
}

.welcome-text {
    font-size: 1.3rem;
    margin: 0 0 40px 0;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-block;
    padding: 15px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.btn-primary {
    background: white;
    color: #667eea;
}

.btn-primary:hover {
    background: #f8f9fa;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: white;
    color: #667eea;
    transform: translateY(-3px);
}

.features-section {
    margin-bottom: 60px;
}

.features-section h2 {
    text-align: center;
    font-size: 2.5rem;
    margin: 0 0 50px 0;
    color: #333;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 20px;
}

.feature-card h3 {
    font-size: 1.5rem;
    margin: 0 0 15px 0;
    color: #333;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.users-section {
    margin-bottom: 40px;
}

.users-section h2 {
    text-align: center;
    font-size: 2rem;
    margin: 0 0 40px 0;
    color: #333;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.user-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.user-card:hover {
    transform: translateY(-3px);
}

.user-avatar {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.user-card h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.2rem;
}

.user-card p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .welcome-section h1 {
        font-size: 2.2rem;
    }
    
    .welcome-text {
        font-size: 1.1rem;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .users-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}
</style>