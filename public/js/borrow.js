/**
 * JavaScript para funcionalidade de empréstimo e devolução de livros
 */

async function borrowBook(bookId) {
    try {
        const response = await fetch('/borrow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload(); // Recarrega a página para atualizar o status
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro ao emprestar livro:', error);
        alert('Erro ao emprestar livro. Tente novamente.');
    }
}

async function returnBook(bookId) {
    try {
        const response = await fetch('/return', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload(); // Recarrega a página para atualizar o status
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro ao devolver livro:', error);
        alert('Erro ao devolver livro. Tente novamente.');
    }
}

// Event listeners para os botões
document.addEventListener('DOMContentLoaded', () => {
    const borrowButtons = document.querySelectorAll('.action-button.borrow');
    const returnButtons = document.querySelectorAll('.action-button.return');

    borrowButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookCard = this.closest('.book-card');
            const bookId = bookCard.dataset.bookId;
            if (bookId) {
                borrowBook(bookId);
            }
        });
    });

    returnButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookCard = this.closest('.book-card');
            const bookId = bookCard.dataset.bookId;
            if (bookId) {
                returnBook(bookId);
            }
        });
    });
});
