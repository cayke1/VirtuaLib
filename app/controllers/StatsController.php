<?php

class StatsController
{
    use AuthGuard;

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getGeneralStats()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            $stats = $statsModel->getGeneralStats();
            
            if (empty($stats)) {
                return $this->json(['error' => 'Não foi possível obter as estatísticas'], 500);
            }
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getGeneralStats: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getBooksByGenre()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            $stats = $statsModel->getBooksByGenre();
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getBooksByGenre: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getBooksByYear()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            $stats = $statsModel->getBooksByYear();
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getBooksByYear: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getBorrowsByPeriod()
    {
        $this->requireRole('admin');
        
        try {
            $period = $_GET['period'] ?? 'month';
            $validPeriods = ['day', 'week', 'month', 'year'];
            
            if (!in_array($period, $validPeriods)) {
                return $this->json(['error' => 'Período inválido. Use: day, week, month ou year'], 400);
            }
            
            $statsModel = new StatsModel();
            $stats = $statsModel->getBorrowsByPeriod($period);
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'period' => $period,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getBorrowsByPeriod: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getMostBorrowedBooks()
    {
        $this->requireRole('admin');
        
        try {
            $limit = (int)($_GET['limit'] ?? 10);
            $limit = max(1, min($limit, 50));
            
            $statsModel = new StatsModel();
            $stats = $statsModel->getMostBorrowedBooks($limit);
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'limit' => $limit,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getMostBorrowedBooks: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getMostActiveUsers()
    {
        $this->requireRole('admin');
        
        try {
            $limit = (int)($_GET['limit'] ?? 10);
            $limit = max(1, min($limit, 50));
            
            $statsModel = new StatsModel();
            $stats = $statsModel->getMostActiveUsers($limit);
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'limit' => $limit,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getMostActiveUsers: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getLateStats()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            $stats = $statsModel->getLateStats();
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getLateStats: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getPerformanceStats()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            $stats = $statsModel->getPerformanceStats();
            
            return $this->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getPerformanceStats: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getDashboardStats()
    {
        $this->requireRole('admin');
        
        try {
            $statsModel = new StatsModel();
            
            $dashboard = [
                'general' => $statsModel->getGeneralStats(),
                'books_by_genre' => $statsModel->getBooksByGenre(),
                'most_borrowed_books' => $statsModel->getMostBorrowedBooks(5),
                'most_active_users' => $statsModel->getMostActiveUsers(5),
                'late_stats' => $statsModel->getLateStats(),
                'performance' => $statsModel->getPerformanceStats()
            ];
            
            return $this->json([
                'success' => true,
                'data' => $dashboard,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            return $this->json(['error' => 'Erro interno do servidor'], 500);
        }
    }
}
