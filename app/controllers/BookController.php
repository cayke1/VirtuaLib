<?php

class BookController extends RenderView {
  
    public function listBooks() {

        $bookModel = new BookModel();
        $books = $bookModel->getBooks();

        $this->loadView('partials/header', ['titulo' => 'Books']);
        $this->loadView('home', ['books' => $books]);



    }
}
?>

