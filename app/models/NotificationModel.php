<?php
class NotificationModel extends Database
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
    }

    public function getByUserId(int $userId)
    {
        $sql = "SELECT id, user_id, title, message, data, is_read, created_at
                FROM Notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 100";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadByUserId(int $userId)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM Notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function markAllReadByUserId(int $userId)
    {
        $sql = "UPDATE Notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    public function createBulk(array $items)
    {
        $sql = "INSERT INTO Notifications (user_id, title, message, data, is_read, created_at) VALUES (:user_id, :title, :message, :data, 0, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $this->pdo->beginTransaction();
        try {
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

    public function create(int $userId, string $title, string $message, ?array $data = null)
    {
        $sql = "INSERT INTO Notifications (user_id, title, message, data, is_read, created_at)
                VALUES (:user_id, :title, :message, :data, 0, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':message', $message, PDO::PARAM_STR);
        $stmt->bindValue(':data', $data ? json_encode($data) : null, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function markAsRead(int $id, int $userId)
    {
        $sql = "UPDATE Notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function delete(int $id, int $userId)
    {
        $sql = "DELETE FROM Notifications WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
}
