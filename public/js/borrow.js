// Funções simples para ações de livros
async function requestBook(bookId) {
    return await performBookAction(bookId, false);
}

async function returnBook(bookId) {
    return await performBookAction(bookId, true);
}

async function performBookAction(bookId, isReturn) {
    const endpoint = isReturn 
        ? `/books/api/return/${bookId}`
        : `/books/api/request/${bookId}`;

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
                button.textContent = isReturn ? 'Solicitar' : 'Solicitado';
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
        button.textContent = "Solicitado";
        if (statusText) statusText.textContent = "Disponível";
        if (statusDot) {
            statusDot.classList.remove("available");
            statusDot.classList.add("pending");
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
        borrowedCounter.textContent = borrowedSuffix
            ? `${newValue} ${borrowedSuffix}`.trim()
            : `${newValue}`;
    }

    if (availableCounter) {
        const availableSuffix = availableCounter.dataset.label ?? 
            availableCounter.textContent.replace(/\d+/g, "").trim();
        const availableValue = parseInt(availableCounter.textContent, 10) || 0;
        const newValue = availableValue + availableChange;
        availableCounter.textContent = availableSuffix
            ? `${newValue} ${availableSuffix}`.trim()
            : `${newValue}`;
    }
}
