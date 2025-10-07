<?php

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
     * Registra um novo empréstimo para o usuário informado.
     */
    public function createBorrow(int $bookId, int $userId): array
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

            if ((int)$book['available'] === 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Livro já está emprestado.'
                ];
            }

            $borrowedAt = new DateTimeImmutable('now');
            $dueDate = $borrowedAt->add(new DateInterval('P' . self::DEFAULT_LOAN_DAYS . 'D'));

            $insertBorrow = $this->pdo->prepare(
                'INSERT INTO Borrows (user_id, book_id, borrowed_at, due_date, status)
                 VALUES (:user_id, :book_id, :borrowed_at, :due_date, :status)'
            );

            $insertBorrow->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId,
                ':borrowed_at' => $borrowedAt->format('Y-m-d H:i:s'),
                ':due_date' => $dueDate->format('Y-m-d'),
                ':status' => 'borrowed'
            ]);

            $updateBook = $this->pdo->prepare(
                'UPDATE Books SET available = 0 WHERE id = :id'
            );
            $updateBook->execute([':id' => $bookId]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Livro emprestado com sucesso.',
                'borrow' => [
                    'id' => (int)$this->pdo->lastInsertId(),
                    'borrowed_at' => $borrowedAt->format(DATE_ATOM),
                    'due_date' => $dueDate->format('Y-m-d')
                ]
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Database error in BorrowModel::createBorrow: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Não foi possível registrar o empréstimo no momento.'
            ];
        }
    }

    /**
     * Finaliza o empréstimo aberto para o par usuário/livro.
     */
    public function closeBorrow(int $bookId, int $userId): array
    {
        try {
            $this->pdo->beginTransaction();

            $borrowStmt = $this->pdo->prepare(
                                "SELECT id, status FROM Borrows
                                 WHERE book_id = :book_id
                                     AND user_id = :user_id
                                     AND status IN ('borrowed', 'late')
                                 ORDER BY borrowed_at DESC
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
            error_log('Database error in BorrowModel::closeBorrow: ' . $exception->getMessage());

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
                    Borrows.borrowed_at,
                    Borrows.due_date,
                    Borrows.returned_at,
                    Borrows.status
                 FROM Borrows
                 INNER JOIN Users ON Users.id = Borrows.user_id
                 INNER JOIN Books ON Books.id = Borrows.book_id
                 ORDER BY Borrows.borrowed_at DESC'
            );

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getHistory: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retorna os IDs dos livros atualmente emprestados pelo usuário.
     */
    public function getActiveBorrowedBookIdsByUser(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                                "SELECT book_id FROM Borrows
                                 WHERE user_id = :user_id
                                     AND status IN ('borrowed', 'late')"
            );
            $stmt->execute([':user_id' => $userId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map('intval', $ids ?: []);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getActiveBorrowedBookIdsByUser: ' . $exception->getMessage());
            return [];
        }
    }
}
