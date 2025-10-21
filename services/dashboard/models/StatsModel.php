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
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM Books");
                $stats['total_books'] = $stmt->fetch()['total'];
                
                // Livros emprestados (status = 'approved' e returned_at IS NULL)
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM Borrows WHERE status = 'approved' AND returned_at IS NULL");
                $stats['borrowed_books'] = $stmt->fetch()['total'];
                
                // Livros disponíveis
                $stats['available_books'] = $stats['total_books'] - $stats['borrowed_books'];
                
                // Total de usuários
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM Users");
                $stats['total_users'] = $stmt->fetch()['total'];
                
                // Solicitações pendentes
                $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM Borrows WHERE status = 'pending'");
                $stats['pending_requests'] = $stmt->fetch()['total'];
                
                return $stats;
            } catch (PDOException $e) {
                error_log("Erro ao buscar estatísticas gerais: " . $e->getMessage());
                return $this->getFallbackStatsData();
            }
        }
        
        return $this->getFallbackStatsData();
    }
    
    /**
     * Dados de fallback quando não há conexão com banco
     */
    public function getFallbackStatsData() {
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
    public function getFallbackRecentActivities() {
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
                // Usar status em inglês para corresponder ao JS da dashboard
                'status' => ($i % 3 === 0) ? 'pending' : (($i % 3 === 1) ? 'borrowed' : 'returned')
            ];
        }

        return $activities;
    }
    
    /**
     * Empréstimos por mês (últimos 6 meses)
     */
    public function getBorrowsByMonth() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("
                    SELECT DATE_FORMAT(requested_at, '%Y-%m') AS month, COUNT(*) AS total_borrows
                    FROM Borrows
                    WHERE requested_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY month
                    ORDER BY month ASC
                ");
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Erro ao buscar empréstimos por mês: " . $e->getMessage());
                return $this->getFallbackBorrowsByMonth();
            }
        }

        return $this->getFallbackBorrowsByMonth();
    }

    /**
     * Fallback para empréstimos por mês
     */
    private function getFallbackBorrowsByMonth() {
        $months = [];
        $now = new DateTimeImmutable();
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->modify("-{$i} months")->format('Y-m');
            $months[] = ['month' => $m, 'total_borrows' => rand(5, 40)];
        }
        return $months;
    }

    /**
     * Top livros mais emprestados
     */
    public function getTopBooks() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("
                    SELECT b.id, b.title, b.author, COUNT(*) AS borrow_count
                    FROM Borrows br
                    JOIN Books b ON br.book_id = b.id
                    GROUP BY br.book_id
                    ORDER BY borrow_count DESC
                    LIMIT 5
                ");
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Erro ao buscar top livros: " . $e->getMessage());
                return $this->getFallbackTopBooks();
            }
        }

        return $this->getFallbackTopBooks();
    }

    /**
     * Fallback para top livros
     */
    private function getFallbackTopBooks() {
        return [
            ['id' => 1, 'title' => 'Dom Casmurro', 'author' => 'Machado de Assis', 'borrow_count' => 34],
            ['id' => 2, 'title' => '1984', 'author' => 'George Orwell', 'borrow_count' => 27],
            ['id' => 3, 'title' => 'O Senhor dos Anéis', 'author' => 'J.R.R. Tolkien', 'borrow_count' => 19]
        ];
    }

    /**
     * Distribuição por categoria
     */
    public function getBooksByCategory() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("
                    SELECT genre as nome, COUNT(*) as total, ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM Books)), 1) as percentual
                    FROM Books
                    GROUP BY genre
                    ORDER BY total DESC
                ");
                $results = $stmt->fetchAll();

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
                error_log("Erro ao buscar categorias: " . $e->getMessage());
                return $this->getFallbackBooksByCategory();
            }
        }

        return $this->getFallbackBooksByCategory();
    }

    /**
     * Fallback para categorias
     */
    private function getFallbackBooksByCategory() {
        return [
            ['nome' => 'Ficção Científica', 'percentual' => 28.5, 'color' => '#059669'],
            ['nome' => 'Romance', 'percentual' => 22.1, 'color' => '#3b82f6'],
            ['nome' => 'Fantasia', 'percentual' => 15.0, 'color' => '#14b8a6']
        ];
    }

    /**
     * Solicitações pendentes
     */
    public function getPendingRequests($limit = 20) {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    SELECT br.id, u.name AS user_name, u.email AS user_email, b.title AS book_title, b.author AS book_author, br.requested_at
                    FROM Borrows br
                    JOIN Users u ON br.user_id = u.id
                    JOIN Books b ON br.book_id = b.id
                    WHERE br.status = 'pending'
                    ORDER BY br.requested_at DESC
                    LIMIT :limit
                ");
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Erro ao buscar solicitações pendentes: " . $e->getMessage());
                return $this->getFallbackPendingRequests($limit);
            }
        }

        return $this->getFallbackPendingRequests($limit);
    }

    /**
     * Dados de fallback para solicitações pendentes
     */
    private function getFallbackPendingRequests($limit) {
        $now = new DateTimeImmutable();
        $requests = [];
        for ($i = 1; $i <= min(5, $limit); $i++) {
            $requests[] = [
                'id' => $i,
                'user_name' => "Usuário {$i}",
                'user_email' => "user{$i}@example.com",
                'book_title' => "Livro Exemplo {$i}",
                'book_author' => "Autor {$i}",
                'requested_at' => $now->modify("-{$i} days")->format('Y-m-d H:i:s')
            ];
        }
        return $requests;
    }

    /**
     * Estatísticas do perfil do usuário
     */
    public function getUserProfileStats($userId) {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("\n                    SELECT\n                        (SELECT COUNT(*) FROM borrows WHERE user_id = :uid) AS total_borrows,\n                        (SELECT COUNT(*) FROM borrows WHERE user_id = :uid AND returned_at IS NULL) AS active_borrows,\n                        (SELECT COUNT(*) FROM borrows WHERE user_id = :uid AND status = 'pending') AS pending_requests,\n                        (SELECT COUNT(*) FROM borrows WHERE user_id = :uid AND due_date < NOW() AND returned_at IS NULL) AS overdue_count\n                ");
                $stmt->execute([':uid' => $userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ?: [];
            } catch (PDOException $e) {
                // fallback
            }
        }

        // Fallback
        return [
            'total_borrows' => 5,
            'active_borrows' => 1,
            'pending_requests' => 0,
            'overdue_count' => 0
        ];
    }

    /**
     * Atividades recentes
     */
    public function getRecentActivities() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("
                    SELECT u.name AS user_name, b.title AS book_title, br.requested_at, br.due_date, br.status
                    FROM Borrows br
                    JOIN Users u ON br.user_id = u.id
                    JOIN Books b ON br.book_id = b.id
                    ORDER BY br.requested_at DESC
                    LIMIT 100
                ");
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Erro ao buscar atividades recentes: " . $e->getMessage());
                return $this->getFallbackRecentActivities();
            }
        }
        
        return $this->getFallbackRecentActivities();
    }

    /**
     * Histórico
     */
    public function getHistory($limit = 100) {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    SELECT u.name AS user_name, b.title AS book_title, br.requested_at, br.returned_at, br.status
                    FROM Borrows br
                    JOIN Users u ON br.user_id = u.id
                    JOIN Books b ON br.book_id = b.id
                    ORDER BY br.requested_at DESC
                    LIMIT :limit
                ");
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Erro ao buscar histórico: " . $e->getMessage());
                return $this->getFallbackHistory($limit);
            }
        }
        
        return $this->getFallbackHistory($limit);
    }

    /**
     * Fallback para histórico
     */
    private function getFallbackHistory($limit) {
        $now = new DateTimeImmutable();
        $history = [];
        $statusList = ['pending', 'approved', 'returned', 'late'];
        for ($i = 1; $i <= min(5, $limit); $i++) {
            $requested = $now->modify("-{$i} days")->format('Y-m-d H:i:s');
            $returned = ($i % 3 === 0) ? $now->modify("-".($i-1)." days")->format('Y-m-d H:i:s') : null;
            $history[] = [
                'user_name' => "Usuário {$i}",
                'book_title' => "Livro Exemplo {$i}",
                'requested_at' => $requested,
                'returned_at' => $returned,
                'status' => $statusList[$i % count($statusList)]
            ];
        }
        return $history;
    }

}

