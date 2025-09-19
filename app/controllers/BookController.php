<?php

class BookController extends RenderView
{

    public function listBooks()
    {

        $bookModel = new BookModel();
        $books = $bookModel->getBooks();

        $this->loadView('partials/header', ['titulo' => 'Books']);
        $this->loadView('home', ['books' => $books]);
    }

    public function searchBooks()
    {
        try {

            $query = $_GET['q'] ?? '';
            error_log("Search query: " . $query);

            header('Content-Type: application/json; charset=utf-8');

            if (empty($query)) {
                echo json_encode([]);
                exit;
            }

            $bookModel = new BookModel();
            $results = $bookModel->search($query);


            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (Exception $e) {
            error_log("Error in searchBooks: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
            exit;
        }
    }
}
