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

    public function getActiveBorrowsCount(): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Borrows WHERE status IN ('Emprestado', 'Atrasado')");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getActiveBorrowsCount: ' . $exception->getMessage());
            return 0;
        }
    }

    public function getTodayBorrowsCount(): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Borrows WHERE DATE(borrowed_at) = CURDATE()");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getTodayBorrowsCount: ' . $exception->getMessage());
            return 0;
        }
    }

    public function getBorrowsByMonth(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(borrowed_at, '%b') as mes,
                    COUNT(*) as total
                FROM Borrows 
                WHERE borrowed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(borrowed_at, '%Y-%m')
                ORDER BY borrowed_at ASC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $mesesPt = [
                'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr',
                'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
            ];

            $data = [];
            foreach ($results as $result) {
                $mesPt = $mesesPt[$result['mes']] ?? $result['mes'];
                $data[$mesPt] = (int)$result['total'];
            }

            return $data;
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getBorrowsByMonth: ' . $exception->getMessage());
            return [];
        }
    }

    public function getTopBorrowedBooks(int $limit = 5): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.title as titulo,
                    b.author as autor,
                    COUNT(bor.id) as emprestimos
                FROM Borrows bor
                INNER JOIN Books b ON bor.book_id = b.id
                GROUP BY b.id, b.title, b.author
                ORDER BY emprestimos DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getTopBorrowedBooks: ' . $exception->getMessage());
            return [];
        }
    }

    public function getRecentActivities(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    'emprestimo' as tipo,
                    CONCAT('Livro emprestado: ', b.title) as texto,
                    CONCAT(b.author, ' - há ', TIMESTAMPDIFF(MINUTE, bor.borrowed_at, NOW()), ' min') as detalhe,
                    '#3b82f6' as color
                FROM Borrows bor
                INNER JOIN Books b ON bor.book_id = b.id
                INNER JOIN Users u ON bor.user_id = u.id
                WHERE bor.borrowed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                
                UNION ALL
                
                SELECT 
                    'devolucao' as tipo,
                    CONCAT('Livro devolvido: ', b.title) as texto,
                    CONCAT(b.author, ' - há ', TIMESTAMPDIFF(MINUTE, bor.returned_at, NOW()), ' min') as detalhe,
                    '#f59e0b' as color
                FROM Borrows bor
                INNER JOIN Books b ON bor.book_id = b.id
                INNER JOIN Users u ON bor.user_id = u.id
                WHERE bor.returned_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                
                ORDER BY 
                    CASE 
                        WHEN tipo = 'emprestimo' THEN borrowed_at
                        WHEN tipo = 'devolucao' THEN returned_at
                    END DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            error_log('Database error in BorrowModel::getRecentActivities: ' . $exception->getMessage());
            return [];
        }
    }
}
