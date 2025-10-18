<?php
/**
 * Notification Model - Serviço de Notificações
 */

class NotificationModel {
    private $pdo;
    
    public function __construct() {
        $this->connectDatabase();
    }
    
    private function connectDatabase() {
        try {
            $host = $_ENV['DB_HOST'] ?? 'jd6e9t.h.filess.io';
            $port = $_ENV['DB_PORT'] ?? '3307';
            $dbname = $_ENV['DB_NAME'] ?? 'vituralib_postdeepup';
            $username = $_ENV['DB_USER'] ?? 'vituralib_postdeepup';
            $password = $_ENV['DB_PASSWORD'] ?? '2a7f702e3f584523efba8585401f930b15f70335';
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            $this->pdo = null;
        }
    }
    
    public function getByUserId(int $userId) {
        if (!$this->pdo) {
            return $this->getFallbackNotificationsData();
        }
        
        try {
            $sql = "SELECT id, user_id, title, message, data, is_read, created_at
                    FROM Notifications
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT 100";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching notifications: ' . $e->getMessage());
            return $this->getFallbackNotificationsData();
        }
    }
    
    public function countUnreadByUserId(int $userId) {
        if (!$this->pdo) {
            return 0;
        }
        
        try {
            $sql = "SELECT COUNT(*) AS cnt FROM Notifications WHERE user_id = :user_id AND is_read = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['cnt'] ?? 0);
        } catch (PDOException $e) {
            error_log('Error counting unread notifications: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function markAllReadByUserId(int $userId) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $sql = "UPDATE Notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Error marking all as read: ' . $e->getMessage());
            return false;
        }
    }
    
    public function createBulk(array $items) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO Notifications (user_id, title, message, data, is_read, created_at) VALUES (:user_id, :title, :message, :data, 0, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $this->pdo->beginTransaction();
            
            foreach ($items as $it) {
                $stmt->bindValue(':user_id', (int)($it['user_id'] ?? 0), PDO::PARAM_INT);
                $stmt->bindValue(':title', $it['title'] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':message', $it['message'] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':data', isset($it['data']) ? json_encode($it['data']) : null, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            error_log('Error in createBulk: ' . $e->getMessage());
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function create(int $userId, string $title, string $message, ?array $data = null) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO Notifications (user_id, title, message, data, is_read, created_at)
                    VALUES (:user_id, :title, :message, :data, 0, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);
            $stmt->bindValue(':data', $data ? json_encode($data) : null, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error creating notification: ' . $e->getMessage());
            return false;
        }
    }
    
    public function markAsRead(int $id, int $userId) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $sql = "UPDATE Notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id, ':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Error marking as read: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete(int $id, int $userId) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $sql = "DELETE FROM Notifications WHERE id = :id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id, ':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Error deleting notification: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getUserNotifications() {
        return $this->getFallbackNotificationsData();
    }
    
    private function getFallbackNotificationsData() {
        return [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Livro Disponível',
                'message' => 'O livro "1984" está disponível para empréstimo.',
                'data' => null,
                'is_read' => 0,
                'created_at' => '2024-01-15 14:30:00'
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'title' => 'Empréstimo Aprovado',
                'message' => 'Seu empréstimo do livro "Dom Casmurro" foi aprovado.',
                'data' => '{"book_id": 1, "type": "approved"}',
                'is_read' => 1,
                'created_at' => '2024-01-15 09:45:00'
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'title' => 'Prazo de Devolução',
                'message' => 'Lembrete: O livro "O Senhor dos Anéis" deve ser devolvido em 3 dias.',
                'data' => '{"book_id": 2, "type": "reminder"}',
                'is_read' => 0,
                'created_at' => '2024-01-14 16:20:00'
            ]
        ];
    }
}
