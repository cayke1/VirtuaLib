<?php

class BookController extends RenderView {
  
    public function listBooks() {

        $bookModel = new BookModel();
        $books = $bookModel->getAvaibleBooks();

        #renderiza o componente book-card passando o array de livros
        echo $books;

        $this->loadView('partials/header', ['titulo' => 'Books']);
        $this->loadView('home', ['books' => $books]);



    }
}
?>