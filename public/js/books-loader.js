document.addEventListener("DOMContentLoaded", async () => {
  const grid = document.getElementById("books-grid");
  grid.innerHTML = `<div class="loading">Carregando livros...</div>`;

  let userId = null;

  try {
    // 1️⃣ Obter usuário autenticado (com tratamento seguro)
    const userRes = await fetch("/auth/api/me");
    const userData = await userRes.json();

    // Se houver usuário autenticado, pega o ID; senão, mantém null
    if (userData && userData.user && userData.user.id) {
      userId = userData.user.id;
    } else {
      console.warn("⚠️ Nenhum usuário autenticado — carregando como visitante.");
    }

    // 2️⃣ Carregar livros (sempre, mesmo sem userId)
    await carregarLivros(userId);

    // 3️⃣ Atualizar estatísticas (só se o usuário estiver logado)
    if (userId) {
      carregarEstatisticas();

      // 4️⃣ Atualizar automaticamente a cada 30s
      setInterval(() => atualizarStatusLivros(userId), 30000);
    }

  } catch (error) {
    console.error("Erro ao inicializar:", error);
    grid.innerHTML = `<p style="color: red;">Erro ao carregar livros: ${error.message}</p>`;
    // Em caso de erro, ainda tenta carregar os livros como visitante
    await carregarLivros(null);
  }
});

/* ==============================
 * 📚 Carregar livros + estados
 * ============================== */
async function carregarLivros(userId) {
  const grid = document.getElementById("books-grid");

  try {
    // Sempre busca os livros
    const booksRes = await fetch("/books/api/list");
    const booksData = await booksRes.json();

    if (!booksData.success || !Array.isArray(booksData.books)) {
      throw new Error("Resposta inválida da API de livros");
    }

    let borrowedBookIds = [];
    let pendingBookIds = [];

    // Só busca dados específicos se o usuário estiver logado
    if (userId) {
      const [borrowsRes, pendingRes] = await Promise.all([
        fetch(`/books/api/get-user-borrows/${userId}`),
        fetch("/books/api/pending-requests")
      ]);

      const borrowsData = await borrowsRes.json();
      const pendingData = await pendingRes.json();

      borrowedBookIds = (borrowsData.success ? borrowsData.borrows : []).map(Number);
      pendingBookIds = (pendingData.requests || [])
        .filter(req => req.user_id === userId)
        .map(req => Number(req.book_id));
    }

    console.log("📚 Livros carregados:", booksData.books);
    console.log("🧾 Emprestados:", borrowedBookIds);
    console.log("⏳ Pendentes:", pendingBookIds);

    grid.innerHTML = ""; // limpa o "Carregando..."

    // Renderiza os livros normalmente
    booksData.books.forEach(book => {
      const isBorrowed = borrowedBookIds.includes(Number(book.id));
      const isPending = pendingBookIds.includes(Number(book.id));
      const card = createBookCard(book, isBorrowed, isPending);
      grid.appendChild(card);
    });

  } catch (error) {
    console.error("Erro ao carregar livros:", error);
    grid.innerHTML = `<p style="color: red;">Erro ao carregar livros: ${error.message}</p>`;
  }
}

/* ==============================
 * 🔁 Atualizar status dos livros
 * ============================== */
async function atualizarStatusLivros(userId) {
  try {
    const [borrowsRes, pendingRes] = await Promise.all([
      fetch(`/books/api/get-user-borrows/${userId}`),
      fetch("/books/api/pending-requests")
    ]);

    const borrowsData = await borrowsRes.json();
    const pendingData = await pendingRes.json();

    const borrowedBookIds = (borrowsData.success ? borrowsData.borrows : []).map(Number);
    const pendingBookIds = (pendingData.requests || [])
      .filter(req => req.user_id === userId)
      .map(req => Number(req.book_id));

    document.querySelectorAll(".book-card").forEach(card => {
      const bookId = Number(card.getAttribute("data-book-id"));
      const button = card.querySelector(".action-button");

      if (!button) return;

      if (pendingBookIds.includes(bookId)) {
        button.textContent = "Pendente";
        button.className = "action-button pending";
        button.disabled = true;
        button.removeAttribute("onclick");

      } else if (borrowedBookIds.includes(bookId)) {
        button.textContent = "Devolver";
        button.className = "action-button return";
        button.disabled = false;
        button.setAttribute("onclick", `returnBook(${bookId})`);

      } else {
        const statusText = card.querySelector(".status-text")?.textContent || "";
        if (statusText.includes("Emprestado")) {
          button.textContent = "Emprestado";
          button.className = "action-button borrowed";
          button.disabled = true;
          button.removeAttribute("onclick");
        } else {
          button.textContent = "Solicitar";
          button.className = "action-button borrow";
          button.disabled = false;
          button.setAttribute("onclick", `requestBook(${bookId})`);
        }
      }
    });

  } catch (error) {
    console.error("Erro ao atualizar status:", error);
  }
}

