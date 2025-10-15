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
            return true;
        } else {
            alert(result.message || "Erro na operação");
            return false;
        }
    } catch (error) {
        console.error('Erro:', error);
        alert("Erro ao conectar com o servidor.");
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
