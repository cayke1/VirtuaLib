<?php

class BookController extends RenderView {
    private $bookModel;

    public function __construct() {
        $this->bookModel = new BookModel();
    }

    public function detail($id) {
        if ($this->bookModel->getBookById($id)) {
            $book = [
                'id' => $this->bookModel->id,
                'titulo' => $this->bookModel->titulo,
                'autor' => $this->bookModel->autor,
                'ano_publicacao' => $this->bookModel->ano_publicacao,
                'editora' => $this->bookModel->editora,
                'genero' => $this->bookModel->genero,
                'sinopse' => $this->bookModel->sinopse,
                'isbn' => $this->bookModel->isbn,
                'disponivel' => $this->bookModel->disponivel
            ];
            
            $this->loadView('partials/header', ['title' => $book['titulo']]);
            $this->loadView('book_detail', ['book' => $book]);
            $this->loadView('partials/footer', []);
        } else {
            $notFound = new NotFoundController();
            $notFound->index();
        }
    }

    public function index() {
        $stmt = $this->bookModel->getAllBooks();
        $books = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $books[] = $row;
        }
        
        $this->loadView('partials/header', ['title' => 'Biblioteca Virtual']);
        $this->loadView('home', ['books' => $books]);
        $this->loadView('partials/footer', []);
    }
    
}
?>