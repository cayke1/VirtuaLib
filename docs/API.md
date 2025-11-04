# Documentação da API - VirtuaLib

## Índice

- [Visão Geral](#visão-geral)
- [Autenticação](#autenticação)
- [Serviços](#serviços)
- [Auth Service](#auth-service)
- [Books Service](#books-service)
- [Notifications Service](#notifications-service)
- [Dashboard Service](#dashboard-service)
- [Códigos de Status](#códigos-de-status)
- [Exemplos de Requisição](#exemplos-de-requisição)

## Visão Geral

A VirtuaLib utiliza uma arquitetura de microsserviços com 4 serviços independentes que se comunicam através de um API Gateway centralizado.

**Base URL:** `http://localhost:8080` (API Gateway)

**Formato de Dados:** JSON

**Autenticação:** Session-based (cookies) com Redis como backend de sessão

## Autenticação

A API utiliza autenticação baseada em sessão. Após o login bem-sucedido, um cookie de sessão (`PHPSESSID`) é retornado e deve ser incluído em todas as requisições subsequentes.

### Fluxo de Autenticação

1. **Registro:** `POST /auth/api/register`
2. **Login:** `POST /auth/api/login` - Retorna cookie de sessão
3. **Usar cookie em requisições protegidas**
4. **Logout:** `POST /auth/api/logout`

### Roles de Usuário

- `user` - Usuário comum (pode emprestar livros, ver notificações)
- `admin` - Administrador (acesso total, incluindo CRUD de livros e aprovação de empréstimos)

---

## Auth Service

Base: `/auth/api/*`

### POST /auth/api/register

Registra um novo usuário no sistema.

**Request Body:**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Usuário registrado com sucesso",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "user",
    "created_at": "2025-11-04T10:00:00Z"
  }
}
```

**Response 400:**
```json
{
  "error": true,
  "message": "Email já cadastrado"
}
```

---

### POST /auth/api/login

Autentica um usuário e cria uma sessão.

**Request Body:**
```json
{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "user"
  }
}
```

**Response 401:**
```json
{
  "error": true,
  "message": "Credenciais inválidas"
}
```

---

### POST /auth/api/logout

Encerra a sessão do usuário.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "success": true,
  "message": "Logout realizado com sucesso"
}
```

---

### GET /auth/api/me

Retorna informações do usuário autenticado.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "id": 1,
  "name": "João Silva",
  "email": "joao@example.com",
  "role": "user",
  "created_at": "2025-11-04T10:00:00Z"
}
```

---

### POST /auth/api/update-profile

Atualiza o perfil do usuário autenticado.

**Autenticação:** Requerida

**Request Body:**
```json
{
  "name": "João Pedro Silva",
  "email": "joao.pedro@example.com"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Perfil atualizado com sucesso"
}
```

---

### GET /auth/api/user-stats

Retorna estatísticas do usuário autenticado.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "total_borrows": 15,
  "active_borrows": 2,
  "overdue_borrows": 0
}
```

---

## Books Service

Base: `/api/*`

### GET /api/search

Busca livros por título, autor ou gênero.

**Query Parameters:**
- `q` (string, obrigatório) - Termo de busca

**Exemplo:** `GET /api/search?q=harry+potter`

**Response 200:**
```json
[
  {
    "id": 1,
    "title": "Harry Potter e a Pedra Filosofal",
    "author": "J.K. Rowling",
    "genre": "Fantasia",
    "year": 1997,
    "description": "O primeiro livro da série...",
    "available": 3,
    "cover_image": "https://cdn.nutria.digital/covers/hp1.jpg",
    "pdf_src": "https://cdn.nutria.digital/pdfs/hp1.pdf"
  }
]
```

---

### GET /api/books

Lista todos os livros do catálogo.

**Autenticação:** Requerida

**Response 200:**
```json
[
  {
    "id": 1,
    "title": "1984",
    "author": "George Orwell",
    "genre": "Ficção Distópica",
    "year": 1949,
    "available": 5,
    "cover_image": "...",
    "pdf_src": "..."
  }
]
```

---

### GET /api/books/{id}

Retorna detalhes de um livro específico.

**Autenticação:** Requerida

**Path Parameters:**
- `id` (integer) - ID do livro

**Response 200:**
```json
{
  "id": 1,
  "title": "1984",
  "author": "George Orwell",
  "genre": "Ficção Distópica",
  "year": 1949,
  "description": "Romance distópico...",
  "available": 5,
  "cover_image": "https://cdn.nutria.digital/covers/1984.jpg",
  "pdf_src": "https://cdn.nutria.digital/pdfs/1984.pdf",
  "created_at": "2025-01-15T08:30:00Z"
}
```

**Response 404:**
```json
{
  "error": true,
  "message": "Livro não encontrado"
}
```

---

### POST /api/books/create

Cria um novo livro no catálogo.

**Autenticação:** Requerida (Admin)

**Content-Type:** `multipart/form-data`

**Form Data:**
- `title` (string, obrigatório)
- `author` (string, obrigatório)
- `genre` (string, obrigatório)
- `year` (integer, obrigatório)
- `available` (integer, obrigatório)
- `description` (string, opcional)
- `cover_image` (file, opcional) - Arquivo de imagem
- `pdf_file` (file, opcional) - Arquivo PDF

**Response 200:**
```json
{
  "success": true,
  "message": "Livro criado com sucesso",
  "book": {
    "id": 10,
    "title": "O Senhor dos Anéis",
    "author": "J.R.R. Tolkien",
    "genre": "Fantasia",
    "year": 1954,
    "available": 3,
    "cover_image": "https://cdn.nutria.digital/covers/lotr.jpg",
    "pdf_src": "https://cdn.nutria.digital/pdfs/lotr.pdf"
  }
}
```

**Response 401:**
```json
{
  "error": true,
  "message": "Acesso negado. Somente administradores."
}
```

---

### PUT /api/books/{id}/update

Atualiza um livro existente.

**Autenticação:** Requerida (Admin)

**Path Parameters:**
- `id` (integer) - ID do livro

**Content-Type:** `multipart/form-data`

**Form Data:** (todos opcionais)
- `title` (string)
- `author` (string)
- `genre` (string)
- `year` (integer)
- `available` (integer)
- `description` (string)
- `cover_image` (file)
- `pdf_file` (file)

**Response 200:**
```json
{
  "success": true,
  "message": "Livro atualizado com sucesso"
}
```

---

### DELETE /api/books/{id}/delete

Remove um livro do catálogo.

**Autenticação:** Requerida (Admin)

**Path Parameters:**
- `id` (integer) - ID do livro

**Response 200:**
```json
{
  "success": true,
  "message": "Livro deletado com sucesso"
}
```

---

### POST /api/request/{id}

Solicita empréstimo de um livro.

**Autenticação:** Requerida

**Path Parameters:**
- `id` (integer) - ID do livro

**Response 200:**
```json
{
  "success": true,
  "message": "Solicitação de empréstimo criada com sucesso"
}
```

**Response 400:**
```json
{
  "error": true,
  "message": "Livro não disponível para empréstimo"
}
```

---

### POST /api/return/{id}

Devolve um livro emprestado.

**Autenticação:** Requerida

**Path Parameters:**
- `id` (integer) - ID do empréstimo (borrow_id)

**Response 200:**
```json
{
  "success": true,
  "message": "Livro devolvido com sucesso"
}
```

---

### POST /api/approve/{requestId}

Aprova uma solicitação de empréstimo.

**Autenticação:** Requerida (Admin)

**Path Parameters:**
- `requestId` (integer) - ID da solicitação

**Response 200:**
```json
{
  "success": true,
  "message": "Solicitação aprovada com sucesso"
}
```

**Nota:** Este endpoint também dispara uma notificação para o usuário solicitante.

---

### POST /api/reject/{requestId}

Rejeita uma solicitação de empréstimo.

**Autenticação:** Requerida (Admin)

**Path Parameters:**
- `requestId` (integer) - ID da solicitação

**Response 200:**
```json
{
  "success": true,
  "message": "Solicitação rejeitada"
}
```

---

### GET /api/pending-requests

Lista todas as solicitações de empréstimo pendentes.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "id": 5,
    "user_id": 3,
    "book_id": 7,
    "user_name": "Maria Santos",
    "book_title": "Dom Casmurro",
    "requested_at": "2025-11-03T14:20:00Z",
    "status": "pending"
  }
]
```

---

## Notifications Service

Base: `/api/notifications/*`

### GET /api/notifications

Lista todas as notificações do usuário autenticado.

**Autenticação:** Requerida

**Response 200:**
```json
[
  {
    "id": 1,
    "user_id": 2,
    "title": "Empréstimo Aprovado",
    "message": "Seu empréstimo do livro '1984' foi aprovado!",
    "data": {
      "book_id": 1,
      "borrow_id": 10
    },
    "is_read": false,
    "created_at": "2025-11-04T09:15:00Z"
  }
]
```

---

### GET /api/notifications/unread-count

Retorna a contagem de notificações não lidas.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "count": 3
}
```

---

### POST /api/notifications/{id}/read

Marca uma notificação como lida.

**Autenticação:** Requerida

**Path Parameters:**
- `id` (integer) - ID da notificação

**Response 200:**
```json
{
  "success": true,
  "message": "Notificação marcada como lida"
}
```

---

### POST /api/notifications/mark-all-read

Marca todas as notificações do usuário como lidas.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "success": true,
  "message": "Todas as notificações marcadas como lidas"
}
```

---

### DELETE /api/notifications/{id}/delete

Remove uma notificação.

**Autenticação:** Requerida

**Path Parameters:**
- `id` (integer) - ID da notificação

**Response 200:**
```json
{
  "success": true,
  "message": "Notificação deletada"
}
```

---

### POST /api/notifications/create

Cria uma notificação manualmente.

**Autenticação:** Requerida (Admin)

**Request Body:**
```json
{
  "user_id": 5,
  "title": "Aviso importante",
  "message": "A biblioteca estará fechada amanhã",
  "data": {
    "type": "announcement"
  }
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Notificação criada com sucesso"
}
```

---

### POST /api/notifications/event

Processa eventos de outros serviços para criar notificações automaticamente.

**Uso:** Service-to-Service (interno)

**Request Body:**
```json
{
  "event": "book.approved",
  "data": {
    "user_id": 3,
    "book_title": "1984",
    "borrow_id": 10
  }
}
```

**Eventos Suportados:**
- `book.requested` - Quando um usuário solicita empréstimo
- `book.approved` - Quando admin aprova empréstimo
- `book.rejected` - Quando admin rejeita empréstimo
- `book.borrowed` - Quando empréstimo é efetivado
- `book.returned` - Quando livro é devolvido

**Response 200:**
```json
{
  "success": true,
  "message": "Evento processado"
}
```

---

## Dashboard Service

Base: `/api/stats/*`, `/api/overdue/*`

### GET /api/stats/general

Retorna estatísticas gerais da biblioteca.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
{
  "total_books": 150,
  "total_users": 87,
  "active_borrows": 23,
  "overdue_borrows": 5,
  "pending_requests": 7
}
```

---

### GET /api/stats/borrows-by-month

Retorna estatísticas de empréstimos por mês (últimos 12 meses).

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "month": "2025-01",
    "count": 45
  },
  {
    "month": "2025-02",
    "count": 52
  }
]
```

---

### GET /api/stats/top-books

Retorna os livros mais emprestados.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "book_id": 5,
    "title": "Harry Potter e a Pedra Filosofal",
    "author": "J.K. Rowling",
    "borrow_count": 28
  },
  {
    "book_id": 12,
    "title": "1984",
    "author": "George Orwell",
    "borrow_count": 23
  }
]
```

---

### GET /api/stats/books-by-category

Retorna a distribuição de livros por categoria/gênero.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "genre": "Fantasia",
    "count": 35
  },
  {
    "genre": "Ficção Científica",
    "count": 28
  }
]
```

---

### GET /api/stats/recent-activities

Retorna atividades recentes na biblioteca.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "type": "borrow",
    "user_name": "João Silva",
    "book_title": "1984",
    "timestamp": "2025-11-04T10:30:00Z"
  },
  {
    "type": "return",
    "user_name": "Maria Santos",
    "book_title": "Dom Casmurro",
    "timestamp": "2025-11-04T09:15:00Z"
  }
]
```

---

### GET /api/stats/user-profile

Retorna estatísticas do perfil do usuário autenticado.

**Autenticação:** Requerida

**Response 200:**
```json
{
  "total_borrows": 15,
  "active_borrows": 2,
  "books_returned": 13,
  "favorite_genre": "Fantasia"
}
```

---

### GET /api/stats/history

Retorna o histórico de empréstimos do usuário autenticado.

**Autenticação:** Requerida

**Response 200:**
```json
[
  {
    "id": 10,
    "book_id": 1,
    "book_title": "1984",
    "book_author": "George Orwell",
    "requested_at": "2025-10-15T10:00:00Z",
    "approved_at": "2025-10-15T14:30:00Z",
    "due_date": "2025-10-29",
    "returned_at": "2025-10-28T11:20:00Z",
    "status": "returned"
  }
]
```

---

### POST /api/overdue/update

Atualiza o status de empréstimos atrasados (marca como "late" se passou a data de devolução).

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
{
  "success": true,
  "updated_count": 3
}
```

---

### GET /api/overdue/user/{userId}

Retorna empréstimos atrasados de um usuário específico.

**Autenticação:** Requerida

**Path Parameters:**
- `userId` (integer) - ID do usuário

**Response 200:**
```json
[
  {
    "id": 8,
    "book_title": "Dom Casmurro",
    "due_date": "2025-10-20",
    "days_overdue": 15,
    "status": "late"
  }
]
```

---

### GET /api/overdue/all

Retorna todos os empréstimos atrasados do sistema.

**Autenticação:** Requerida (Admin)

**Response 200:**
```json
[
  {
    "id": 8,
    "user_id": 5,
    "user_name": "Carlos Souza",
    "book_id": 3,
    "book_title": "Dom Casmurro",
    "due_date": "2025-10-20",
    "days_overdue": 15,
    "status": "late"
  }
]
```

---

## Códigos de Status

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 400 | Requisição inválida (dados ausentes ou inválidos) |
| 401 | Não autenticado ou sessão expirada |
| 403 | Não autorizado (requer role admin) |
| 404 | Recurso não encontrado |
| 500 | Erro interno do servidor |

---

## Exemplos de Requisição

### Exemplo com cURL - Login e Busca de Livros

```bash
# 1. Login
curl -X POST http://localhost:8080/auth/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"senha123"}' \
  -c cookies.txt

# 2. Buscar livros (usando cookie de sessão)
curl -X GET "http://localhost:8080/api/search?q=harry+potter" \
  -b cookies.txt

# 3. Solicitar empréstimo
curl -X POST http://localhost:8080/api/request/5 \
  -b cookies.txt
```

### Exemplo com JavaScript (Fetch API)

```javascript
// Login
const login = async () => {
  const response = await fetch('http://localhost:8080/auth/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include', // importante para incluir cookies
    body: JSON.stringify({
      email: 'user@example.com',
      password: 'senha123'
    })
  });
  return await response.json();
};

// Buscar livros
const searchBooks = async (query) => {
  const response = await fetch(`http://localhost:8080/api/search?q=${query}`, {
    credentials: 'include'
  });
  return await response.json();
};

// Solicitar empréstimo
const requestBook = async (bookId) => {
  const response = await fetch(`http://localhost:8080/api/request/${bookId}`, {
    method: 'POST',
    credentials: 'include'
  });
  return await response.json();
};
```

### Exemplo com Python (requests)

```python
import requests

# Criar sessão para manter cookies
session = requests.Session()

# Login
login_response = session.post(
    'http://localhost:8080/auth/api/login',
    json={
        'email': 'user@example.com',
        'password': 'senha123'
    }
)

# Buscar livros
search_response = session.get(
    'http://localhost:8080/api/search',
    params={'q': 'harry potter'}
)
books = search_response.json()

# Solicitar empréstimo
request_response = session.post(
    'http://localhost:8080/api/request/5'
)
```

---

## Notas Importantes

1. **CORS**: O API Gateway deve ser configurado para aceitar requisições cross-origin se o frontend estiver em domínio diferente.

2. **Rate Limiting**: Atualmente não implementado, mas recomendado para produção.

3. **Upload de Arquivos**: Os endpoints de criação/atualização de livros aceitam arquivos via `multipart/form-data`. Imagens e PDFs são enviados para Cloudflare R2 storage.

4. **Comunicação Inter-Service**: Serviços se comunicam diretamente usando HTTP. O Books Service envia eventos para o Notifications Service através do endpoint `/api/notifications/event`.

5. **Sessões**: Sessões são armazenadas no Redis (porta 6379) e compartilhadas entre todos os serviços.

6. **Database**: Todos os serviços compartilham o mesmo banco MySQL (porta 3306).

---

## Visualização Swagger

Para visualizar esta API com interface Swagger UI, você pode usar o arquivo `openapi.yaml` na raiz do projeto:

1. Acesse [editor.swagger.io](https://editor.swagger.io/)
2. Importe o arquivo `openapi.yaml`
3. Visualize a documentação interativa

Ou instale localmente:

```bash
npm install -g swagger-ui-express
# Ou use Docker
docker run -p 80:8080 -e SWAGGER_JSON=/foo/openapi.yaml -v /path/to/VirtuaLib:/foo swaggerapi/swagger-ui
```
