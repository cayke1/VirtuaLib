<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['titulo']) ?> - Detalhes do Livro</title>
    <link rel="stylesheet" href="/app/views/assets/css/Book.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <button class="back-button" onclick="goBack()">
                <i class="fas fa-arrow-left"></i>

                Voltar
            </button>
        </header>

        <main class="main-content">
            <div class="book-image-container">
                <div class="book-image">
                    <i class="fas fa-book placeholder-icon"></i>
                </div>
            </div>

            <div class="book-details">
                <div class="book-meta">
                    <span class="genre"><?= htmlspecialchars($book['genero'] ?? 'Não informado') ?></span>
                    <span class="availability <?= $book['disponivel'] ? 'available' : 'unavailable' ?>">
                        <i class="fas fa-circle"></i>
                        <?= $book['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                    </span>
                </div>

                <h1 class="book-title"><?= htmlspecialchars($book['titulo']) ?></h1>

                <div class="book-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($book['autor']) ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span><?= htmlspecialchars($book['ano_publicacao']) ?></span>
                    </div>
                    <?php if (!empty($book['editora'])): ?>
                    <div class="info-item">
                        <i class="fas fa-building"></i>
                        <span><?= htmlspecialchars($book['editora']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($book['isbn'])): ?>
                    <div class="info-item">
                        <i class="fas fa-barcode"></i>
                        <span>ISBN: <?= htmlspecialchars($book['isbn']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <section class="synopsis">
                    <h2>Sinopse</h2>
                    <p>
                        <?php
                        if (!empty($book['sinopse'])) {
                            // Usa a sinopse do banco de dados
                            echo nl2br(htmlspecialchars($book['sinopse']));
                        } else {
                            // Fallback para sinopse padrão baseada no título
                            echo htmlspecialchars($book['titulo']) . ' é uma obra de ' . htmlspecialchars($book['autor']) . ', publicada em ' . htmlspecialchars($book['ano_publicacao']) . '.';
                            if (!empty($book['genero'])) {
                                echo ' Esta obra do gênero ' . htmlspecialchars($book['genero']) . ' oferece uma experiência única de leitura.';
                            }
                            echo ' Uma leitura recomendada para todos os amantes da boa literatura.';
                        }
                        ?>
                    </p>
                </section>

                <div class="action-buttons">
                    <button class="btn borrow-btn <?= $book['disponivel'] ? '' : 'disabled' ?>" 
                            <?= $book['disponivel'] ? '' : 'disabled' ?>>
                        <i class="fas fa-book"></i>
                        <?= $book['disponivel'] ? 'Emprestar Livro' : 'Indisponível para Empréstimo' ?>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }
        
        // Interação com os botões
        document.querySelector('.borrow-btn')?.addEventListener('click', function() {
            if (!this.disabled) {
                alert('Livro emprestado com sucesso!');
                this.disabled = true;
                this.classList.add('disabled');
                this.innerHTML = '<i class="fas fa-check"></i> Empréstimo Realizado';
            }
        });
        
        document.querySelector('.favorite-btn')?.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.innerHTML = '<i class="fas fa-heart"></i> Remover dos Favoritos';
                alert('Livro adicionado aos favoritos!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.innerHTML = '<i class="far fa-heart"></i> Adicionar aos Favoritos';
                alert('Livro removido dos favoritos!');
            }
        });
    </script>
</body>
</html>