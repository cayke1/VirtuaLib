<head>
    <link rel="stylesheet" href="/public/css/book-details.css">
</head>
<div class="container">
    <a href="/" class="back-btn">&larr; Voltar</a>
    <div class="book-card">
        <div class="book-cover">📖</div>

        <div>
            <div class="book-tags">
                <span class="book-badge outline"><?php echo htmlspecialchars($book['genre']); ?></span>
                <?php if (isset($book['available']) && $book['available']): ?>
                    <span class="book-badge available">Disponível</span>
                <?php else: ?>
                    <span class="book-badge unavailable">Indisponível</span>
                <?php endif; ?>
            </div>

            <h2 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h2>
            <p class="book-meta">👤 <?php echo htmlspecialchars($book['author']); ?></p>
            <p class="book-meta">📅 <?php echo htmlspecialchars($book['year']); ?></p>

            <div class="book-description">
                <h2>Sinopse</h2>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>

            <div class="book-actions">
                <button
                    class="action-button borrow"
                    data-book-id="<?php echo $book['id']; ?>">
                    <?php echo (isset($book['available']) && $book['available']) ? 'Emprestar' : 'Devolver'; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("click", async (e) => {
        if (!e.target.classList.contains("action-button")) return;

        const button = e.target;
        const isReturn = button.classList.contains("return");
        const bookId = button.getAttribute("data-book-id");

        // Função para indicar estado de carregamento
        const setLoadingState = (loading) => {
            button.disabled = loading;
            button.style.cursor = loading ? "not-allowed" : "pointer";
            if (loading) {
                button.textContent = isReturn ? "Devolvendo..." : "Emprestando...";
            }
        };

        // Troca para estado "emprestado"
        const setBorrowedState = () => {
            button.classList.replace("borrow", "return");
            button.textContent = "Devolver";
        };

        // Troca para estado "disponível"
        const setReturnedState = () => {
            button.classList.replace("return", "borrow");
            button.textContent = "Emprestar";
        };

        try {
            setLoadingState(true);

            const response = await fetch(`/${isReturn ? "return" : "borrow"}/${bookId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
            });

            const result = await response.json();

            if (result.success) {
                isReturn ? setReturnedState() : setBorrowedState();
            } else {
                alert("Erro na operação: " + result.message);
            }
        } catch (error) {
            alert("Erro ao conectar com o servidor.");
        }

        setLoadingState(false);
    });
</script>