/* ==============================
 * 🎴 Criar card de livro
 * ============================== */
function createBookCard(book, isBorrowed, isPending) {
  const isAvailable = parseInt(book.available) === 1;
  const card = document.createElement("div");
  card.className = "book-card";
  card.setAttribute("data-book-id", book.id);

  let buttonText = "Solicitar";
  let buttonClass = "borrow";
  let buttonDisabled = false;
  let buttonAction = `requestBook(${book.id})`;

  if (isPending) {
    buttonText = "Pendente";
    buttonClass = "pending";
    buttonDisabled = true;
    buttonAction = "";
  } else if (isBorrowed) {
    buttonText = "Devolver";
    buttonClass = "return";
    buttonAction = `returnBook(${book.id})`;
  } else if (!isAvailable) {
    buttonText = "Emprestado";
    buttonClass = "borrowed";
    buttonDisabled = true;
    buttonAction = "";
  }

  card.innerHTML = `
    <div 
      class="book-cover-container"
      onclick="window.location.href='/books/details/${book.id}'"
      style="cursor: pointer;"
    >
      ${
        book.cover_image
          ? `<img src="${book.cover_image}" alt="Capa de ${book.title}" class="book-cover-image" loading="lazy" />`
          : `<div class="book-cover-placeholder">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>`
      }
    </div>

    <div class="book-info">
      <div class="book-status">
        <span class="status-dot ${isAvailable ? "available" : "borrowed"}"></span>
        <span class="status-text">${isAvailable ? "Disponível" : "Emprestado"}</span>
      </div>

      <h3 class="book-title">${book.title}</h3>
      <p class="book-author">${book.author}</p>
      <p class="book-genre-year">${book.genre} • ${book.year}</p>
      <p class="book-description">${book.description}</p>
    </div>

    <div class="book-actions">
      <button 
        class="action-button ${buttonClass}"
        ${buttonDisabled ? "disabled" : ""}
        ${buttonAction ? `onclick="${buttonAction}"` : ""}>
        ${buttonText}
      </button>
    </div>
  `;

  return card;
}

/* ==============================
 * 📘 Ações: Solicitar / Devolver
 * ============================== */
async function requestBook(bookId) {
  try {
    const res = await fetch(`/books/api/request/${bookId}`, {
      method: "POST",
      credentials: "same-origin",
    });
    const data = await res.json();

    if (data.success) {
      alert("📚 Solicitação enviada com sucesso!");
      // Atualiza todos os status (pendentes incluídos)
      const user = await (await fetch("/auth/api/me")).json();
      await atualizarStatusLivros(user.user.id);
    } else {
      alert("❌ Falha ao solicitar o livro.");
    }
  } catch (error) {
    console.error("Erro ao solicitar livro:", error);
  }
}

async function returnBook(bookId) {
  try {
    const res = await fetch(`/books/api/return/${bookId}`, { method: "POST" });
    const data = await res.json();
    if (data.success) {
      alert("✅ Livro devolvido!");
      const user = await (await fetch("/auth/api/me")).json();
      await Promise.all([carregarEstatisticas(), atualizarStatusLivros(user.user.id)]);
    } else {
      alert("Erro ao devolver o livro.");
    }
  } catch (err) {
    console.error("Erro ao devolver livro:", err);
  }
}

/* ==============================
 * 📊 Estatísticas
 * ============================== */
async function carregarEstatisticas() {
  try {
    const response = await fetch("/books/api/list");
    const data = await response.json();

    const livros = data.books || [];
    let disponiveis = 0, emprestados = 0;

    livros.forEach(livro => {
      if (parseInt(livro.available) === 1) disponiveis++;
      else emprestados++;
    });

    const availableEl = document.getElementById("books-available");
    const borrowedEl = document.getElementById("books-borrowed");

    if (availableEl) availableEl.textContent = `${disponiveis} livros disponíveis`;
    if (borrowedEl) borrowedEl.textContent = `${emprestados} emprestados`;
  } catch (erro) {
    console.error("Erro ao carregar estatísticas:", erro);
  }
}
