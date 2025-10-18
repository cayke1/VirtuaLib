// dashboard-api.js
// Simples utilitário para carregar dados do Dashboard via API

async function fetchJson(url, opts = {}) {
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.statusText}`);
    return res.json();
}

export async function loadGeneralStats() {
    return fetchJson('/api/stats/general');
}

export async function loadBorrowsByMonth() {
    return fetchJson('/api/stats/borrows-by-month');
}

export async function loadTopBooks() {
    return fetchJson('/api/stats/top-books');
}

export async function loadBooksByCategory() {
    return fetchJson('/api/stats/books-by-category');
}

export async function loadRecentActivities() {
    return fetchJson('/api/stats/recent-activities');
}

export async function loadUserProfile() {
    return fetchJson('/api/stats/user-profile');
}
export async function loadFallbackStats() {
    return fetchJson('/api/stats/fallback');
}   
