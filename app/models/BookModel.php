<?php

class BookModel extends Database {

    private $pdo;

    public function __construct(){
        $this->pdo = $this->getConnection();
   
    }

    public function getBooks(){
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM Books");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }
        catch(PDOException $e){
            echo "Error: " . $e->getMessage();
            return [];

        }
        

    

    }
}
?>

