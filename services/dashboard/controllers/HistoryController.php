<?php

require_once __DIR__ . '/../../utils/AuthGuard.php';
require_once __DIR__ . '/../../utils/View.php';

class HistoryController {
    private $statsModel;
    use AuthGuard;

    public function __construct() {
        $this->statsModel = new StatsModel();
        View::setBasePath(__DIR__ . '/../views/');
    }

    public function showHistory() {
        $this->requireRole('admin');
        
        // Obter dados do usuário da sessão
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $_SESSION['user'] ?? null;
                
        $history = $this->statsModel->getHistory(100);
                
        $data = [
            'title' => 'Histórico - Virtual Library',
            'history' => $history,
            'currentUser' => $user
        ];
        View::display('history', $data);

    }
        
    // API endpoint
    public function getHistory() {
        $this->requireRole('admin');
                
        header('Content-Type: application/json; charset=utf-8');
        $history = $this->statsModel->getHistory(100);
        echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
    }
}