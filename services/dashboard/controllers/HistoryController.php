<?php

#include __DIR__ . '/../../utils/AuthGuard.php';
require_once __DIR__ . '/../../utils/View.php';

class HistoryController {
    private $statsModel;

    public function __construct() {
        $this->statsModel = new StatsModel();
        View::setBasePath(__DIR__ . '/../views/');
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
        
    // API endpoint
    public function getHistory() {
        #        $this->requireAuth();
                
        header('Content-Type: application/json; charset=utf-8');
        $history = $this->statsModel->getHistory(100);
        echo json_encode(['history' => $history], JSON_UNESCAPED_UNICODE);
    }
}