# Backend de Notificações — Alterações implementadas

## Resumo das alterações

Foram adicionados e atualizados os seguintes artefatos:

- Novo model:
  - `services/notifications/models/NotificationModel.php` — operações sobre a tabela `Notifications` (get, create, createBulk, count unread, mark as read, mark all read, delete).

- Novo controller:
  - `services/notifications/controllers/NotificationsController.php` — endpoints JSON para listar notificações do usuário, criar, criar em massa, marcar como lida, marcar todas como lidas, contar não-lidas e apagar.

- Nova view partial (opcional no frontend):
  - `services/notifications/views/components/notifications.php` — componente simples para renderizar uma lista de notificações (usado pelo navbar, se integrado).

- Rotas atualizadas:
  - `services/notifications/routes.php` — rotas adicionadas para expor os novos endpoints (lista abaixo).

- DDL (tabela): a tabela `Notifications` está contemplada no DDL projeto (arquivo `ddl.sql` / `docs/ddl.sql`), com colunas compatíveis com o model.

## Tabela do banco (DDL)

A tabela usada pelo model tem a seguinte definição (extraída do DDL do projeto):

```sql
CREATE TABLE IF NOT EXISTS Notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);
```

> Observação: se o seu ambiente ainda não contém esta tabela, execute o arquivo `ddl.sql` ou importe esta instrução no seu banco MySQL/MariaDB.

## Endpoints (rotas) adicionadas

As rotas foram registradas em `services/notifications/routes.php`. A seguir estão os endpoints, método HTTP, rota e responsabilidades:

- GET /api/notifications
  - Lista as notificações do usuário logado (até 100, ordenadas por `created_at` desc).
  - Requer sessão autenticada.
  - Resposta: `{ "notifications": [ ... ] }`.

- GET /api/notifications/unread-count
  - Retorna `{ "unread": N }` com o número de notificações não-lidas do usuário atual.
  - Requer sessão autenticada.

- POST /api/notifications/{id}/read
  - Marca a notificação `id` como lida para o usuário autenticado.
  - Requer sessão autenticada.
  - Resposta: `{ "message": "Marked as read" }`.

- POST /api/notifications/mark-all-read
  - Marca todas as notificações do usuário atual como lidas.
  - Requer sessão autenticada.
  - Resposta: `{ "message": "All marked as read" }`.

- POST /api/notifications/create
  - Cria uma notificação para um usuário específico.
  - Requer `admin` (método protegido com `requireRole('admin')`).
  - Body JSON: `{ "user_id": 2, "title": "Titulo", "message": "Texto", "data": { ... } }`.
  - Resposta (201): `{ "id": 123, "message": "Notification created" }`.

- POST /api/notifications/create-bulk
  - Cria várias notificações (array de objetos) em uma transação.
  - Requer `admin`.
  - Body JSON: `[ { "user_id":1, "title":"A", "message":"B" }, { "user_id":2, "title":"C","message":"D" } ]`.

- DELETE /api/notifications/{id}
  - Deleta a notificação `id` do usuário autenticado.
  - Requer sessão autenticada.

> Observação: as rotas e os nomes dos endpoints seguem o padrão usado no projeto (controllers orientados a JSON). Se você preferir usar verbos HTTP mais "padrão REST" (por exemplo, usar POST somente em `/api/notifications` para criar), posso ajustar as rotas facilmente.

## Formatos de dados

Notificação (exemplo retornado por GET /api/notifications):

```json
{
  "id": 12,
  "user_id": 2,
  "title": "Livro disponível",
  "message": "O livro X foi devolvido e está disponível.",
  "data": "{\"book_id\": 5}",
  "is_read": 0,
  "created_at": "2025-10-04 12:34:56"
}
```

No banco, a coluna `data` armazena JSON (nullable). O model o armazena como JSON-encoded string.

## Autorização

- A leitura/listagem, marcar como lida, marcar todas como lidas e exclusão exigem que o usuário esteja autenticado (internamente usa o trait `AuthGuard` e `$_SESSION['user']`).
- As operações de criação (`create`, `create-bulk`) foram restritas a usuários com `role = 'admin'` conforme lógica atual. Se desejar permitir que outros sistemas (background jobs) criem notificações, podemos expor uma rota protegida por token ou permitir criar via model direto.
---

