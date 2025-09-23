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

    public function return($id)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE Books SET borrowed = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in return: " . $e->getMessage());
            return false;
        }
    }
}
