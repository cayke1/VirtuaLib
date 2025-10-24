<?php

/**
 * Dashboard Controller - Serviço de Dashboard e Estatísticas
 */

// Include the View utility


class DashboardController
{
    #use AuthGuard;

    private $statsModel;
    use AuthGuard;

    public function __construct()
    {
        $this->statsModel = new StatsModel();

        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }

    /**
     * Renderizar view de dashboard
     */
    public function showDashboard()
    {
        $this->requireRole('admin');
        
        // Obter dados do usuário da sessão
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $_SESSION['user'] ?? null;
        
        $data = [
            'title' => 'Dashboard Service - Virtual Library',
            'isAdmin' => true, // Já verificamos que é admin
            'user' => $user // Passar dados do usuário para a view
        ];

        // Render the view
        View::display('dashboard', $data);
    }

    // --- API endpoints JSON usados pelo frontend dashboard.js ---
    public function getGeneralStats()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $stats = $this->statsModel->getGeneralStats();

        // Converter para o formato esperado pelo frontend
        $formattedStats = [
            'total_livros' => [
                'valor' => number_format($stats['total_books']),
                'descricao' => 'Disponíveis no acervo',
                'icon' => '📖',
                'color' => '#3b82f6'
            ],
            'livros_emprestados' => [
                'valor' => number_format($stats['borrowed_books']),
                'descricao' => 'Atualmente emprestados',
                'icon' => '📚',
                'color' => '#f59e0b'
            ],
            'usuarios_ativos' => [
                'valor' => number_format($stats['total_users']),
                'descricao' => 'Cadastrados no sistema',
                'icon' => '👥',
                'color' => '#10b981'
            ],
            'solicitacoes_pendentes' => [
                'valor' => number_format($stats['pending_requests']),
                'descricao' => 'Aguardando aprovação',
                'icon' => '📅',
                'color' => '#6366f1'
            ]
        ];

