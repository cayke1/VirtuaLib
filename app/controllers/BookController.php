<?php

class BookController extends RenderView {
    
    public function index() {
        $books = new BookModel();
        
        $args = [
            'title' => 'Livros Disponíveis',
            'books' => $books->getAll(),
            'totalBooks' => $books->getTotalAvailable()
        ];
        
        $this->loadView('partials/header', ['title' => $args['title']]);
        $this->loadView('books/index', $args);
        $this->loadView('partials/footer', []);
    }
    
    public function show($id) {
        $books = new BookModel();
        $book = $books->getById($id);
        
        if (!$book) {
            $this->handleNotFound();
            return;
        }
        
        $args = [
            'title' => $book['title'] . ' - ' . $book['author'],
            'book' => $book
        ];
        
        $this->loadView('partials/header', ['title' => $args['title']]);
        $this->loadView('books/show', $args);
        $this->loadView('partials/footer', []);
    }
    
    public function search() {
        $searchTerm = $_GET['q'] ?? '';
        $books = new BookModel();
        
        $searchResults = [];
        if (!empty($searchTerm)) {
            $searchResults = array_merge(
                $books->searchByTitle($searchTerm),
                $books->searchByAuthor($searchTerm)
            );
            
            $uniqueResults = [];
            $seenIds = [];
            foreach ($searchResults as $book) {
                if (!in_array($book['id'], $seenIds)) {
                    $uniqueResults[] = $book;
                    $seenIds[] = $book['id'];
                }
            }
            $searchResults = $uniqueResults;
        }
        
        $args = [
            'title' => 'Resultados da Busca',
            'books' => $searchResults,
            'searchTerm' => $searchTerm,
            'totalResults' => count($searchResults)
        ];
        
        $this->loadView('partials/header', ['title' => $args['title']]);
        $this->loadView('books/search', $args);
        $this->loadView('partials/footer', []);
    }
    
    private function handleNotFound() {
        $notFoundPath = __DIR__ . "/NotFoundController.php";
        
        if (file_exists($notFoundPath)) {
            require_once $notFoundPath;
            $controller = new NotFoundController();
            $controller->index();
        } else {
            http_response_code(404);
            echo "404 - Livro não encontrado";
        }
    }
}
