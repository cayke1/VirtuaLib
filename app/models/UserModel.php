<?php
class UserModel extends Database
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
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
}