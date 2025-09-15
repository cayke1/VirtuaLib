<?php foreach($books as $book): ?>
    <div>
        <h2><?php echo htmlspecialchars($book['titulo']); ?></h2>
        <p>Author: <?php echo htmlspecialchars($book['autor']); ?></p>
        <p>Year: <?php echo htmlspecialchars($book['ano']); ?></p>
    </div>
<?php endforeach; ?>