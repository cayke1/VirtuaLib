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
}
