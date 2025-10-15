<?php
$totalBooks = count($books);

$availableBooks = count(array_filter($books, function ($book) {
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
                <span id="books-available" data-label="livros disponíveis"><?php echo $availableBooks; ?> livros disponíveis</span>
            </div>
            <div class="stat-item borrowed">
                <i class="fas fa-book"></i>
                <span id="books-borrowed" data-label="emprestados"><?php echo $borrowedBooks; ?> emprestados</span>
            </div>
        </div>

        <div class="controls">
            <button class="control-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 3H2L10 12.46V19L14 21V12.46L22 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Filtros
            </button>
            <button class="control-button" onclick="toggleSort(this)">
                <svg class="sort-icon-default" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 6H21M7 12H17M10 18H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg class="sort-icon-active" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path d="M6 9L12 3L18 9M18 15L12 21L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Ordenar
            </button>
        </div>
    </div>

    <!-- Grid de livros -->
    <script>
        // Funções simples para ações de livros
        async function requestBook(bookId) {
            return await performBookAction(bookId, false);
        }

        async function returnBook(bookId) {
            return await performBookAction(bookId, true);
        }

        async function performBookAction(bookId, isReturn) {
            const endpoint = isReturn ?
                `/books/api/return/${bookId}` :
                `/books/api/request/${bookId}`;

            console.log('Fazendo requisição para:', endpoint);

            // Encontrar o botão e adicionar estado de loading
            const button = document.querySelector(`[data-book-id="${bookId}"] .action-button`);
            const originalText = button ? button.textContent : '';

            if (button) {
                button.disabled = true;
                button.style.opacity = '0.7';
                button.innerHTML = '<span style="display: inline-block; animation: spin 1s linear infinite;">⟳</span> Processando...';
            }

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                });

                const result = await response.json();
                console.log('Resposta:', result);

                if (response.ok && result.success) {
                    // Atualizar interface
                    updateBookStatus(bookId, isReturn);
                    updateCounters(isReturn ? -1 : 1, isReturn ? 1 : -1);

                    // Efeito visual de sucesso no card
                    const card = document.querySelector(`[data-book-id="${bookId}"]`);
                    if (card) {
                        card.style.transform = 'scale(1.02)';
                        card.style.boxShadow = '0 8px 25px rgba(16, 185, 129, 0.3)';
                        card.style.transition = 'all 0.3s ease';

                        setTimeout(() => {
                            card.style.transform = 'scale(1)';
                            card.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
                        }, 600);
                    }

                    // Mostrar toast de sucesso
                    const action = isReturn ? 'devolvido' : 'solicitado';
                    showSuccess(
                        `Livro ${action} com sucesso!`,
                        'Operação realizada'
                    );

                    // Restaurar botão
                    if (button) {
                        button.disabled = false;
                        button.style.opacity = '1';
                        button.textContent = isReturn ? 'Solicitar' : 'Devolver';
                    }

                    return true;
                } else {
                    // Mostrar toast de erro
                    showError(
                        result.message || "Erro na operação",
                        'Erro'
                    );

                    // Restaurar botão
                    if (button) {
                        button.disabled = false;
                        button.style.opacity = '1';
                        button.textContent = originalText;
                    }

                    return false;
                }
            } catch (error) {
                console.error('Erro:', error);
                showError(
                    "Erro ao conectar com o servidor. Verifique sua conexão.",
                    'Erro de conexão'
                );

                // Restaurar botão
                if (button) {
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.textContent = originalText;
                }

                return false;
            }
        }

        function updateBookStatus(bookId, isReturn) {
            const card = document.querySelector(`[data-book-id="${bookId}"]`);
            if (!card) return;

            const button = card.querySelector('.action-button');
            const statusText = card.querySelector('.status-text');
            const statusDot = card.querySelector('.status-dot');

            if (isReturn) {
                // Devolver: mudar para disponível
                button.classList.replace("return", "borrow");
                button.textContent = "Solicitar";
                if (statusText) statusText.textContent = "Disponível";
                if (statusDot) {
                    statusDot.classList.remove("borrowed");
                    statusDot.classList.add("available");
                }
            } else {
                // Solicitar: mudar para emprestado
                button.classList.replace("borrow", "return");
                button.textContent = "Devolver";
                if (statusText) statusText.textContent = "Emprestado";
                if (statusDot) {
                    statusDot.classList.remove("available");
                    statusDot.classList.add("borrowed");
                }
            }
        }

        function updateCounters(borrowedChange, availableChange) {
            const borrowedCounter = document.getElementById("books-borrowed");
            const availableCounter = document.getElementById("books-available");

            if (borrowedCounter) {
                const borrowedSuffix = borrowedCounter.dataset.label ??
                    borrowedCounter.textContent.replace(/\d+/g, "").trim();
                const borrowedValue = parseInt(borrowedCounter.textContent, 10) || 0;
                const newValue = borrowedValue + borrowedChange;
                borrowedCounter.textContent = borrowedSuffix ?
                    `${newValue} ${borrowedSuffix}`.trim() :
                    `${newValue}`;
            }

            if (availableCounter) {
                const availableSuffix = availableCounter.dataset.label ??
                    availableCounter.textContent.replace(/\d+/g, "").trim();
                const availableValue = parseInt(availableCounter.textContent, 10) || 0;
                const newValue = availableValue + availableChange;
                availableCounter.textContent = availableSuffix ?
                    `${newValue} ${availableSuffix}`.trim() :
                    `${newValue}`;
            }
        }
    </script>
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <?php include __DIR__ . '/components/book-card.php'; ?>
        <?php endforeach; ?>
    </div>

</div>