document.addEventListener("click", async (e) => {
    if (!e.target.classList.contains("action-button")) return;

    const button = e.target;
    const isReturn = button.classList.contains("return");
    const bookId = button.getAttribute("data-book-id");

    const borrowedCounter = document.getElementById("books-borrowed");
    const availableCounter = document.getElementById("books-available");

    // Função para indicar estado de carregamento
    const setLoadingState = (loading) => {
        button.disabled = loading;
        button.style.cursor = loading ? "not-allowed" : "pointer";
        if (loading) {
            button.textContent = isReturn ? "Devolvendo..." : "Emprestando...";
        }
    };

    // Função para atualizar os contadores
    const updateCounters = (borrowedChange, availableChange) => {
        borrowedCounter.textContent =
            parseInt(borrowedCounter.textContent) + borrowedChange + " emprestados";
        availableCounter.textContent =
            parseInt(availableCounter.textContent) + availableChange + " livros disponíveis";
    };

    // Troca para estado "emprestado"
    const setBorrowedState = () => {
        button.classList.replace("borrow", "return");
        button.textContent = "Devolver";
        updateCounters(+1, -1);
    };

    // Troca para estado "disponível"
    const setReturnedState = () => {
        button.classList.replace("return", "borrow");
        button.textContent = "Emprestar";
        updateCounters(-1, +1);
    };

    try {
        setLoadingState(true);

        // Corrigido uso de template string no fetch
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
        // console.error(error);
        // alert("Erro ao conectar com o servidor.");
    }

    setLoadingState(false);
});
