<?php
class BookModel extends Database
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
    }

    public function getAll()
    {
        $query = $this->pdo->query("SELECT * FROM books WHERE available = 1 ORDER BY title ASC");
        return $query->rowCount() > 0 ? $query->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = ? AND available = 1");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    public function searchByTitle($searchTerm)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE title LIKE ? AND available = 1 ORDER BY title ASC");
        $stmt->execute(["%$searchTerm%"]);
        return $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function searchByAuthor($author)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE author LIKE ? AND available = 1 ORDER BY title ASC");
        $stmt->execute(["%$author%"]);
        return $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getTotalAvailable()
    {
        $query = $this->pdo->query("SELECT COUNT(*) as total FROM books WHERE available = 1");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
