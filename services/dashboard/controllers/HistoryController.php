<?php

require_once __DIR__ . '/../../utils/AuthGuard.php';
require_once __DIR__ . '/../../utils/View.php';

class HistoryController
{
    use AuthGuard;

    private $statsModel;

    public function __construct()
    {
        $this->statsModel = new StatsModel();
        View::setBasePath(__DIR__ . '/../views/');
    }

    public function showHistory()
    {
        $this->requireRole('admin');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;

        $history = $this->statsModel->getHistory(100);

        $data = [
            'title' => 'Histórico - Virtual Library',
            'history' => $history,
            'currentUser' => $user
        ];
        View::display('/components/sidebar');
        View::display('history', $data);
    }

    public function getHistory()
    {
        $this->requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');
        $history = $this->statsModel->getHistory(100);
        echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
    }
}