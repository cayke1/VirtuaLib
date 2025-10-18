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

    /**
     * Dados de fallback para atividades recentes quando não há conexão com o banco
     */
    private function getFallbackRecentActivities() {
        // Retorna uma lista simples de atividades simuladas com mesmas chaves esperadas
        $now = new DateTime();
        $activities = [];

        for ($i = 0; $i < 10; $i++) {
            $requested = clone $now;
            $requested->modify("-{$i} days");

            $due = clone $requested;
            $due->modify('+14 days');

            $activities[] = [
                'user_name' => "Usuário {$i}",
                'book_title' => "Livro Exemplo {$i}",
                'requested_at' => $requested->format('Y-m-d H:i:s'),
                'due_date' => $due->format('Y-m-d H:i:s'),
                'status' => ($i % 3 === 0) ? 'pendente' : (($i % 3 === 1) ? 'emprestado' : 'atrasado')
            ];
        }

        return $activities;
    }

    public function getRecentActivities() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("
                    SELECT u.name AS user_name, b.title AS book_title, br.requested_at, br.due_date, br.status
                    FROM borrows br
                    JOIN users u ON br.user_id = u.id
                    JOIN books b ON br.book_id = b.id
                    ORDER BY br.requested_at DESC
                    LIMIT 100
                ");
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                return $this->getFallbackRecentActivities();
            }
        }
        
        return $this->getFallbackRecentActivities();
    }
}
