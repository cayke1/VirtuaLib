## Autenticação no Virtualib

Este documento explica como a autenticação funciona no back-end e dá sugestões de implementação no front-end.

### Visão geral
- **Modelo**: sessão baseada em cookies (PHP session).
- **Tabela**: `Users` com `id`, `name`, `email` (único), `password` (hash Bcrypt), `role` (`user`/`admin`).
- **Endpoints**:
  - `POST /api/auth/register` → cria usuário e inicia sessão.
  - `POST /api/auth/login` → autentica e inicia sessão.
  - `POST /api/auth/logout` → encerra a sessão.
  - `GET /api/auth/me` → retorna o usuário atual na sessão.

### Detalhes técnicos do back-end
- Sessões são inicializadas em `index.php` com cookies `HttpOnly` e `SameSite=Lax`.
- `UserModel` utiliza `password_hash`/`password_verify` para credenciais.
- `AuthController` controla `register`, `login`, `logout` e `me`.
- `AuthGuard` (trait) oferece:
  - `requireAuth()` → 401 quando não autenticado.
  - `requireRole($role)` → 403 quando autenticado sem permissão.
- Exemplo de rota protegida: `BookController@createBook` exige `admin` e está mapeada em `/api/books` (POST).

### Contratos de API
- `POST /api/auth/register`
  - Body: `{ "name", "email", "password" }`
  - 201: `{ message, user }`
  - 409: `{ error: 'Email already in use' }`
- `POST /api/auth/login`
  - Body: `{ "email", "password" }`
  - 200: `{ message, user }`
  - 401: `{ error: 'Email or password incorrect' }`
- `POST /api/auth/logout`
  - 200: `{ message: 'Logged out' }`
- `GET /api/auth/me`
  - 200: `{ user: { id, name, email, role } | null }`
- `POST /api/books` (admin)
  - Body: `{ title, author, genre, year, description, available? }`
  - 200: `{ id, message }`
  - 401/403/400/500 conforme caso

### Regras de autorização
- Use `requireAuth()` em ações que exigem usuário logado.
- Use `requireRole('admin')` para ações administrativas (ex.: criar, editar, remover livros).

### Consumo no front-end
#### Mesma origem (recomendado)
- O cookie de sessão é enviado automaticamente pelos navegadores nas requisições `fetch`.
- Fluxo típico:
  1) `POST /api/auth/login` → guarda estado local do usuário (e.g., em store/context).
  2) `GET /api/auth/me` no carregamento da aplicação para hidratar o usuário.
  3) Esconder/mostrar botões (ex.: "Emprestar", "Criar Livro") de acordo com `user` e `role`.

#### Cross-origin (CORS)
- Habilitar CORS no backend e usar `credentials: 'include'` no `fetch` do front.
- Exemplo:
```js
await fetch('https://api.exemplo.com/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password }),
  credentials: 'include'
});
```

#### Exemplos práticos (front)
- Login:
```js
async function login(email, password) {
  const res = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  if (!res.ok) throw new Error('Login falhou');
  return res.json();
}
```

- Usuário atual:
```js
async function getMe() {
  const res = await fetch('/api/auth/me');
  return res.json();
}
```

- Logout:
```js
async function logout() {
  await fetch('/api/auth/logout', { method: 'POST' });
}
```

- Criar livro (somente admin):
```js
async function createBook(payload) {
  const res = await fetch('/api/books', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  if (!res.ok) throw new Error('Falha ao criar livro');
  return res.json();
}
```

### UI/UX sugeridos (prático)
- Mostrar estado de autenticação no header (nome do usuário, botão sair).
- Desabilitar/esconder ações protegidas quando `user === null` ou `role` inadequada.
- Após login, reexecutar a ação que o usuário tentou (ex.: guardar intenção "criar livro" e reenviar após sucesso).
- Mensagens claras de erro para 401/403.
- Exemplo simples de "store" em front vanilla:
```js
const auth = {
  user: null,
  async hydrate() {
    const { user } = await getMe();
    this.user = user;
    renderAuthUI();
  },
  isAdmin() { return this.user?.role === 'admin'; }
};

function renderAuthUI() {
  document.querySelectorAll('[data-auth=admin-only]')
    .forEach(el => el.style.display = auth.isAdmin() ? '' : 'none');
  document.querySelectorAll('[data-auth=logged]')
    .forEach(el => el.style.display = auth.user ? '' : 'none');
  document.querySelectorAll('[data-auth=anonymous]')
    .forEach(el => el.style.display = auth.user ? 'none' : '');
}

window.addEventListener('DOMContentLoaded', () => auth.hydrate());
```

### CSRF (exemplo prático)
Para proteger requisições de alteração de estado (POST/PUT/PATCH/DELETE), utilize um token CSRF armazenado em sessão e enviado ao front via cookie ou endpoint.

Backend (exemplo conceitual em PHP):
```php
// Em index.php (após session_start)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
setcookie('XSRF-TOKEN', $_SESSION['csrf_token'], 0, '/', '', isset($_SERVER['HTTPS']), false);

// Em um middleware/guard para métodos não-GET
function verifyCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') return;
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $header)) {
        http_response_code(419);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSRF token mismatch']);
        exit;
    }
}
```

Frontend (vanilla):
```js
function getCsrfFromCookie() {
  const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
  return m ? decodeURIComponent(m[1]) : '';
}

async function postWithCsrf(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfFromCookie()
    },
    body: JSON.stringify(body)
  });
  return res;
}

// Exemplo: criar livro (admin)
async function createBook(payload) {
  const res = await postWithCsrf('/api/books', payload);
  if (!res.ok) throw new Error('Falha ao criar livro');
  return res.json();
}
```

### Boas práticas e próximos passos
- Rate limiting em endpoints de login.
- "Remember me": aumentar duração de sessão ou token de lembrança.
- Logs e auditoria de ações administrativas.
- Senhas: continuar usando Bcrypt (padrão do `password_hash`).


