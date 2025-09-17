const searchInput = document.querySelector(".search-input");
const resultsContainer = document.getElementById("results");

const cache = new Map();
let debounceTimeout;

searchInput.addEventListener("keyup", function () {
  const query = this.value.trim();

  if (query.length < 2) {
    resultsContainer.innerHTML = "";
    resultsContainer.classList.remove("show");
    return;
  }

  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => searchBooks(query), 300);
});

async function searchBooks(query) {
  if (cache.has(query)) {
    renderResults(cache.get(query));
    return;
  }

  try {
    const res = await fetch("/search?q=" + encodeURIComponent(query));
    const data = await res.json();
    cache.set(query, data);
    renderResults(data);
  } catch (err) {
    console.error("Erro na busca:", err);
    resultsContainer.innerHTML =
      "<div class='no-results'>Erro ao buscar resultados.</div>";
    resultsContainer.classList.add("show");
  }
}

function renderResults(data) {
  if (data.length > 0) {
    resultsContainer.innerHTML = data
      .map(
        (book) => `
                <div class="book-item" onclick="window.location.href='/book/${
                  book.id
                }'">
                    <strong>${book.title || "Sem título"}</strong><br>
                    <em>${book.author || "Autor desconhecido"}</em> (${
          book.year || "Ano não informado"
        })
                </div>
            `
      )
      .join("");
  } else {
    resultsContainer.innerHTML =
      "<div class='book-item'>Nenhum resultado encontrado.</div>";
  }
  resultsContainer.classList.add("show");
}

document.addEventListener("click", function (event) {
  if (!event.target.closest(".search-container")) {
    resultsContainer.classList.remove("show");
  }
});
