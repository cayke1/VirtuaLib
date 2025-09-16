<?php
class Database {
    private $host;
    private $database;
    private $user;
    private $password;
    private $port;

    public function getConnection()
    {
        $this->host     = getenv('DB_HOST');
        $this->database = getenv('DB_DATABASE');
        $this->user     = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->port     = getenv('DB_PORT');

        try {
            $pdo = new PDO(
                "mysql:host=$this->host;port=$this->port;dbname=$this->database;charset=utf8",
                $this->user,
                $this->password
            );
            return $pdo;
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
            exit();
        }
    }
}

$db = new Database();
$pdo = $db->getConnection();
