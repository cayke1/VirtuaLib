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

    public function updateBook($id, array $data)
    {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params[':title'] = $data['title'];
        }
        if (isset($data['author'])) {
            $fields[] = 'author = :author';
            $params[':author'] = $data['author'];
        }
        if (isset($data['genre'])) {
            $fields[] = 'genre = :genre';
            $params[':genre'] = $data['genre'];
        }
        if (isset($data['year'])) {
            $fields[] = 'year = :year';
            $params[':year'] = (int)$data['year'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (isset($data['available'])) {
            $fields[] = 'available = :available';
            $params[':available'] = (int)(bool)$data['available'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE Books SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in updateBook: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBook($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM Books WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in deleteBook: " . $e->getMessage());
            return false;
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

    /**
     * Dados de fallback quando não há conexão com banco
     */
    private function getFallbackBooks()
    {
        return [
            [
                'id' => 1,
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'genre' => 'Romance',
                'year' => 1899,
                'description' => 'Um dos maiores clássicos da literatura brasileira, narrado por Bentinho e sua dúvida sobre Capitu.',
                'available' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'title' => '1984',
                'author' => 'George Orwell',
                'genre' => 'Ficção Científica',
                'year' => 1949,
                'description' => 'Uma distopia política sobre um regime totalitário que vigia todos os cidadãos.',
                'available' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 3,
                'title' => 'O Senhor dos Anéis: A Sociedade do Anel',
                'author' => 'J.R.R. Tolkien',
                'genre' => 'Fantasia',
                'year' => 1954,
                'description' => 'Primeiro volume da trilogia épica que segue a jornada de Frodo para destruir o Um Anel.',
                'available' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ]
        ];
    }
}
