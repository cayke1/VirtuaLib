<?php
class Database {
    private $host;
    private $database;
    private $user;
    private $password;
    private $port;
    public function getConnection()
    {
        $this->host = LoadEnv::get('DB_HOST');
        $this->database = LoadEnv::get('DB_NAME');
        $this->user = LoadEnv::get('DB_USER');
        $this->password = LoadEnv::get('DB_PASSWORD');
        $this->port = LoadEnv::get('DB_PORT');
        try {
            $pdo = new PDO(
                "mysql:host=$this->host;port=$this->port;dbname=$this->database",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Erro de conexão com o banco: " . $e->getMessage() . " (Código: " . $e->getCode() . ")");
        }
    }
}