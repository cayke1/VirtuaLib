<?php

class BookModel extends Database {
    private $pdo;

    public function __construct(){
        $this->pdo = $this->getConnection();
    }

    #somente livros disponiveis
    public function getAvaibleBooks(){
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM livros WHERE status = 'disponivel'");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e){
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    
}