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
                $this->password
            );
            return $pdo;
        } catch (PDOException $e) {
            echo "" . $e;
            //echo "Desculpa, erro interno, verifique o banco de dados!<br>";
            exit();
        }
    }
}
