<?php

class BookController extends RenderView
{
    use AuthGuard;

    public function listBooks()
    {

        $bookModel = new BookModel();
        $books = $bookModel->getBooks();

        $this->loadView('partials/header', ['title' => 'Livros']);
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

        $this->loadView('partials/header', ['title' => $book['title']]);
        $this->loadView('components/book-details', ['book' => $book]);
    }

    public function borrowBook($id)
    {
        $bookModel = new BookModel();
        $success = $bookModel->borrowBook($id);

        if (!$success) {
            return http_response_code(400);
        }
        header("Content-Type: application/json");
        echo json_encode([
            "success" => $success,
            "message" => $success ? "Livro emprestado com sucesso" : "Erro ao emprestar o livro"
        ]);
    }

    public function returnBook($id)
    {
        $bookModel = new BookModel();
        $success = $bookModel->returnBook($id);

        if (!$success) {
            return http_response_code(400);
        }
        header("Content-Type: application/json");
        echo json_encode([
            "success" => $success,
            "message" => $success ? "Livro devolvido com sucesso" : "Erro ao devolver o livro"
        ]);
    }

    public function createBook()
    {
        $this->requireRole('admin');
        $payload = $this->readJsonBody();
        if (!$this->validateCreatePayload($payload)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Campos obrigatórios ausentes']);
            return;
        }

        $model = new BookModel();
        $id = $model->createBook($payload);
        if (!$id) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Falha ao criar livro']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['id' => $id, 'message' => 'Livro criado com sucesso']);
    }

    private function readJsonBody()
    {
        $raw = file_get_contents('php://input');
        if (!$raw) { return null; }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function validateCreatePayload($data)
    {
        if (!is_array($data)) { return false; }
        $required = ['title','author','genre','year','description'];
        foreach ($required as $field) {
            if (empty($data[$field])) { return false; }
        }
        if (!is_numeric($data['year'])) { return false; }
        return true;
    }
}