## Testes e verificação rápida

- Fiz checagens estáticas (sintaxe) nos arquivos alterados; não foram encontradas falhas de parse.
- Recomendo um teste manual rápido:
  1. Verificar que a tabela `Notifications` existe (rodar `ddl.sql` se necessário).
  2. Autenticar via `/api/auth/login` com um usuário admin.
  3. Tentar `POST /api/notifications/create` e `GET /api/notifications` para confirmar fluxo.

## Observações e próximos passos recomendados

- Integrar visualmente o componente de notificações ao `partials/navbar.php` (frontend). Posso fazer isso e adicionar `public/js/notifications.js` para buscar `unread-count` e listar / marcar como lidas interativamente.
- Considerar WebSockets/Server-Sent Events para notificações em tempo real (opcional)
- Adicionar testes automatizados (unitários/integration) para o model e endpoints.
- Revisar permissões: permitir que jobs/serviços criem notificações sem usar credenciais de admin (ex.: rota protegida por token ou serviço interno que chame o model diretamente).

## Mapeamento de requisitos -> status

- Model de notificações: Done (`services/notifications/models/NotificationModel.php`).
- Controller com endpoints básicos: Done (`services/notifications/controllers/NotificationsController.php`).
- Rotas: Done (`services/notifications/routes.php`).
- DDL/table: Present (arquivo `docs/sql/books-tables.sql`).

---

## Alterações recentes — Eventos e disparos (fluxo automático)

Nas últimas mudanças foi adicionada uma infraestrutura básica de eventos para permitir que ações do sistema disparem notificações automaticamente. Isso cobre o caso de uso onde, por exemplo, um usuário empresta ou devolve um livro e deve receber uma notificação.

Arquivos adicionados/alterados relacionados a eventos:

- `services/utils/EventDispatcher.php` — dispatcher simples com API estática:
  - `EventDispatcher::listen(string $event, callable $listener)` — registra listeners.
  - `EventDispatcher::dispatch(string $event, $payload = null)` — dispara o evento para todos os listeners registrados.

- `services/notifications/services/NotificationService.php` — serviço que encapsula `NotificationModel` e registra listeners padrão:
  - Registra listeners para os eventos `book.borrowed` e `book.returned`.
  - Os listeners criam notificações na tabela `Notifications` usando o `NotificationModel`.
  - Métodos públicos úteis: `notify($userId, $title, $message, $data)`, `notifyBorrowed($payload)`, `notifyReturned($payload)`.

- `services/books/controllers/BookController.php` — agora dispara eventos quando operações são bem-sucedidas:
  - Após emprestar um livro: `EventDispatcher::dispatch('book.borrowed', $payload)`
  - Após devolver um livro: `EventDispatcher::dispatch('book.returned', $payload)`
  - `payload` padrão enviado: `['user_id'=>..., 'book_id'=>..., 'book_title'=> ...]`.

Como testar o novo fluxo

1. Certifique-se de que o servidor esteja reiniciado (para recarregar o bootstrap atualizado).
2. Autentique como usuário (para operações de empréstimo/devolução) e opcionalmente como `admin` para criação manual de notificações.
3. Acesse a rota de empréstimo (por exemplo, executar o fluxo que chama `BookController::borrowBook`) e verifique a tabela `Notifications` por uma nova linha correspondente.
4. Verifique também que `GET /api/notifications` lista a notificação e `GET /api/notifications/unread-count` aumenta conforme esperado.

Notas operacionais e recomendações

- O `EventDispatcher` atual é síncrono (listeners são executados no mesmo processo). Se precisar de alto throughput ou de operações demoradas, recomendo enfileirar a criação de notificações (ex.: com RabbitMQ, Redis, ou uma tabela de jobs) e processar assincronamente.
- Os listeners são registrados quando `NotificationService.php` é incluído. Garantir que este arquivo seja incluído no bootstrap (como feito em `index.php`) é essencial para que os dispatches sejam entregues.
- Podemos expandir a lista de eventos (ex.: `user.registered`, `book.available`) e criar listeners para alertar admins, enviar e-mails, etc.
