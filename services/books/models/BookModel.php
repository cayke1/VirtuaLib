<?php
/**
 * Book Model - Serviço de Livros
 */

class BookModel {
    private $pdo;
    
    public function __construct() {
        $this->connectDatabase();
    }
    
    /**
     * Conectar ao banco de dados
     */
    private function connectDatabase() {
        try {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $dbname = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Fallback para dados simulados se não conseguir conectar
            $this->pdo = null;
        }
    }
    
    /**
     * Obter lista de livros
     */
    public function getBooksList() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM books ORDER BY title");
                $books = $stmt->fetchAll();
                return $books ?: $this->getFallbackBooksData();
            } catch (PDOException $e) {
                return $this->getFallbackBooksData();
            }
        }
        
        return $this->getFallbackBooksData();
    }
    
    /**
     * Dados de fallback quando não há conexão com banco
     */
    private function getFallbackBooksData() {
        return [
            [
                'id' => 1,
                'title' => 'O Senhor dos Anéis',
                'author' => 'J.R.R. Tolkien',
                'isbn' => '978-85-359-0277-8',
                'category' => 'Fantasia',
                'borrowed' => false,
                'description' => 'Uma das maiores obras da literatura fantástica.'
            ],
            [
                'id' => 2,
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '978-85-359-0278-5',
                'category' => 'Ficção Científica',
                'borrowed' => true,
                'description' => 'Um clássico da literatura distópica.'
            ],
            [
                'id' => 3,
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'isbn' => '978-85-359-0279-2',
                'category' => 'Literatura Brasileira',
                'borrowed' => false,
                'description' => 'Uma das obras-primas da literatura brasileira.'
            ]
        ];
    }
}
