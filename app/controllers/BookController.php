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

    public function returnBook()
    {
        try {
            header('Content-Type: application/json; charset=utf-8');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $bookId = $input['book_id'] ?? null;

            if (!$bookId || !is_numeric($bookId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID do livro inválido']);
                exit;
            }

            $bookModel = new BookModel();
            $result = $bookModel->returnBook((int)$bookId);

            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }

            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;

        } catch (Exception $e) {
            error_log("Error in returnBook: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
            exit;
        }
    }
}
