<?php

require_once __DIR__ . '/../../utils/AuthGuard.php';
require_once __DIR__ . '/../../auth/models/BorrowModel.php';

class OverdueController
{
    use AuthGuard;

    private $borrowModel;

    public function __construct()
    {
        $this->borrowModel = new BorrowModel();
    }

    public function updateOverdueStatus()
    {
        $this->requireRole('admin');
        
        header('Content-Type: application/json; charset=utf-8');

        try {
            $result = $this->borrowModel->updateOverdueStatus();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log('Error updating overdue status: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'status' => 500,
                'message' => 'Erro interno do servidor'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getOverdueByUser($userId)
    {
        $this->requireRole('admin');
        
        header('Content-Type: application/json; charset=utf-8');

        try {
            $overdueBorrows = $this->borrowModel->getOverdueBorrowsByUser((int)$userId);
            echo json_encode([
                'success' => true,
                'overdue_borrows' => $overdueBorrows
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log('Error getting overdue borrows by user: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'status' => 500,
                'message' => 'Erro interno do servidor'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAllOverdue()
    {
        $this->requireRole('admin');
        
        header('Content-Type: application/json; charset=utf-8');

        try {
            $overdueBorrows = $this->borrowModel->getAllOverdueBorrows();
            echo json_encode([
                'success' => true,
                'overdue_borrows' => $overdueBorrows
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log('Error getting all overdue borrows: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'status' => 500,
                'message' => 'Erro interno do servidor'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
