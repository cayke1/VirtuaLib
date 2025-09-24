<?php

class BookModel extends Database
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
    }

    public function getBooks()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Books");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBooks: " . $e->getMessage());
            return [];
        }
    }

    public function search(string $query)
    {
        $sql = "SELECT id, title, author, genre, year, description 
                FROM Books 
                WHERE title LIKE :query 
                   OR author LIKE :query 
                   OR genre LIKE :query 
                   OR description LIKE :query
                ORDER BY title ASC
                LIMIT 10";

        try {
            $stmt = $this->pdo->prepare($sql);
            $searchTerm = '%' . $query . '%';
            $stmt->execute([":query" => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in search: " . $e->getMessage());
            return [];
        }
    }
    public function getBookById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Books WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBookById: " . $e->getMessage());
            return null;
        }
    }
    
    public function borrowBook(int $bookId)
    {
        try {
            $checkSql = "SELECT id, borrowed FROM Books WHERE id = :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $bookId]);
            $book = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                return ['success' => false, 'message' => 'Livro não encontrado'];
            }

            if ($book['borrowed']) {
                return ['success' => false, 'message' => 'Livro já está emprestado'];
            }

            $updateSql = "UPDATE Books SET borrowed = 1 WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':id' => $bookId]);

            return ['success' => true, 'message' => 'Livro emprestado com sucesso'];
        } catch (PDOException $e) {
            error_log("Database error in borrowBook: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor'];
        }
    }

    public function returnBook(int $bookId)
    {
        try {
            $checkSql = "SELECT id, borrowed FROM Books WHERE id = :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $bookId]);
            $book = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                return ['success' => false, 'message' => 'Livro não encontrado'];
            }

            if (!$book['borrowed']) {
                return ['success' => false, 'message' => 'Livro já está disponível'];
            }

            $updateSql = "UPDATE Books SET borrowed = 0 WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':id' => $bookId]);

            return ['success' => true, 'message' => 'Livro devolvido com sucesso'];
        } catch (PDOException $e) {
            error_log("Database error in returnBook: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do servidor'];
        }
    }
}
