<?php

require_once __DIR__ . '/../core/EventDispatcher.php';

class BorrowModel extends Database
{
    private const DEFAULT_LOAN_DAYS = 14;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Registra uma nova solicitação de empréstimo para o usuário informado.
     * O livro não é marcado como indisponível até ser aprovado pelo admin.
     */
    public function requestBorrow(int $bookId, int $userId): array
    {
        try {
            $this->pdo->beginTransaction();

            $bookStmt = $this->pdo->prepare(
                'SELECT id, available FROM Books WHERE id = :id FOR UPDATE'
            );
            $bookStmt->execute([':id' => $bookId]);
            $book = $bookStmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Livro não encontrado.'
                ];
            }

            // Verificar se já existe uma solicitação pendente para este livro por este usuário
            $existingRequestStmt = $this->pdo->prepare(
                'SELECT id FROM Borrows 
                 WHERE user_id = :user_id AND book_id = :book_id AND status = :status'
            );
            $existingRequestStmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId,
                ':status' => 'pending'
            ]);

            if ($existingRequestStmt->fetch()) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Você já possui uma solicitação pendente para este livro.'
                ];
            }

            // Verificar se já existe um empréstimo ativo para este livro por este usuário
            $activeBorrowStmt = $this->pdo->prepare(
                'SELECT id FROM Borrows 
                 WHERE user_id = :user_id AND book_id = :book_id AND status IN (:status1, :status2)'
            );
            $activeBorrowStmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId,
                ':status1' => 'approved',
                ':status2' => 'late'
            ]);

            if ($activeBorrowStmt->fetch()) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Você já possui este livro emprestado.'
                ];
            }

            $requestedAt = new DateTimeImmutable('now');

            $insertRequest = $this->pdo->prepare(
                'INSERT INTO Borrows (user_id, book_id, requested_at, status)
                 VALUES (:user_id, :book_id, :requested_at, :status)'
            );

            $insertRequest->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId,
                ':requested_at' => $requestedAt->format('Y-m-d H:i:s'),
                ':status' => 'pending'
            ]);

            $this->pdo->commit();

            // Disparar evento de notificação
            $bookTitle = $this->getBookTitle($bookId);
            EventDispatcher::dispatch('book.requested', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'book_title' => $bookTitle
            ]);

            return [
                'success' => true,
                'message' => 'Solicitação de empréstimo realizada com sucesso. Aguarde a aprovação.',
                'request' => [
                    'id' => (int)$this->pdo->lastInsertId(),
                    'requested_at' => $requestedAt->format(DATE_ATOM),
                    'status' => 'pending'
                ]
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Database error in BorrowModel::requestBorrow: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Não foi possível registrar a solicitação no momento.'
            ];
        }
    }

    /**
     * Aprova uma solicitação de empréstimo pendente (para uso por administradores).
     * Marca o livro como indisponível e define a data de vencimento.
     */
    public function approveBorrow(int $requestId, int $adminUserId): array
    {
        try {
            $this->pdo->beginTransaction();

            $requestStmt = $this->pdo->prepare(
                'SELECT id, user_id, book_id FROM Borrows 
                 WHERE id = :id AND status = :status FOR UPDATE'
            );
            $requestStmt->execute([
                ':id' => $requestId,
                ':status' => 'pending'
            ]);
            $request = $requestStmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Solicitação não encontrada ou já processada.'
                ];
            }

            // Verificar se o livro ainda está disponível
            $bookStmt = $this->pdo->prepare(
                'SELECT available FROM Books WHERE id = :id FOR UPDATE'
            );
            $bookStmt->execute([':id' => $request['book_id']]);
            $book = $bookStmt->fetch(PDO::FETCH_ASSOC);

            if (!$book || (int)$book['available'] === 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Livro não está mais disponível.'
                ];
            }

            $approvedAt = new DateTimeImmutable('now');
            $dueDate = $approvedAt->add(new DateInterval('P' . self::DEFAULT_LOAN_DAYS . 'D'));

            $updateRequest = $this->pdo->prepare(
                'UPDATE Borrows 
                 SET approved_at = :approved_at, due_date = :due_date, status = :status 
                 WHERE id = :id'
            );
            $updateRequest->execute([
                ':approved_at' => $approvedAt->format('Y-m-d H:i:s'),
                ':due_date' => $dueDate->format('Y-m-d'),
                ':status' => 'approved',
                ':id' => $requestId
            ]);

            $updateBook = $this->pdo->prepare(
                'UPDATE Books SET available = 0 WHERE id = :id'
            );
            $updateBook->execute([':id' => $request['book_id']]);

            $this->pdo->commit();

            // Disparar evento de notificação
            $bookTitle = $this->getBookTitle($request['book_id']);
            EventDispatcher::dispatch('book.approved', [
                'user_id' => $request['user_id'],
                'book_id' => $request['book_id'],
                'book_title' => $bookTitle,
                'approved_by' => $adminUserId
            ]);

            return [
                'success' => true,
                'message' => 'Solicitação aprovada com sucesso.',
                'approval' => [
                    'approved_at' => $approvedAt->format(DATE_ATOM),
                    'due_date' => $dueDate->format('Y-m-d')
                ]
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Database error in BorrowModel::approveBorrow: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Não foi possível aprovar a solicitação no momento.'
            ];
        }
    }

    /**
     * Finaliza o empréstimo aprovado para o par usuário/livro.
     */
    public function returnBook(int $bookId, int $userId): array
    {
        try {
            $this->pdo->beginTransaction();

            $borrowStmt = $this->pdo->prepare(
                "SELECT id, status FROM Borrows
                                 WHERE book_id = :book_id
                                     AND user_id = :user_id
                                     AND status IN ('approved', 'late')
                                 ORDER BY approved_at DESC
                                 LIMIT 1
                                 FOR UPDATE"
            );
            $borrowStmt->execute([
                ':book_id' => $bookId,
                ':user_id' => $userId,
            ]);
            $borrow = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrow) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Não há empréstimo ativo para este livro.'
                ];
            }

            $returnedAt = new DateTimeImmutable('now');

            $updateBorrow = $this->pdo->prepare(
                'UPDATE Borrows SET returned_at = :returned_at, status = :status WHERE id = :id'
            );
            $updateBorrow->execute([
                ':returned_at' => $returnedAt->format('Y-m-d H:i:s'),
                ':status' => 'returned',
                ':id' => $borrow['id'],
            ]);

            $updateBook = $this->pdo->prepare(
                'UPDATE Books SET available = 1 WHERE id = :id'
            );
            $updateBook->execute([':id' => $bookId]);

            $this->pdo->commit();

            // Disparar evento de notificação
            $bookTitle = $this->getBookTitle($bookId);
            EventDispatcher::dispatch('book.returned', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'book_title' => $bookTitle
            ]);

            return [
                'success' => true,
                'message' => 'Livro devolvido com sucesso.',
                'return' => [
                    'returned_at' => $returnedAt->format(DATE_ATOM)
                ]
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Database error in BorrowModel::returnBook: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Não foi possível registrar a devolução no momento.'
            ];
        }
    }

    /**
     * Retorna o histórico completo de empréstimos.
     */
    public function getHistory(): array
    {
        try {
            $query = $this->pdo->query(
                'SELECT
                    Borrows.id,
                    Users.name AS user_name,
                    Books.title AS book_title,
                    Borrows.requested_at,
                    Borrows.approved_at,
                    Borrows.due_date,
                    Borrows.returned_at,
                    Borrows.status
                 FROM Borrows
                 INNER JOIN Users ON Users.id = Borrows.user_id
                 INNER JOIN Books ON Books.id = Borrows.book_id
                 ORDER BY Borrows.requested_at DESC'
            );

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getHistory: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retorna os IDs dos livros atualmente emprestados pelo usuário (aprovados).
     */
    public function getActiveBorrowedBookIdsByUser(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT book_id FROM Borrows
                                 WHERE user_id = :user_id
                                     AND status IN ('approved', 'late')"
            );
            $stmt->execute([':user_id' => $userId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map('intval', $ids ?: []);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getActiveBorrowedBookIdsByUser: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retorna os IDs dos livros com solicitações pendentes pelo usuário.
     */
    public function getPendingRequestBookIdsByUser(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT book_id FROM Borrows
                 WHERE user_id = :user_id AND status = 'pending'"
            );
            $stmt->execute([':user_id' => $userId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map('intval', $ids ?: []);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getPendingRequestBookIdsByUser: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retorna todas as solicitações pendentes com informações do usuário e livro.
     */
    public function getPendingRequests(): array
    {
        try {
            $query = $this->pdo->query(
                'SELECT
                    Borrows.id,
                    Borrows.user_id,
                    Borrows.book_id,
                    Borrows.requested_at,
                    Users.name AS user_name,
                    Users.email AS user_email,
                    Books.title AS book_title,
                    Books.author AS book_author,
                    Books.available
                 FROM Borrows
                 INNER JOIN Users ON Users.id = Borrows.user_id
                 INNER JOIN Books ON Books.id = Borrows.book_id
                 WHERE Borrows.status = "pending"
                 ORDER BY Borrows.requested_at ASC'
            );

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getPendingRequests: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Rejeita uma solicitação de empréstimo (remove do sistema).
     */
    public function rejectRequest(int $requestId, int $adminUserId): array
    {
        try {
            $this->pdo->beginTransaction();

            $requestStmt = $this->pdo->prepare(
                'SELECT id, user_id, book_id FROM Borrows 
                 WHERE id = :id AND status = :status FOR UPDATE'
            );
            $requestStmt->execute([
                ':id' => $requestId,
                ':status' => 'pending'
            ]);
            $request = $requestStmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Solicitação não encontrada ou já processada.'
                ];
            }

            $deleteStmt = $this->pdo->prepare(
                'DELETE FROM Borrows WHERE id = :id'
            );
            $deleteStmt->execute([':id' => $requestId]);

            $this->pdo->commit();

            // Disparar evento de notificação
            $bookTitle = $this->getBookTitle($request['book_id']);
            EventDispatcher::dispatch('book.rejected', [
                'user_id' => $request['user_id'],
                'book_id' => $request['book_id'],
                'book_title' => $bookTitle,
                'rejected_by' => $adminUserId
            ]);

            return [
                'success' => true,
                'message' => 'Solicitação rejeitada com sucesso.'
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Database error in BorrowModel::rejectRequest: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Não foi possível rejeitar a solicitação no momento.'
            ];
        }
    }

    /**
     * Obtém o título do livro pelo ID.
     */
    private function getBookTitle(int $bookId): string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT title FROM Books WHERE id = :id');
            $stmt->execute([':id' => $bookId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['title'] : 'Livro desconhecido';
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getBookTitle: ' . $exception->getMessage());
            return 'Livro desconhecido';
        }
    }

    public function getBorrowsByMonth(): array
    {
        try {
            $query = $this->pdo->query(
                "SELECT 
                    DATE_FORMAT(requested_at, '%Y-%m') AS month, 
                    COUNT(*) AS total_borrows 
                 FROM Borrows 
                 GROUP BY month 
                 ORDER BY month ASC"
            );

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getBorrowsByMonth: ' . $exception->getMessage());
            return [];
        }
    }

    public function getTopBorrowedBooks(int $limit = 5): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    Books.id,
                    Books.title,
                    Books.author,
                    COUNT(Borrows.id) AS borrow_count
                 FROM Borrows
                 INNER JOIN Books ON Books.id = Borrows.book_id
                 GROUP BY Books.id, Books.title, Books.author
                 ORDER BY borrow_count DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getTopBorrowedBooks: ' . $exception->getMessage());
            return [];
        }
    }

    public function getBooksByCategory(): array
    {
        try {
            $query = $this->pdo->query(
                "SELECT 
                    category, 
                    COUNT(*) AS book_count 
                 FROM Books 
                 GROUP BY category"
            );

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getBooksByCategory: ' . $exception->getMessage());
            return [];
        }
    }

    public function getRecentActivities(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT
                    Borrows.id,
                    Users.name AS user_name,
                    Books.title AS book_title,
                    Borrows.requested_at,
                    Borrows.approved_at,
                    Borrows.due_date,
                    Borrows.returned_at,
                    Borrows.status
                 FROM Borrows
                 INNER JOIN Users ON Users.id = Borrows.user_id
                 INNER JOIN Books ON Books.id = Borrows.book_id
                 ORDER BY 
                    CASE 
                        WHEN Borrows.status = 'returned' THEN Borrows.returned_at
                        WHEN Borrows.status = 'approved' THEN Borrows.approved_at
                        WHEN Borrows.status = 'pending' THEN Borrows.requested_at
                        ELSE Borrows.requested_at
                    END DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getRecentActivities: ' . $exception->getMessage());
            return [];
        }
    }

    public function getActiveBorrowsCount(): int
    {
        try {
            $query = $this->pdo->query(
                "SELECT COUNT(*) AS active_count 
                 FROM Borrows 
                 WHERE status = 'borrowed'"
            );

            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['active_count'] : 0;
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getActiveBorrowsCount: ' . $exception->getMessage());
            return 0;
        }
    }

    public function getTodayBorrowsCount(): int
    {
        try {
            $today = (new DateTimeImmutable('now'))->format('Y-m-d');
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) AS today_count 
                 FROM Borrows 
                 WHERE DATE(approved_at) = :today AND status = 'borrowed'"
            );
            $stmt->execute([':today' => $today]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['today_count'] : 0;
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getTodayBorrowsCount: ' . $exception->getMessage());
            return 0;
        }
    }

    public function getActiveBorrowsCountByUser(int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) AS active_count 
                 FROM Borrows 
                 WHERE user_id = :user_id AND status = 'borrowed'"
            );
            $stmt->execute([':user_id' => $userId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['active_count'] : 0;
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getActiveBorrowsCountByUser: ' . $exception->getMessage());
            return 0;
        }
    }

    public function getTotalBorrowsCountByUser(int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) AS total_count 
                 FROM Borrows 
                 WHERE user_id = :user_id AND status IN ('borrowed', 'returned')"
            );
            $stmt->execute([':user_id' => $userId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['total_count'] : 0;
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getTotalBorrowsCountByUser: ' . $exception->getMessage());
            return 0;
        }
    }
}
