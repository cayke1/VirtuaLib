<?php
/**
 * Stats Model - Serviço de Dashboard
 */

class StatsModel {
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
     * Obter estatísticas gerais
     */
    public function getGeneralStats() {
        if ($this->pdo) {
            try {
                $stats = [];
                
                // Total de livros
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM books");
                $stats['total_books'] = $stmt->fetch()['total'];
                
                // Livros emprestados
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM books WHERE borrowed = 1");
                $stats['borrowed_books'] = $stmt->fetch()['total'];
                
                // Livros disponíveis
                $stats['available_books'] = $stats['total_books'] - $stats['borrowed_books'];
                
                // Total de usuários
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
                $stats['total_users'] = $stmt->fetch()['total'];
                
                // Empréstimos ativos
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM borrows WHERE returned_at IS NULL");
                $stats['pending_requests'] = $stmt->fetch()['total'];
                
                return $stats;
            } catch (PDOException $e) {
                return $this->getFallbackStatsData();
            }
        }
        
        return $this->getFallbackStatsData();
    }
    
    /**
     * Dados de fallback quando não há conexão com banco
     */
    private function getFallbackStatsData() {
        return [
            'total_books' => 150,
            'borrowed_books' => 45,
            'available_books' => 105,
            'total_users' => 25,
            'pending_requests' => 8,
            'user_borrows' => 3,
            'monthly_borrows' => 12,
            'popular_category' => 'Ficção Científica'
        ];
    }
}
