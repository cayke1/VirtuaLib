function toggleBookmark(element) {
    element.classList.toggle('favorited');
    
    const bookCard = element.closest('.book-card');
    const bookTitle = bookCard.querySelector('.book-title').textContent;
    
    if (element.classList.contains('favorited')) {
        addToFavorites(bookTitle);
    } else {
        removeFromFavorites(bookTitle);
    }
}

function addToFavorites(bookTitle) {
    let favorites = getFavorites();
    if (!favorites.includes(bookTitle)) {
        favorites.push(bookTitle);
        saveFavorites(favorites);
    }
}

function removeFromFavorites(bookTitle) {
    let favorites = getFavorites();
    favorites = favorites.filter(title => title !== bookTitle);
    saveFavorites(favorites);
}

function getFavorites() {
    const favorites = localStorage.getItem('bookFavorites');
    return favorites ? JSON.parse(favorites) : [];
}

function saveFavorites(favorites) {
    localStorage.setItem('bookFavorites', JSON.stringify(favorites));
}

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

function toggleSort(element) {
    element.classList.toggle('active');
    
    const defaultIcon = element.querySelector('.sort-icon-default');
    const activeIcon = element.querySelector('.sort-icon-active');
    
    if (element.classList.contains('active')) {
        defaultIcon.style.display = 'none';
        activeIcon.style.display = 'block';
    } else {
        defaultIcon.style.display = 'block';
        activeIcon.style.display = 'none';
    }
    
    const isActive = element.classList.contains('active');
    localStorage.setItem('sortActive', isActive);
}

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

document.addEventListener('DOMContentLoaded', function() {
    restoreFavorites();
    restoreSortState();
});
