<?php
/**
 * Book Controller - Serviço de Livros e Empréstimos
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class BookController {
    private $bookModel;
    private $borrowModel;
    
    public function __construct() {
        $this->bookModel = new BookModel();
        $this->borrowModel = new BorrowModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    /**
     * Lista todos os livros disponíveis com informações de empréstimo.
     */
    public function listBooks()
    {
        $books = $this->bookModel->getBooks();

        $currentUserId = $_SESSION['user']['id'] ?? null;
        $borrowedLookup = [];
        $pendingLookup = [];
        if ($currentUserId) {
            $borrowedIds = $this->borrowModel->getActiveBorrowedBookIdsByUser((int)$currentUserId);
            $pendingIds = $this->borrowModel->getPendingRequestBookIdsByUser((int)$currentUserId);

            if (!empty($borrowedIds)) {
                $borrowedLookup = array_flip($borrowedIds);
            }
            if (!empty($pendingIds)) {
                $pendingLookup = array_flip($pendingIds);
            }
        }

        foreach ($books as &$book) {
            $bookId = (int)($book['id'] ?? 0);
            $book['available'] = (int)($book['available'] ?? 0);
            $book['borrowed_by_current_user'] = isset($borrowedLookup[$bookId]);
            $book['requested_by_current_user'] = isset($pendingLookup[$bookId]);
        }
        unset($book);

        View::display('partials/header', ['title' => 'Livros']);
        View::display('home', ['books' => $books]);
        View::display('partials/footer');
    }

    public function searchBooks()
    {
        try {
            $query = $_GET['q'] ?? '';

            header('Content-Type: application/json; charset=utf-8');

            if (empty($query)) {
                echo json_encode([]);
                exit;
            }

            $results = $this->bookModel->search($query);

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
        $book = $this->bookModel->getBookById($id);

        if (!$book) {
            http_response_code(404);
            echo "Livro não encontrado";
            exit;
        }

        // Adicionar informações de empréstimo como no listBooks
        $currentUserId = $_SESSION['user']['id'] ?? null;
        $book['available'] = (int)($book['available'] ?? 0);
        $book['borrowed_by_current_user'] = false;
        $book['requested_by_current_user'] = false;

        if ($currentUserId) {
            $borrowedIds = $this->borrowModel->getActiveBorrowedBookIdsByUser((int)$currentUserId);
            $pendingIds = $this->borrowModel->getPendingRequestBookIdsByUser((int)$currentUserId);
            
            $book['borrowed_by_current_user'] = in_array((int)$id, $borrowedIds);
            $book['requested_by_current_user'] = in_array((int)$id, $pendingIds);
        }

        View::display('partials/header', ['title' => $book['title']]);
        View::display('components/book-details', ['book' => $book]);
        View::display('partials/footer');
    }

    public function requestBook($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }

        // Debug: verificar sessão
        error_log('RequestBook - Session data: ' . print_r($_SESSION, true));
        error_log('RequestBook - Book ID: ' . $id);

        $this->requireAuth();

        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            return;
        }

        error_log('RequestBook - User ID: ' . $userId);

        $result = $this->borrowModel->requestBorrow((int)$id, (int)$userId);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function approveBorrow($requestId)
    {

        error_log("Aprovando solicitação de empréstimo ID: $requestId");
        $this->requireAuth(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }

        $adminUserId = $_SESSION['user']['id'] ?? null;
        if (!$adminUserId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Administrador não autenticado.']);
            return;
        }

        $result = $this->borrowModel->approveBorrow((int)$requestId, (int)$adminUserId);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function returnBook($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }

        $this->requireAuth();

        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            return;
        }

        $result = $this->borrowModel->returnBook((int)$id, (int)$userId);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
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

        $id = $this->bookModel->createBook($payload);
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
        if (!$raw) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function validateCreatePayload($data)
    {
        if (!is_array($data)) {
            return false;
        }
        $required = ['title', 'author', 'genre', 'year', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        if (!is_numeric($data['year'])) {
            return false;
        }
        return true;
    }

    public function rejectRequest($requestId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }

        $this->requireRole('admin');

        $adminUserId = $_SESSION['user']['id'] ?? null;
        if (!$adminUserId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Administrador não autenticado.']);
            return;
        }

        $result = $this->borrowModel->rejectRequest((int)$requestId, (int)$adminUserId);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            exit;
        }
    }

    private function requireRole($role)
    {
        $this->requireAuth();
        if (($_SESSION['user']['role'] ?? '') !== $role) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acesso negado']);
            exit;
        }
    }
}