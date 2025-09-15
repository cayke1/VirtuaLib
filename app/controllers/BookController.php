<?php

class BookController extends RenderView {


    public function listBooks() {

        $bookModel = new BookModel();
        $books = $bookModel->getAvaibleBooks();

        $this->loadView('partials/header', ['titulo' => 'Books']);
        $this->loadView('books', ['books' => $books]);
    }

 

}