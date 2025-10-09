<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($title ?? 'Books Service - Virtual Library') ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .service-info { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #666; 
            font-size: 14px; 
        }
        h1 { 
            color: #333; 
        }
        .books-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        .book-card { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            transition: transform 0.2s ease;
        }
        .book-card:hover {
            transform: translateY(-2px);
        }
        .book-title { 
            font-weight: bold; 
            margin-bottom: 10px; 
            color: #333; 
            font-size: 16px;
        }
        .book-author { 
            color: #666; 
            margin-bottom: 10px; 
            font-style: italic;
        }
        .book-status { 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
            display: inline-block;
            margin-bottom: 10px;
        }
        .status-available { 
            background: #d4edda; 
            color: #155724; 
        }
        .status-borrowed { 
            background: #f8d7da; 
            color: #721c24; 
        }
        .book-details {
            color: #555;
            font-size: 14px;
        }
        .book-details p {
            margin: 5px 0;
        }
        .model-info { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .model-info h3 {
            margin-top: 0;
            color: #333;
        }
        .model-info ul {
            text-align: left;
            color: #555;
        }
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="service-info">📚 Books Service</div>
        
        <h1>Catálogo de Livros</h1>
        
        <?php if (empty($books)): ?>
            <div class="empty-state">
                <h3>📖 Nenhum livro encontrado</h3>
                <p>O catálogo está vazio no momento.</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <?php 
                    $statusClass = $book['borrowed'] ? 'status-borrowed' : 'status-available';
                    $statusText = $book['borrowed'] ? 'Emprestado' : 'Disponível';
                    ?>
                    <div class="book-card">
                        <div class="book-title"><?= View::escape($book['title']) ?></div>
                        <div class="book-author">por <?= View::escape($book['author']) ?></div>
                        <div class="book-status <?= $statusClass ?>"><?= $statusText ?></div>
                        <div class="book-details">
                            <p><strong>ISBN:</strong> <?= View::escape($book['isbn']) ?></p>
                            <p><strong>Categoria:</strong> <?= View::escape($book['category']) ?></p>
                            <?php if (isset($book['description'])): ?>
                                <p><strong>Descrição:</strong> <?= View::escape(substr($book['description'], 0, 100)) ?><?= strlen($book['description']) > 100 ? '...' : '' ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
