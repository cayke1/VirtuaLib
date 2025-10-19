<?php
/**
 * Dashboard Controller - Serviço de Dashboard e Estatísticas
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class DashboardController {
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
        header('Content-Type: application/json; charset=utf-8');
        $stats = $this->statsModel->getGeneralStats();
        echo json_encode(['stats' => $stats], JSON_UNESCAPED_UNICODE);
    }

    public function getBorrowsByMonth() {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->statsModel->getBorrowsByMonth();
        echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function getTopBooks() {
        header('Content-Type: application/json; charset=utf-8');
        $books = $this->statsModel->getTopBooks();
        echo json_encode(['books' => $books], JSON_UNESCAPED_UNICODE);
    }

    public function getBooksByCategory() {
        header('Content-Type: application/json; charset=utf-8');
        $categories = $this->statsModel->getBooksByCategory();
        echo json_encode(['categories' => $categories], JSON_UNESCAPED_UNICODE);
    }

    public function getRecentActivities() {
        header('Content-Type: application/json; charset=utf-8');
        $activities = $this->statsModel->getRecentActivities();
        echo json_encode(['activities' => $activities], JSON_UNESCAPED_UNICODE);
    }

    public function getUserProfileStats() {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $stats = $this->statsModel->getUserProfileStats((int)$userId);
        echo json_encode($stats, JSON_UNESCAPED_UNICODE);
    }

    public function getFallbackStatsData() {
        header('Content-Type: application/json; charset=utf-8');
        $stats = $this->statsModel->getFallbackStatsData();
        echo json_encode(['stats' => $stats], JSON_UNESCAPED_UNICODE);
    }   


    public function showHistory() {
        $history = $this->statsModel->getHistory(100);
        $isApi = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
        if ($isApi) {
            echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
            header('Content-Type: application/json; charset=utf-8');
            return;
        }

        $data = [
            'title' => 'Histórico - Virtual Library',
            'history' => $history
        ];
        View::display('history', $data);
        
    }

    // função endpoint api
    public function getHistory() {
        header('Content-Type: application/json; charset=utf-8');
        $history = $this->statsModel->getHistory(100);
        echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
    }
}