        echo json_encode(['stats' => $formattedStats], JSON_UNESCAPED_UNICODE);
    }

    public function getBorrowsByMonth()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $data = $this->statsModel->getBorrowsByMonth();
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function getTopBooks()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $books = $this->statsModel->getTopBooks();
        echo json_encode(['books' => $books], JSON_UNESCAPED_UNICODE);
    }

    public function getBooksByCategory()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $categories = $this->statsModel->getBooksByCategory();
        echo json_encode(['categories' => $categories], JSON_UNESCAPED_UNICODE);
    }

    public function getRecentActivities()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $activities = $this->statsModel->getRecentActivities();
        echo json_encode(['activities' => $activities], JSON_UNESCAPED_UNICODE);
    }

    public function getUserProfileStats()
    {
        $this->requireAuth();

        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $stats = $this->statsModel->getUserProfileStats((int)$userId);
        echo json_encode(['stats' => $stats], JSON_UNESCAPED_UNICODE);
    }

    public function getFallbackStatsData()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $stats = $this->statsModel->getFallbackStatsData();

        // Converter para o formato esperado pelo frontend
        $formattedStats = [
            'total_livros' => [
                'valor' => number_format($stats['total_books']),
                'descricao' => 'Disponíveis no acervo',
                'icon' => '📖',
                'color' => '#3b82f6'
            ],
            'livros_emprestados' => [
                'valor' => number_format($stats['borrowed_books']),
                'descricao' => 'Atualmente emprestados',
                'icon' => '📚',
                'color' => '#f59e0b'
            ],
            'usuarios_ativos' => [
                'valor' => number_format($stats['total_users']),
                'descricao' => 'Cadastrados no sistema',
                'icon' => '👥',
                'color' => '#10b981'
            ],
            'solicitacoes_pendentes' => [
                'valor' => number_format($stats['pending_requests']),
                'descricao' => 'Aguardando aprovação',
                'icon' => '📅',
                'color' => '#6366f1'
            ]
        ];

        echo json_encode(['stats' => $formattedStats], JSON_UNESCAPED_UNICODE);
    }



    /**
     * Aprovar uma solicitação de empréstimo via API do serviço de books
     */
    public function approveBorrow($requestId)
    {
        $this->requireRole('admin');
        
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

        // Chamar API do serviço de books
        $result = $this->callBooksServiceAPI("/api/approve/{$requestId}", 'POST', [
            'admin_user_id' => $adminUserId
        ]);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Rejeitar uma solicitação de empréstimo via API do serviço de books
     */
    public function rejectRequest($requestId)
    {
        $this->requireRole('admin');
        
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

        // Chamar API do serviço de books
        $result = $this->callBooksServiceAPI("/api/reject/{$requestId}", 'POST', [
            'admin_user_id' => $adminUserId
        ]);

        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 400);
        if (isset($result['status'])) {
            unset($result['status']);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obter solicitações pendentes
     */
    public function getPendingRequests()
    {
        $this->requireRole('admin');

        $limit = (int)($_GET['limit'] ?? 20);
        $limit = max(1, min(100, $limit)); // Limitar entre 1 e 100

        try {
            $requests = $this->statsModel->getPendingRequests($limit);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['requests' => $requests], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro ao buscar solicitações pendentes: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro interno do servidor'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Mostrar página de gerenciamento de livros
     */
    public function showBooksManagement()
    {
        $this->requireRole('admin');
        
        // Obter dados do usuário da sessão
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $_SESSION['user'] ?? null;
        
        $data = [
            'title' => 'Gerenciamento de Livros - Virtual Library',
            'isAdmin' => true,
            'user' => $user
        ];

        // Render the view
        View::display('books-management', $data);
    }

    /**
     * Obter todos os livros via API
     */
    public function getBooks()
    {
        $this->requireRole('admin');
        
        $result = $this->callBooksServiceAPI('/api/books', 'GET');
        
        if (!$result['success']) {
            http_response_code($result['status'] ?? 500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $result['message'] ?? 'Erro ao buscar livros'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obter um livro específico via API
     */
    public function getBook($id)
    {
        $this->requireRole('admin');
        
        $result = $this->callBooksServiceAPI("/api/books/{$id}", 'GET');
        
        if (!$result['success']) {
            http_response_code($result['status'] ?? 500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $result['message'] ?? 'Erro ao buscar livro'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Criar um novo livro via API
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
        
        $data = $this->readJsonBody();
        if (!$data) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $result = $this->callBooksServiceAPI('/api/books/create', 'POST', $data);
        
        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 500);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Atualizar um livro via API
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
        
        $data = $this->readJsonBody();
        if (!$data) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $result = $this->callBooksServiceAPI("/api/books/{$id}/update", 'POST', $data);
        
        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 500);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Deletar um livro via API
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
        
        $result = $this->callBooksServiceAPI("/api/books/{$id}/delete", 'POST');
        
        $statusCode = $result['success'] ? 200 : ($result['status'] ?? 500);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ler dados JSON do corpo da requisição
     */
    private function readJsonBody()
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Chamar API do serviço de books
     */
    private function callBooksServiceAPI($endpoint, $method = 'GET', $data = null)
    {
        $booksServiceUrl = $_ENV['BOOKS_SERVICE_URL'] ?? 'http://books-service';
        $url = rtrim($booksServiceUrl, '/') . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Service-Auth: ' . ($_ENV['SERVICE_AUTH_TOKEN'] ?? 'default-token')
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Erro ao chamar API do serviço de books: " . $error);
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erro de comunicação com o serviço de livros'
            ];
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erro ao decodificar resposta da API: " . json_last_error_msg());
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Resposta inválida do serviço de livros'
            ];
        }

        return $decodedResponse ?: [
            'success' => false,
            'status' => $httpCode,
            'message' => 'Resposta vazia do serviço de livros'
        ];
    }
}
