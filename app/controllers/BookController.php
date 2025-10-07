<?php

class BookController extends RenderView
{
    use AuthGuard;

    /**
     * Lista todos os livros disponíveis com informações de empréstimo.
     */
    public function listBooks()
    {
        $bookModel = new BookModel();
        $books = $bookModel->getBooks();

        $currentUserId = $_SESSION['user']['id'] ?? null;
        $borrowedLookup = [];
        $pendingLookup = [];
        if ($currentUserId) {
            $borrowModel = new BorrowModel();
            $borrowedIds = $borrowModel->getActiveBorrowedBookIdsByUser((int)$currentUserId);
            $pendingIds = $borrowModel->getPendingRequestBookIdsByUser((int)$currentUserId);

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

        $this->loadView('partials/header', ['title' => 'Livros']);
        $this->loadView('home', ['books' => $books]);
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

    public function requestBook($id)
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

        $borrowModel = new BorrowModel();
        $result = $borrowModel->requestBorrow((int)$id, (int)$userId);

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

        $borrowModel = new BorrowModel();
        $result = $borrowModel->approveBorrow((int)$requestId, (int)$adminUserId);

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

        $borrowModel = new BorrowModel();
        $result = $borrowModel->returnBook((int)$id, (int)$userId);

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

    public function viewHistory()
    {
        $this->requireAuth();

        $borrowModel = new BorrowModel();
        $history = $borrowModel->getHistory();

        $this->loadView('history', [
            'title' => 'Histórico de Empréstimos',
            'history' => $history,
            'currentUser' => $_SESSION['user'] ?? null,
        ]);
    }

    public function viewDashboard()
    {
        $this->requireAuth();

        // Verificar se é admin
        $isAdmin = $_SESSION['user']['role'] ?? null === 'admin';

        $pendingRequests = [];
        if ($isAdmin) {
            $borrowModel = new BorrowModel();
            $pendingRequests = $borrowModel->getPendingRequests();
        }

        $this->loadView('dashboard', [
            'title' => 'Dashboard',
            'pendingRequests' => $pendingRequests,
            'isAdmin' => $isAdmin
        ]);
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

        $borrowModel = new BorrowModel();
        $result = $borrowModel->rejectRequest((int)$requestId, (int)$adminUserId);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
