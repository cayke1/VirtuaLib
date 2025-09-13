<?php

class BookModel {
    private $conn;
    private $table_name = "livros";

    public $id;
    public $titulo;
    public $autor;
    public $ano_publicacao;
    public $editora;
    public $genero;
    public $sinopse;
    public $isbn;
    public $disponivel;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getBookById($id) {
        $query = "SELECT id_livro, titulo, autor, editora, ano_publicacao, genero, sinopse, isbn, disponivel 
                  FROM " . $this->table_name . " 
                  WHERE id_livro = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id_livro'];
            $this->titulo = $row['titulo'];
            $this->autor = $row['autor'];
            $this->ano_publicacao = $row['ano_publicacao'];
            $this->editora = $row['editora'];
            $this->genero = $row['genero'];
            $this->sinopse = $row['sinopse'];
            $this->isbn = $row['isbn'];
            $this->disponivel = $row['disponivel'];
            return true;
        }

        return false;
    }

    public function getAllBooks() {
        $query = "SELECT id_livro as id, titulo, autor, ano_publicacao, editora, genero, sinopse, isbn, disponivel 
                  FROM " . $this->table_name . " 
                  ORDER BY titulo ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
    
}
?>