<?php
/**
 * User Model - Serviço de Autenticação
 */

class UserModel {
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
     * Obter dados do usuário
     */
    public function getUserData() {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM users LIMIT 1");
                $user = $stmt->fetch();
                return $user ?: $this->getFallbackUserData();
            } catch (PDOException $e) {
                return $this->getFallbackUserData();
            }
        }
        
        return $this->getFallbackUserData();
    }
    
    /**
     * Dados de fallback quando não há conexão com banco
     */
    private function getFallbackUserData() {
        return [
            'id' => 1,
            'name' => 'Usuário Exemplo',
            'email' => 'usuario@exemplo.com',
            'role' => 'user',
            'created_at' => '2024-01-01 10:00:00',
            'status' => 'active'
        ];
    }
    public function getAll()
    {
        $query = $this->pdo->query("SELECT id, name, email, role, created_at FROM Users");
        if ($query->rowCount() > 0) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createUser($name, $email, $password, $role = 'user')
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO Users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return [false, null];
        }
        if (!isset($user['password'])) {
            return [false, null];
        }
        $valid = password_verify($password, $user['password']);
        if (!$valid) {
            return [false, null];
        }
        unset($user['password']);
        return [true, $user];
    }

    public function getActiveUsersCount()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT u.id) as total
                FROM Users u
                INNER JOIN Borrows b ON u.id = b.user_id
                WHERE b.requested_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Database error in getActiveUsersCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserById(int $userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, email, role, created_at 
                FROM Users 
                WHERE id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getUserById: " . $e->getMessage());
            return null;
        }
    }
}
