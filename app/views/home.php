<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtuaLib - Biblioteca Virtual</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>VirtuaLib</h1>
            <p>Sua biblioteca virtual</p>
        </header>

        <main class="books-grid">
            <?php foreach ($books as $book): ?>
            <div class="book-card" onclick="window.location.href='/book/<?= $book['id'] ?>'">
                <div class="book-card-image">
                    <i class="fas fa-book"></i>
                </div>
                <div class="book-card-content">
                    <h3><?= htmlspecialchars($book['titulo']) ?></h3>
                    <p class="author"><?= htmlspecialchars($book['autor']) ?></p>
                    <p class="year"><?= htmlspecialchars($book['ano_publicacao']) ?></p>
                    <span class="availability <?= $book['disponivel'] ? 'available' : 'unavailable' ?>">
                        <i class="fas fa-circle"></i>
                        <?= $book['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>