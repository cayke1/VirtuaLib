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
    public function viewBookDetails($id)
    {
        $bookModel = new BookModel();
        $book = $bookModel->getBookById($id);

        if (!$book) {
            http_response_code(404);
            echo "Livro não encontrado";
            exit;
        }

        $this->loadView('partials/header', ['titulo' => 'Detalhes do Livro']);
        $this->loadView('components/book-details', ['book' => $book]);
    }

    public function returnBook($id)
    {
        $bookModel = new BookModel();
        $success = $bookModel->return($id);

        if (!$success) {
            return http_response_code(400);
        }
        header("Content-Type: application/json");
        echo json_encode([
            "success" => $success,
            "message" => $success ? "Livro devolvido com sucesso" : "Erro ao devolver o livro"
        ]);
    }
}
