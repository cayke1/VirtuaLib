<?php
/**
 * Dashboard Controller - Serviço de Dashboard e Estatísticas
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';
require_once __DIR__ . '/../../utils/AuthGuard.php';

class DashboardController {
    #use AuthGuard;
    
    private $statsModel;
    
    public function __construct() {
        $this->statsModel = new StatsModel();
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
    /**
     * Renderizar view de dashboard
     */
    public function showDashboard() {
        #$this->requireAuth();
        $stats = $this->statsModel->getGeneralStats();
        $pendingRequests = $this->statsModel->getPendingRequests(20);

        // Determinar se o usuário atual é admin (se houver sessão)
        $isAdmin = false;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
            $isAdmin = true;
        }

        $data = [
            'title' => 'Dashboard Service - Virtual Library',
            'stats' => $stats,
            'pendingRequests' => $pendingRequests,
            'isAdmin' => $isAdmin
        ];

        // Render the view
        View::display('dashboard', $data);
    }

    // --- API endpoints JSON usados pelo frontend dashboard.js ---
    public function getGeneralStats() {
#        #
        
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
                'descricao' => 'Nos últimos 30 dias',
                'icon' => '👥',
                'color' => '#10b981'
            ],
            'emprestimos_hoje' => [
                'valor' => number_format($stats['pending_requests']),
                'descricao' => 'Empréstimos realizados hoje',
                'icon' => '📅',
                'color' => '#6366f1'
            ]
        ];
        
        echo json_encode(['stats' => $formattedStats], JSON_UNESCAPED_UNICODE);
    }

    public function getBorrowsByMonth() {
#        $this->requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->statsModel->getBorrowsByMonth();
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function getTopBooks() {
#        $this->requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        $books = $this->statsModel->getTopBooks();
        echo json_encode(['books' => $books], JSON_UNESCAPED_UNICODE);
    }

    public function getBooksByCategory() {
#        $this->requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        $categories = $this->statsModel->getBooksByCategory();
        echo json_encode(['categories' => $categories], JSON_UNESCAPED_UNICODE);
    }

    public function getRecentActivities() {
#        $this->requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        $activities = $this->statsModel->getRecentActivities();
        echo json_encode(['activities' => $activities], JSON_UNESCAPED_UNICODE);
    }

    public function getUserProfileStats() {
#        $this->requireAuth();
        
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

    public function getFallbackStatsData() {
#        $this->requireAuth();
        
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
                'descricao' => 'Nos últimos 30 dias',
                'icon' => '👥',
                'color' => '#10b981'
            ],
            'emprestimos_hoje' => [
                'valor' => number_format($stats['pending_requests']),
                'descricao' => 'Empréstimos realizados hoje',
                'icon' => '📅',
                'color' => '#6366f1'
            ]
        ];
        
        echo json_encode(['stats' => $formattedStats], JSON_UNESCAPED_UNICODE);
    }   


    public function showHistory() {
#        $this->requireAuth();
        
        $history = $this->statsModel->getHistory(100);
        
        $data = [
            'title' => 'Histórico - Virtual Library',
            'history' => $history
        ];
        View::display('history', $data);
    }

    // função endpoint api
    public function getHistory() {
#        $this->requireAuth();
        
        header('Content-Type: application/json; charset=utf-8');
        $history = $this->statsModel->getHistory(100);
        echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
    }
}
