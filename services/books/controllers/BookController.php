<?php
/**
 * Book Controller - Serviço de Livros
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class BookController {
    private $bookModel;
    
    public function __construct() {
        $this->bookModel = new BookModel();
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
    /**
     * Renderizar view de livros
     */
    public function listBooks() {
        $books = $this->bookModel->getBooksList();
        
        $data = [
            'title' => 'Books Service - Virtual Library',
            'books' => $books
        ];
        
        // Render the view
        View::display('books', $data);
    }
}
