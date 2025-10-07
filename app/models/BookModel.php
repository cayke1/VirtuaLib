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
    
    public function createBook(array $data)
    {
        $sql = "INSERT INTO Books (title, author, genre, year, description, available) 
                VALUES (:title, :author, :genre, :year, :description, :available)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':title', $data['title'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':author', $data['author'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':genre', $data['genre'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':year', (int)($data['year'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':description', $data['description'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':available', isset($data['available']) ? (int)(bool)$data['available'] : 1, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in createBook: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalBooks()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Books");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Database error in getTotalBooks: " . $e->getMessage());
            return 0;
        }
    }

    public function getBooksByCategory()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    genre as nome,
                    COUNT(*) as total,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM Books)), 1) as percentual
                FROM Books 
                GROUP BY genre 
                ORDER BY total DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $colors = ['#059669', '#3b82f6', '#14b8a6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4'];
            $categories = [];
            
            foreach ($results as $index => $result) {
                $categories[] = [
                    'nome' => $result['nome'] ?: 'Sem categoria',
                    'percentual' => (float)$result['percentual'],
                    'color' => $colors[$index % count($colors)]
                ];
            }

            return $categories;
        } catch (PDOException $e) {
            error_log("Database error in getBooksByCategory: " . $e->getMessage());
            return [];
        }
    }
}
