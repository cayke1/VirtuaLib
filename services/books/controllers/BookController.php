<?php
/**
 * Book Controller - Serviço de Livros e Empréstimos
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';
require_once __DIR__ . '/../../utils/ImageUploader.php';
require_once __DIR__ . '/../../utils/PdfUploader.php';

class BookController {
    private $bookModel;
    private $borrowModel;
    private $imageUploader;
    private $pdfUploader;
    
    public function __construct() {
        $this->bookModel = new BookModel();
        $this->borrowModel = new BorrowModel();
        $this->imageUploader = new ImageUploader();
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
        
        // Verificar se é uma chamada entre serviços (com token) ou chamada direta (com sessão)
        $isServiceCall = $this->isServiceCall();
        
        if (!$isServiceCall) {
            $this->requireAuth(['admin']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }

        // Tentar obter adminUserId da sessão ou dos dados POST (para chamadas entre serviços)
        $adminUserId = $_SESSION['user']['id'] ?? null;
        
        // Se não há sessão, tentar obter dos dados POST (chamada entre serviços)
        if (!$adminUserId) {
            $postData = $this->readJsonBody();
            $adminUserId = $postData['admin_user_id'] ?? null;
        }
        
        if (!$adminUserId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Administrador não autenticado.']);
            return;
        }

        $result = $this->borrowModel->approveBorrow((int)$requestId, (int)$adminUserId);

        // Se aprovado com sucesso, notificar o usuário
        if ($result['success']) {
            $this->notifyUser($result['user_id'], $result['book_title'], 'approved');
        }

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

    /**
     * API para listar todos os livros
     */
    public function getBooksApi()
    {
        $this->requireRole('admin');
        
        try {
            $books = $this->bookModel->getBooks();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['books' => $books], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error in getBooksApi: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API para obter um livro específico
     */
    public function getBookByIdApi($id)
    {
        $this->requireRole('admin');
        
        try {
            $book = $this->bookModel->getBookById($id);
            if (!$book) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Livro não encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['book' => $book], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error in getBookByIdApi: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Criar um novo livro
     */
    public function createBook()
    {
        $this->requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // ✅ Verificar se tem arquivos
        $hasImageUpload = isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE;
        $hasPdfUpload = isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasImageUpload || $hasPdfUpload) {
            $this->createBookWithFiles();
            return;
        }

        // Criar sem arquivos (JSON)
        $payload = $this->readJsonBody();
        if (!$this->validateCreatePayload($payload)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $bookId = $this->bookModel->createBook($payload);
            if (!$bookId) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Falha ao criar livro'], JSON_UNESCAPED_UNICODE);
                return;
            }

            http_response_code(201);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'Livro criado com sucesso', 'id' => $bookId], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error in createBook: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    private function createBookWithFiles()
    {
        try {
            // Validar dados básicos
            $requiredFields = ['title', 'author', 'genre', 'year'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => "Campo obrigatório: $field"], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $bookData = [
                'title' => $_POST['title'],
                'author' => $_POST['author'],
                'genre' => $_POST['genre'],
                'year' => (int)$_POST['year'],
                'description' => $_POST['description'] ?? '',
                'available' => isset($_POST['available']) ? (int)$_POST['available'] : 1
            ];

            // Criar livro primeiro para ter o ID
            $bookId = $this->bookModel->createBook($bookData);
            if (!$bookId) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Falha ao criar livro'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $updateData = [];
            $responseData = ['message' => 'Livro criado com sucesso', 'id' => $bookId];

            // Upload de imagem (opcional)
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->imageUploader->uploadImage($_FILES['cover_image'], $bookId);
                if ($uploadResult['success']) {
                    $updateData['cover_image'] = $uploadResult['path'];
                    $responseData['cover_image'] = $uploadResult['path'];
                }
            }

            // Upload de PDF (opcional)
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                if (!isset($this->pdfUploader)) {
                    require_once __DIR__ . '/../../utils/PdfUploader.php';
                    $this->pdfUploader = new PdfUploader();
                }
                
                $pdfResult = $this->pdfUploader->uploadPdf($_FILES['pdf_file'], $bookId);
                if ($pdfResult['success']) {
                    $updateData['pdf_src'] = $pdfResult['path'];
                    $responseData['pdf_src'] = $pdfResult['path'];
                }
            }

            // Atualizar com os caminhos dos arquivos
            if (!empty($updateData)) {
                $this->bookModel->updateBook($bookId, $updateData);
            }

            http_response_code(201);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($responseData, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error in createBookWithFiles: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Atualizar um livro existente
     */
    public function updateBook($id)
    {
        $this->requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // ✅ Verificar se tem QUALQUER arquivo (imagem OU PDF)
        $hasImageUpload = isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE;
        $hasPdfUpload = isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE;
        
        if ($hasImageUpload || $hasPdfUpload) {
            $this->updateBookWithFiles($id);
            return;
        }
        
        // Processar dados JSON (sem arquivos)
        $payload = $this->readJsonBody();
        if (!$this->validateUpdatePayload($payload)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $success = $this->bookModel->updateBook($id, $payload);
            if (!$success) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Falha ao atualizar livro'], JSON_UNESCAPED_UNICODE);
                return;
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'Livro atualizado com sucesso'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error in updateBook: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

/**
 * ✅ Atualizar livro com upload de imagem E/OU PDF (ambos opcionais)
 */
    private function updateBookWithFiles($id)
    {
        try {
            // Obter dados atuais do livro
            $currentBook = $this->bookModel->getBookById($id);
            if (!$currentBook) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Livro não encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Preparar dados para atualização
            $updateData = [];
            
            if (!empty($_POST['title'])) {
                $updateData['title'] = $_POST['title'];
            }
            if (!empty($_POST['author'])) {
                $updateData['author'] = $_POST['author'];
            }
            if (!empty($_POST['genre'])) {
                $updateData['genre'] = $_POST['genre'];
            }
            if (!empty($_POST['year'])) {
                $updateData['year'] = (int)$_POST['year'];
            }
            if (!empty($_POST['description'])) {
                $updateData['description'] = $_POST['description'];
            }
            if (isset($_POST['available'])) {
                $updateData['available'] = (int)$_POST['available'];
            }

            $responseData = ['message' => 'Livro atualizado com sucesso'];

            // ✅ Upload da imagem (se enviada)
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->imageUploader->uploadImage($_FILES['cover_image'], $id);
                if (!$uploadResult['success']) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Erro no upload da imagem: ' . $uploadResult['message']], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $updateData['cover_image'] = $uploadResult['path'];
                $responseData['cover_image'] = $uploadResult['path'];
                $oldImage = $currentBook['cover_image'] ?? null;
            }

            // ✅ Upload do PDF (se enviado)
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                if (!isset($this->pdfUploader)) {
                    require_once __DIR__ . '/../../utils/PdfUploader.php';
                    $this->pdfUploader = new PdfUploader();
                }
                
                $pdfResult = $this->pdfUploader->uploadPdf($_FILES['pdf_file'], $id);
                if (!$pdfResult['success']) {
                    // Se já uploadou imagem, deletar antes de retornar erro
                    if (isset($uploadResult) && $uploadResult['success']) {
                        $this->imageUploader->deleteImage($uploadResult['path']);
                    }
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Erro no upload do PDF: ' . $pdfResult['message']], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $updateData['pdf_src'] = $pdfResult['path'];
                $responseData['pdf_src'] = $pdfResult['path'];
                $oldPdf = $currentBook['pdf_src'] ?? null;
            }

            // Atualizar livro
            $success = $this->bookModel->updateBook($id, $updateData);
            if (!$success) {
                // Rollback: deletar novos arquivos se falhou
                if (isset($uploadResult) && $uploadResult['success']) {
                    $this->imageUploader->deleteImage($uploadResult['path']);
                }
                if (isset($pdfResult) && $pdfResult['success']) {
                    $this->pdfUploader->deletePdf($pdfResult['path']);
                }
                
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Falha ao atualizar livro'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Deletar arquivos antigos se existirem e foram substituídos
            if (isset($oldImage) && !empty($oldImage)) {
                $this->imageUploader->deleteImage($oldImage);
            }
            if (isset($oldPdf) && !empty($oldPdf)) {
                $this->pdfUploader->deletePdf($oldPdf);
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($responseData, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("Error in updateBookWithFiles: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Deletar um livro
     */
    public function deleteBook($id)
    {
        $this->requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Obter dados do livro antes de deletar para remover a imagem
            $book = $this->bookModel->getBookById($id);
            
            $success = $this->bookModel->deleteBook($id);
            if (!$success) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Falha ao deletar livro'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Deletar imagem se existir
            if ($book && !empty($book['cover_image'])) {
                $this->imageUploader->deleteImage($book['cover_image']);
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'Livro deletado com sucesso'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error in deleteBook: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
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

    private function validateUpdatePayload($data)
    {
        if (!is_array($data)) {
            return false;
        }
        
        // Pelo menos um campo deve estar presente
        $allowedFields = ['title', 'author', 'genre', 'year', 'description', 'cover_image', 'available'];
        $hasValidField = false;
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $hasValidField = true;
                break;
            }
        }
        
        if (!$hasValidField) {
            return false;
        }
        
        // Se year estiver presente, deve ser numérico
        if (isset($data['year']) && !is_numeric($data['year'])) {
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

        // Verificar se é uma chamada entre serviços (com token) ou chamada direta (com sessão)
        $isServiceCall = $this->isServiceCall();
        
        if (!$isServiceCall) {
            $this->requireRole('admin');
        }

        // Tentar obter adminUserId da sessão ou dos dados POST (para chamadas entre serviços)
        $adminUserId = $_SESSION['user']['id'] ?? null;
        
        // Se não há sessão, tentar obter dos dados POST (chamada entre serviços)
        if (!$adminUserId) {
            $postData = $this->readJsonBody();
            $adminUserId = $postData['admin_user_id'] ?? null;
        }
        
        if (!$adminUserId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Administrador não autenticado.']);
            return;
        }

        $result = $this->borrowModel->rejectRequest((int)$requestId, (int)$adminUserId);

        // Se rejeitado com sucesso, notificar o usuário
        if ($result['success']) {
            $this->notifyUser($result['user_id'], $result['book_title'], 'rejected');
        }

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obter solicitações pendentes via API
     */
    public function getPendingRequests()
    {
        $this->requireRole('admin');

        $limit = (int)($_GET['limit'] ?? 20);
        $limit = max(1, min(100, $limit)); // Limitar entre 1 e 100

        try {
            $requests = $this->borrowModel->getPendingRequests();
            
            // Limitar resultados
            $requests = array_slice($requests, 0, $limit);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['requests' => $requests], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações pendentes: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
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

    /**
     * Verificar se é uma chamada entre serviços (com token de autenticação)
     */
    private function isServiceCall()
    {
        $serviceToken = $_SERVER['HTTP_X_SERVICE_AUTH'] ?? '';
        $expectedToken = $_ENV['SERVICE_AUTH_TOKEN'] ?? 'default-token';
        
        return !empty($serviceToken) && $serviceToken === $expectedToken;
    }

    /**
     * Notificar usuário via serviço de notificações
     */
    private function notifyUser($userId, $bookTitle, $type)
    {
        $notificationsServiceUrl = $_ENV['NOTIFICATIONS_SERVICE_URL'] ?? 'http://notifications-service';
        $url = rtrim($notificationsServiceUrl, '/') . '/api/notifications/event';
        
        $eventData = [
            'type' => 'book.' . $type,
            'user_id' => $userId,
            'book_title' => $bookTitle,
            'book_id' => null // Será preenchido pelo serviço se necessário
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Service-Auth: ' . ($_ENV['SERVICE_AUTH_TOKEN'] ?? 'default-token')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Erro ao notificar usuário via serviço de notificações: " . $error);
        } elseif ($httpCode !== 200) {
            error_log("Falha ao notificar usuário. HTTP Code: $httpCode, Response: $response");
        } else {
            error_log("Notificação enviada com sucesso para usuário $userId, tipo: $type");
        }
    }
}