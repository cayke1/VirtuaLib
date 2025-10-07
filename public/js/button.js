document.addEventListener("click", async (e) => {
    if (!e.target.classList.contains("action-button")) return;

    const button = e.target;
    const isReturn = button.classList.contains("return");
    const bookId = button.getAttribute("data-book-id");

    const borrowedCounter = document.getElementById("books-borrowed");
    const availableCounter = document.getElementById("books-available");

    const borrowedSuffix = borrowedCounter
        ? borrowedCounter.dataset.label ?? borrowedCounter.textContent.replace(/\d+/g, "").trim()
        : null;
    const availableSuffix = availableCounter
        ? availableCounter.dataset.label ?? availableCounter.textContent.replace(/\d+/g, "").trim()
        : null;

    const parseCounterValue = (element) => {
        if (!element) return null;
        const numeric = parseInt(element.textContent, 10);
        return Number.isNaN(numeric) ? 0 : numeric;
    };

        const setLoadingState = (loading) => {
        button.disabled = loading;
        button.style.cursor = loading ? "not-allowed" : "pointer";
        if (loading) {
            button.textContent = isReturn ? "Devolvendo..." : "Solicitando...";
        }
    };

    const updateCounters = (borrowedChange, availableChange) => {
        const borrowedValue = parseCounterValue(borrowedCounter);
        if (borrowedValue !== null && borrowedCounter) {
            const newValue = borrowedValue + borrowedChange;
            borrowedCounter.textContent = borrowedSuffix
                ? `${newValue} ${borrowedSuffix}`.trim()
                : `${newValue}`;
        }

        const availableValue = parseCounterValue(availableCounter);
        if (availableValue !== null && availableCounter) {
            const newValue = availableValue + availableChange;
            availableCounter.textContent = availableSuffix
                ? `${newValue} ${availableSuffix}`.trim()
                : `${newValue}`;
        }
    };

    const setBorrowedState = () => {
        button.classList.replace("borrow", "return");
        button.textContent = "Devolver";
        updateCounters(+1, -1);

        const card = button.closest(".book-card");
        if (card) {
            const statusText = card.querySelector(".status-text");
            const statusDot = card.querySelector(".status-dot");
            if (statusText) {
                statusText.textContent = "Emprestado";
            }
            if (statusDot) {
                statusDot.classList.remove("available");
                statusDot.classList.add("borrowed");
            }
        }
    };

    const setReturnedState = () => {
        button.classList.replace("return", "borrow");
        button.textContent = "Solicitar";
        updateCounters(-1, +1);

        const card = button.closest(".book-card");
        if (card) {
            const statusText = card.querySelector(".status-text");
            const statusDot = card.querySelector(".status-dot");
            if (statusText) {
                statusText.textContent = "Disponível";
            }
            if (statusDot) {
                statusDot.classList.remove("borrowed");
                statusDot.classList.add("available");
            }
        }
    };

    try {
        setLoadingState(true);

        const response = await fetch(`/${isReturn ? "return" : "request"}/${bookId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        });

        let result = {};
        try {
            result = await response.json();
        } catch (parseError) {
            result = {};
        }

        if (response.ok && result.success) {
            isReturn ? setReturnedState() : setBorrowedState();
        } else {
            const message = result.message || "Erro na operação";
            alert(message);
        }
    } catch (error) {
        alert("Erro ao conectar com o servidor.");
    }

    setLoadingState(false);
});
