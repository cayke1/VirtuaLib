// Função para alternar o estado de favorito
function toggleBookmark(element) {
    element.classList.toggle('favorited');
    
    // Opcional: Salvar no localStorage para persistir o estado
    const bookCard = element.closest('.book-card');
    const bookTitle = bookCard.querySelector('.book-title').textContent;
    
    if (element.classList.contains('favorited')) {
        // Adicionar aos favoritos
        addToFavorites(bookTitle);
    } else {
        // Remover dos favoritos
        removeFromFavorites(bookTitle);
    }
}

// Função para adicionar aos favoritos
function addToFavorites(bookTitle) {
    let favorites = getFavorites();
    if (!favorites.includes(bookTitle)) {
        favorites.push(bookTitle);
        saveFavorites(favorites);
    }
}

// Função para remover dos favoritos
function removeFromFavorites(bookTitle) {
    let favorites = getFavorites();
    favorites = favorites.filter(title => title !== bookTitle);
    saveFavorites(favorites);
}

// Função para obter favoritos do localStorage
function getFavorites() {
    const favorites = localStorage.getItem('bookFavorites');
    return favorites ? JSON.parse(favorites) : [];
}

// Função para salvar favoritos no localStorage
function saveFavorites(favorites) {
    localStorage.setItem('bookFavorites', JSON.stringify(favorites));
}

// Função para restaurar estado dos favoritos ao carregar a página
function restoreFavorites() {
    const favorites = getFavorites();
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        const title = card.querySelector('.book-title').textContent;
        const bookmarkIcon = card.querySelector('.bookmark-icon');
        
        if (favorites.includes(title)) {
            bookmarkIcon.classList.add('favorited');
        }
    });
}

// Função para alternar o estado do botão de ordenar
function toggleSort(element) {
    element.classList.toggle('active');
    
    // Alternar ícones
    const defaultIcon = element.querySelector('.sort-icon-default');
    const activeIcon = element.querySelector('.sort-icon-active');
    
    if (element.classList.contains('active')) {
        defaultIcon.style.display = 'none';
        activeIcon.style.display = 'block';
    } else {
        defaultIcon.style.display = 'block';
        activeIcon.style.display = 'none';
    }
    
    // Opcional: Salvar estado no localStorage
    const isActive = element.classList.contains('active');
    localStorage.setItem('sortActive', isActive);
}

// Função para restaurar estado do botão de ordenar
function restoreSortState() {
    const sortButton = document.querySelector('.control-button[onclick="toggleSort(this)"]');
    if (sortButton) {
        const isActive = localStorage.getItem('sortActive') === 'true';
        const defaultIcon = sortButton.querySelector('.sort-icon-default');
        const activeIcon = sortButton.querySelector('.sort-icon-active');
        
        if (isActive) {
            sortButton.classList.add('active');
            defaultIcon.style.display = 'none';
            activeIcon.style.display = 'block';
        } else {
            sortButton.classList.remove('active');
            defaultIcon.style.display = 'block';
            activeIcon.style.display = 'none';
        }
    }
}

// Restaurar favoritos e estado do botão quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    restoreFavorites();
    restoreSortState();
});
