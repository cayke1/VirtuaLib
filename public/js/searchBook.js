const searchInput = document.getElementById("search-input");
const searchResults = document.getElementById("search-results");
const searchLoading = document.getElementById("search-loading");
const searchItems = document.getElementById("search-items");

const searchInputMobile = document.getElementById("search-input-mobile");
const searchResultsMobile = document.getElementById("search-results-mobile");
const searchLoadingMobile = document.getElementById("search-loading-mobile");
const searchItemsMobile = document.getElementById("search-items-mobile");

const cache = new Map();
let debounceTimeout;

function initSearch(input, results, loading, items) {
    if (!input || !results || !loading || !items) return;
    
    input.addEventListener("keyup", function() {
        search(this.value.trim(), results, loading, items);
    });
}

function search(query, resultsContainer, loadingElement, itemsContainer) {
    if (query.length < 2) {
        hideResults(resultsContainer, loadingElement, itemsContainer);
        return;
    }

    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => 
        searchBooks(query, resultsContainer, loadingElement, itemsContainer), 300
    );
}

function hideResults(resultsContainer, loadingElement, itemsContainer) {
    resultsContainer.classList.remove("show");
    loadingElement.classList.remove("show");
    itemsContainer.innerHTML = "";
}

function showLoading(resultsContainer, loadingElement, itemsContainer) {
    resultsContainer.classList.add("show");
    loadingElement.classList.add("show");
    itemsContainer.innerHTML = "";
}

async function searchBooks(query, resultsContainer, loadingElement, itemsContainer) {
    if (cache.has(query)) {
        renderResults(cache.get(query), resultsContainer, loadingElement, itemsContainer);
        return;
    }

    showLoading(resultsContainer, loadingElement, itemsContainer);

    try {
        const res = await fetch("/api/search?q=" + encodeURIComponent(query));
        const data = await res.json();
        cache.set(query, data);
        renderResults(data, resultsContainer, loadingElement, itemsContainer);
    } catch (err) {
        console.error("Erro na busca:", err);
        showError(resultsContainer, loadingElement, itemsContainer);
    }
}

function renderResults(data, resultsContainer, loadingElement, itemsContainer) {
    loadingElement.classList.remove("show");
    
    if (data.length > 0) {
        itemsContainer.innerHTML = data
            .map(book => `
                <div class="search-item" onclick="window.location.href='/details/${book.id}'">
                    <div class="search-item-title">${book.title || "Sem título"}</div>
                    <div class="search-item-author">${book.author || "Autor desconhecido"}</div>
                    <div class="search-item-year">${book.year || "Ano não informado"}</div>
                </div>
            `)
            .join("");
    } else {
        itemsContainer.innerHTML = '<div class="search-no-results">Nenhum resultado encontrado.</div>';
    }
    
    resultsContainer.classList.add("show");
}

function showError(resultsContainer, loadingElement, itemsContainer) {
    loadingElement.classList.remove("show");
    itemsContainer.innerHTML = '<div class="search-error">Erro ao buscar resultados. Tente novamente.</div>';
    resultsContainer.classList.add("show");
}

initSearch(searchInput, searchResults, searchLoading, searchItems);
initSearch(searchInputMobile, searchResultsMobile, searchLoadingMobile, searchItemsMobile);

document.addEventListener("click", function (event) {
    const isDesktopSearch = event.target.closest(".search-container");
    const isMobileSearch = event.target.closest(".search-container-mobile");
    
    if (!isDesktopSearch && !isMobileSearch) {
        hideResults(searchResults, searchLoading, searchItems);
        hideResults(searchResultsMobile, searchLoadingMobile, searchItemsMobile);
    }
});
