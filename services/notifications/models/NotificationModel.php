<?php
/**
 * Notification Model - Serviço de Notificações
 */

class NotificationModel {
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
     * Obter notificações do usuário
     */
    public function getUserNotifications() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM Notifications ORDER BY created_at DESC LIMIT 10");
                $notifications = $stmt->fetchAll();
                return $notifications ?: $this->getFallbackNotificationsData();
            } catch (PDOException $e) {
                return $this->getFallbackNotificationsData();
            }
        }
        
        return $this->getFallbackNotificationsData();
    }
    
    /**
     * Dados de fallback quando não há conexão com banco
     */
    private function getFallbackNotificationsData() {
        return [
            [
                'id' => 1,
                'title' => 'Livro Disponível',
                'message' => 'O livro "1984" está disponível para empréstimo.',
                'type' => 'info',
                'read_at' => null,
                'created_at' => '2024-01-15 14:30:00'
            ],
            [
                'id' => 2,
                'title' => 'Empréstimo Aprovado',
                'message' => 'Seu empréstimo do livro "Dom Casmurro" foi aprovado.',
                'type' => 'success',
                'read_at' => '2024-01-15 10:15:00',
                'created_at' => '2024-01-15 09:45:00'
            ],
            [
                'id' => 3,
                'title' => 'Prazo de Devolução',
                'message' => 'Lembrete: O livro "O Senhor dos Anéis" deve ser devolvido em 3 dias.',
                'type' => 'warning',
                'read_at' => null,
                'created_at' => '2024-01-14 16:20:00'
            ]
        ];
    }
}
