# Arquitetura do Sistema - VirtuaLib

## Índice

- [Visão Geral](#visão-geral)
- [Padrão Arquitetural](#padrão-arquitetural)
- [Diagrama de Arquitetura](#diagrama-de-arquitetura)
- [Serviços](#serviços)
- [Fluxo de Dados](#fluxo-de-dados)
- [Banco de Dados](#banco-de-dados)
- [Infraestrutura](#infraestrutura)
- [Segurança](#segurança)
- [Escalabilidade](#escalabilidade)

---

## Visão Geral

VirtuaLib é um sistema de biblioteca digital construído com **arquitetura de microsserviços** (Service-Oriented Architecture - SOA). O sistema é composto por 4 serviços independentes que se comunicam através de um API Gateway centralizado.

### Tecnologias Principais

- **Linguagem:** PHP 7.4+
- **Banco de Dados:** MySQL 8.0
- **Cache/Sessões:** Redis 7.0
- **Storage:** Cloudflare R2 (compatível com S3)
- **Containerização:** Docker & Docker Compose
- **Load Balancer:** Nginx
- **Padrão:** MVC (Model-View-Controller) em cada serviço

---

## Padrão Arquitetural

### Service-Oriented Architecture (SOA)

Cada serviço é:
- **Independente**: Pode ser desenvolvido, testado e implantado separadamente
- **Responsável**: Possui uma responsabilidade única e bem definida
- **Comunicativo**: Interage com outros serviços via HTTP/REST
- **Stateless**: A sessão é gerenciada externamente (Redis)

### Vantagens da Arquitetura Escolhida

1. **Separação de Responsabilidades**: Cada serviço tem um domínio específico
2. **Escalabilidade Independente**: Serviços podem escalar separadamente
3. **Manutenibilidade**: Mudanças em um serviço não afetam diretamente outros
4. **Desenvolvimento Paralelo**: Times podem trabalhar em serviços diferentes
5. **Resiliência**: Falha em um serviço não derruba todo o sistema

---

## Diagrama de Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENTE                             │
│                   (Browser / Mobile App)                    │
└────────────────────────────┬────────────────────────────────┘
                             │
                             │ HTTP/HTTPS
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                    Nginx Load Balancer                      │
│                        (Port 80)                            │
│  - Balanceamento de carga                                   │
│  - SSL Termination (produção)                               │
│  - Compressão gzip                                          │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                      API Gateway                            │
│                      (Port 8080)                            │
│                                                             │
│  Responsabilidades:                                         │
│  ✓ Roteamento de requisições para serviços                 │
│  ✓ Pattern matching baseado em URI                         │
│  ✓ Gerenciamento de sessão (Redis-backed)                  │
│  ✓ CORS handling                                           │
│  ✓ Request/Response logging                                │
│                                                             │
│  Arquivo: services/api-gateway.php                         │
└─────┬───────────┬───────────┬────────────┬──────────────────┘
      │           │           │            │
      ▼           ▼           ▼            ▼
┌──────────┐┌──────────┐┌─────────────┐┌──────────────┐
│  Auth    ││  Books   ││Notifications││  Dashboard   │
│ Service  ││ Service  ││  Service    ││   Service    │
│ (8081)   ││ (8082)   ││  (8083)     ││   (8084)     │
│          ││          ││             ││              │
│ • Login  ││ • CRUD   ││ • Criar     ││ • Stats      │
│ • Regist ││ • Search ││ • Listar    ││ • Dashboard  │
│ • Profile││ • Borrow ││ • Marcar    ││ • History    │
│ • Logout ││ • Return ││   lida      ││ • Overdue    │
└────┬─────┘└────┬─────┘└──────┬──────┘└──────┬───────┘
     │           │              │               │
     │           └──────────────┼───────────────┘
     │                          │
     │           ┌──────────────┘
     │           │ HTTP Service-to-Service
     │           │ (Notificações de eventos)
     │           │
     └───────────┴──────────────┬────────────────────────
                                │
                   ┌────────────┴────────────┐
                   │                         │
                   ▼                         ▼
          ┌─────────────────┐      ┌─────────────────┐
          │  MySQL Database │      │  Redis Session  │
          │   (Port 3306)   │      │   Store (6379)  │
          │                 │      │                 │
          │  • users        │      │ • Sessões PHP   │
          │  • books        │      │ • Cache         │
          │  • borrows      │      │                 │
          │  • notifications│      │                 │
          └─────────────────┘      └─────────────────┘
                   │
                   │
          ┌────────▼────────────────────────┐
          │    Cloudflare R2 Storage        │
          │    (cdn.nutria.digital)         │
          │                                 │
          │  • Imagens de capas de livros   │
          │  • Arquivos PDF dos livros      │
          └─────────────────────────────────┘
```

---

## Serviços

### 1. Auth Service (Port 8081)

**Responsabilidade:** Gerenciamento de autenticação e perfis de usuário

**Rotas principais:**
- `POST /auth/api/register` - Registro
- `POST /auth/api/login` - Login
- `POST /auth/api/logout` - Logout
- `GET /auth/api/me` - Perfil atual
- `POST /auth/api/update-profile` - Atualizar perfil
- `GET /auth/api/user-stats` - Estatísticas do usuário

**Arquivos principais:**
```
services/auth/
├── routes.php                  # Definição de rotas
├── controllers/
│   └── AuthController.php      # Lógica de autenticação
├── models/
│   └── UserModel.php          # Modelo de dados de usuário
└── views/                     # Views de login/registro
```

**Dependências:**
- MySQL (tabela `users`)
- Redis (sessões)

---

### 2. Books Service (Port 8082)

**Responsabilidade:** Gerenciamento de livros e empréstimos

**Rotas principais:**
- `GET /api/books` - Listar livros
- `GET /api/search?q=` - Buscar livros
- `POST /api/books/create` - Criar livro (Admin)
- `PUT /api/books/{id}/update` - Atualizar livro (Admin)
- `DELETE /api/books/{id}/delete` - Deletar livro (Admin)
- `POST /api/request/{id}` - Solicitar empréstimo
- `POST /api/return/{id}` - Devolver livro
- `POST /api/approve/{requestId}` - Aprovar empréstimo (Admin)
- `POST /api/reject/{requestId}` - Rejeitar empréstimo (Admin)
- `GET /api/pending-requests` - Listar pendentes (Admin)

**Arquivos principais:**
```
services/books/
├── routes.php
├── controllers/
│   └── BookController.php      # ~50KB - lógica complexa
├── models/
│   ├── BookModel.php          # CRUD de livros
│   └── BorrowModel.php        # ~30KB - gestão de empréstimos
└── utils/
    ├── ImageUploader.php      # Upload para R2
    └── PdfUploader.php        # Upload de PDFs
```

**Dependências:**
- MySQL (tabelas `books`, `borrows`)
- Redis (sessões)
- Cloudflare R2 (storage de arquivos)
- Notifications Service (para enviar notificações)

**Comunicação Inter-Service:**
- Envia eventos HTTP para Notifications Service quando:
  - Empréstimo é aprovado
  - Empréstimo é rejeitado
  - Livro é devolvido

---

### 3. Notifications Service (Port 8083)

**Responsabilidade:** Sistema de notificações em tempo real

**Rotas principais:**
- `GET /api/notifications` - Listar notificações
- `GET /api/notifications/unread-count` - Contar não lidas
- `POST /api/notifications/{id}/read` - Marcar como lida
- `POST /api/notifications/mark-all-read` - Marcar todas
- `DELETE /api/notifications/{id}/delete` - Deletar
- `POST /api/notifications/create` - Criar (Admin)
- `POST /api/notifications/event` - Processar evento (interno)

**Arquivos principais:**
```
services/notifications/
├── routes.php
├── controllers/
│   └── NotificationsController.php
├── models/
│   └── NotificationModel.php
└── services/
    └── NotificationService.php    # Lógica de eventos
```

**Eventos Suportados:**
- `book.requested` - Nova solicitação de empréstimo
- `book.approved` - Empréstimo aprovado
- `book.rejected` - Empréstimo rejeitado
- `book.borrowed` - Livro emprestado (após aprovação)
- `book.returned` - Livro devolvido

**Dependências:**
- MySQL (tabela `notifications`)
- Redis (sessões)

---

### 4. Dashboard Service (Port 8084)

**Responsabilidade:** Estatísticas, relatórios e dashboard administrativo

**Rotas principais:**
- `GET /api/stats/general` - Estatísticas gerais
- `GET /api/stats/borrows-by-month` - Empréstimos por mês
- `GET /api/stats/top-books` - Livros mais emprestados
- `GET /api/stats/books-by-category` - Distribuição por categoria
- `GET /api/stats/recent-activities` - Atividades recentes
- `GET /api/stats/user-profile` - Stats do usuário
- `GET /api/stats/history` - Histórico de empréstimos
- `GET /api/overdue/all` - Empréstimos atrasados (Admin)
- `POST /api/overdue/update` - Atualizar status (Admin)

**Arquivos principais:**
```
services/dashboard/
├── routes.php
├── controllers/
│   ├── DashboardController.php
│   ├── HistoryController.php
│   └── OverdueController.php
├── models/
│   ├── StatsModel.php         # Queries de estatísticas
│   └── BorrowModel.php        # Modelo compartilhado
└── views/                     # Views do dashboard
```

**Dependências:**
- MySQL (todas as tabelas - leituras)
- Redis (sessões)

---

## Fluxo de Dados

### Fluxo de Autenticação

```
1. Cliente → Nginx → API Gateway
2. API Gateway → Auth Service (8081)
3. Auth Service valida credenciais no MySQL
4. Auth Service cria sessão no Redis
5. Redis ← Auth Service (armazena dados da sessão)
6. Auth Service → API Gateway (retorna cookie de sessão)
7. API Gateway → Cliente (cookie PHPSESSID)
```

### Fluxo de Solicitação de Empréstimo

```
1. Cliente envia POST /api/request/5
2. Nginx → API Gateway → Books Service
3. Books Service:
   a. Valida sessão (Redis)
   b. Verifica disponibilidade do livro (MySQL)
   c. Cria registro de empréstimo com status "pending" (MySQL)
   d. Envia evento para Notifications Service via HTTP
      POST http://notifications:8083/api/notifications/event
      {
        "event": "book.requested",
        "data": {
          "user_id": 3,
          "book_title": "1984"
        }
      }
4. Notifications Service:
   a. Recebe evento
   b. Cria notificação para admins (MySQL)
   c. Retorna sucesso
5. Books Service → Cliente (sucesso)
```

### Fluxo de Aprovação de Empréstimo (Admin)

```
1. Admin envia POST /api/approve/10
2. Nginx → API Gateway → Books Service
3. Books Service:
   a. Valida que usuário é admin (Redis session)
   b. Atualiza empréstimo (status → "approved", due_date) (MySQL)
   c. Decrementa available do livro (MySQL)
   d. Envia evento "book.approved" → Notifications Service
4. Notifications Service cria notificação para o usuário
5. Cliente recebe confirmação
```

### Fluxo de Visualização de Dashboard

```
1. Admin acessa /dashboard
2. Nginx → API Gateway → Dashboard Service
3. Dashboard Service:
   a. Valida sessão e role admin (Redis)
   b. Executa múltiplas queries de estatísticas (MySQL):
      - Total de livros
      - Total de usuários
      - Empréstimos ativos
      - Empréstimos atrasados
      - Livros mais emprestados (últimos 30 dias)
      - Empréstimos por mês (últimos 12 meses)
   c. Renderiza view com dados
4. HTML renderizado → Cliente
```

---

## Banco de Dados

### Esquema MySQL

**Database:** `virtualib`

#### Tabela: `users`

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,  -- Hashed com password_hash()
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
);
```

#### Tabela: `books`

```sql
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  genre VARCHAR(100) NOT NULL,
  year INT NOT NULL,
  description TEXT,
  available INT NOT NULL DEFAULT 0,
  cover_image VARCHAR(500),        -- URL do Cloudflare R2
  pdf_src VARCHAR(500),            -- URL do Cloudflare R2
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_title (title),
  INDEX idx_author (author),
  INDEX idx_genre (genre),
  INDEX idx_available (available)
);
```

#### Tabela: `borrows`

```sql
CREATE TABLE borrows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL,
  due_date DATE NULL,              -- Data limite (14 dias após aprovação)
  returned_at TIMESTAMP NULL,
  status ENUM('pending', 'approved', 'returned', 'late') DEFAULT 'pending',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_book_id (book_id),
  INDEX idx_status (status),
  INDEX idx_due_date (due_date)
);
```

#### Tabela: `notifications`

```sql
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  data JSON,                       -- Dados adicionais (book_id, etc)
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_is_read (is_read),
  INDEX idx_created_at (created_at)
);
```

### Redis

**Propósito:** Armazenamento de sessões PHP

**Estrutura de chaves:**
```
PHPREDIS_SESSION:<session_id> → Serialized session data
```

**Configuração:**
- `session.save_handler = redis`
- `session.save_path = "tcp://redis:6379"`
- TTL: 30 minutos (default)

---

## Infraestrutura

### Docker Compose

O sistema utiliza Docker Compose para orquestração de containers:

```yaml
services:
  nginx:           # Load balancer (porta 80)
  api-gateway:     # API Gateway (porta 8080)
  auth:            # Auth Service (porta 8081)
  books:           # Books Service (porta 8082)
  notifications:   # Notifications Service (porta 8083)
  dashboard:       # Dashboard Service (porta 8084)
  mysql:           # Banco de dados (porta 3306)
  redis:           # Cache/Sessões (porta 6379)
```

**Arquivo:** `docker-compose.yml`

### Networking

Todos os containers estão na mesma rede Docker:
- Nome da rede: `virtualib_network`
- Driver: `bridge`
- Comunicação interna por nome de serviço (DNS interno do Docker)

**Exemplo:**
```php
// Books Service pode chamar Notifications Service assim:
$url = "http://notifications:8083/api/notifications/event";
```

### Volumes

- **MySQL Data:** `./mysql-data:/var/lib/mysql` (persistência de dados)
- **Code:** Cada serviço tem seu código montado como volume

---

## Segurança

### Autenticação

- **Método:** Session-based com Redis
- **Password Hashing:** `password_hash()` com bcrypt
- **Session Timeout:** 30 minutos de inatividade

### Autorização

**Trait:** `AuthGuard` (compartilhado entre serviços)

```php
trait AuthGuard {
    protected function requireAuth() {
        // Valida se usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }
    }

    protected function requireRole($role) {
        // Valida role específica (ex: 'admin')
        $this->requireAuth();
        if ($_SESSION['role'] !== $role) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado']);
            exit;
        }
    }
}
```

### CORS

Configurado no API Gateway para permitir requisições cross-origin:

```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
```

### SQL Injection Prevention

Uso de **Prepared Statements** em todas as queries:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### File Upload Security

- Validação de tipo MIME
- Validação de extensão de arquivo
- Upload para storage externo (R2) ao invés de filesystem local
- Nomes de arquivo sanitizados

---

## Escalabilidade

### Escalabilidade Horizontal

Cada serviço pode ser escalado independentemente:

```yaml
# docker-compose.yml
services:
  books:
    image: virtualib/books-service
    deploy:
      replicas: 3  # 3 instâncias do Books Service
```

### Load Balancing

Nginx distribui carga entre múltiplas instâncias:

```nginx
upstream books_backend {
    server books-1:8082;
    server books-2:8082;
    server books-3:8082;
}
```

### Caching

**Oportunidades de cache (não implementadas ainda):**
1. Cache de listagem de livros (Redis)
2. Cache de estatísticas do dashboard (5 minutos)
3. CDN para assets estáticos

### Otimizações de Database

**Índices existentes:**
- `users.email` (login)
- `books.title, author, genre, available` (buscas)
- `borrows.user_id, book_id, status, due_date` (queries comuns)
- `notifications.user_id, is_read` (queries de notificações)

**Otimizações futuras:**
- Read replicas do MySQL
- Query caching
- Connection pooling

---

## Monitoramento e Observabilidade

### Logs

**Localização atual:**
- `docker logs <container_name>`

**Recomendações para produção:**
- Implementar logging estruturado (JSON)
- Centralizar logs (ELK Stack, Loki, CloudWatch)
- Adicionar correlation IDs para rastreamento entre serviços

### Métricas (Futuro)

- Taxa de requisições por segundo
- Tempo de resposta de cada serviço
- Taxa de erro
- Uso de CPU/memória por container
- Tamanho da fila de empréstimos pendentes

### Health Checks

**Recomendado implementar:**
```php
// GET /health em cada serviço
{
  "status": "healthy",
  "database": "connected",
  "redis": "connected",
  "uptime": 12345
}
```

---

## Decisões Arquiteturais

### Por que Microsserviços?

1. **Escalabilidade**: Books Service (mais usado) pode escalar independentemente
2. **Manutenibilidade**: Mudanças em notificações não afetam books
3. **Deployment**: Podemos fazer deploy de um serviço sem afetar outros
4. **Aprendizado**: Experiência prática com SOA/microsserviços

### Por que não Monolito?

Apesar do projeto ser pequeno, a escolha por microsserviços foi didática e permite:
- Simular arquitetura real de sistemas maiores
- Praticar comunicação entre serviços
- Aprender sobre desafios de sistemas distribuídos

### Trade-offs

**Vantagens:**
- ✅ Separação de responsabilidades
- ✅ Escalabilidade independente
- ✅ Resiliência (falha isolada)

**Desvantagens:**
- ❌ Complexidade operacional maior
- ❌ Latência de rede entre serviços
- ❌ Debugging mais difícil (distributed tracing necessário)
- ❌ Transações distribuídas (eventual consistency)

---

## Próximos Passos

### Melhorias Recomendadas

1. **API Gateway melhorado**
   - Rate limiting
   - Request/response caching
   - Circuit breaker pattern

2. **Message Queue**
   - Usar RabbitMQ ou Kafka para comunicação assíncrona
   - Substituir HTTP síncrono entre Books → Notifications

3. **Service Discovery**
   - Consul ou Eureka para registro dinâmico de serviços

4. **Monitoring**
   - Prometheus + Grafana para métricas
   - Jaeger para distributed tracing

5. **CI/CD**
   - Pipeline automatizado (GitHub Actions, GitLab CI)
   - Testes automatizados (PHPUnit)
   - Deploy automatizado

6. **Documentação Automática**
   - Swagger UI integrado ao API Gateway
   - Geração automática de client SDKs

---

## Referências

- [Microservices Pattern](https://microservices.io/)
- [12 Factor App](https://12factor.net/)
- [REST API Best Practices](https://restfulapi.net/)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
