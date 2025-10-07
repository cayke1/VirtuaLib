<?php

class StatsController
{
    use AuthGuard;

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getGeneralStats()
    {
        $this->requireAuth();
        
        try {
            $bookModel = new BookModel();
            $userModel = new UserModel();
            $borrowModel = new BorrowModel();

            $totalBooks = $bookModel->getTotalBooks();
            $borrowedBooks = $borrowModel->getActiveBorrowsCount();
            $activeUsers = $userModel->getActiveUsersCount();
            $todayBorrows = $borrowModel->getTodayBorrowsCount();

            $stats = [
                'total_livros' => [
                    'valor' => number_format($totalBooks),
                    'descricao' => 'Disponíveis no acervo',
                    'icon' => '📖',
                    'color' => '#3b82f6'
                ],
                'livros_emprestados' => [
                    'valor' => number_format($borrowedBooks),
                    'descricao' => 'Atualmente emprestados',
                    'icon' => '📚',
                    'color' => '#f59e0b'
                ],
                'usuarios_ativos' => [
                    'valor' => number_format($activeUsers),
                    'descricao' => 'Nos últimos 30 dias',
                    'icon' => '👥',
                    'color' => '#10b981'
                ],
                'emprestimos_hoje' => [
                    'valor' => number_format($todayBorrows),
                    'descricao' => 'Empréstimos realizados hoje',
                    'icon' => '📅',
                    'color' => '#6366f1'
                ]
            ];

            $this->json(['stats' => $stats]);
        } catch (Exception $e) {
            error_log("Error in getGeneralStats: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar estatísticas'], 500);
        }
    }

    public function getBorrowsByMonth()
    {
        $this->requireAuth();
        
        try {
            $borrowModel = new BorrowModel();
            $data = $borrowModel->getBorrowsByMonth();

            $this->json(['data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getBorrowsByMonth: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar dados de empréstimos'], 500);
        }
    }

    public function getTopBooks()
    {
        $this->requireAuth();
        
        try {
            $borrowModel = new BorrowModel();
            $topBooks = $borrowModel->getTopBorrowedBooks(5);

            $this->json(['books' => $topBooks]);
        } catch (Exception $e) {
            error_log("Error in getTopBooks: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar livros mais emprestados'], 500);
        }
    }

    public function getBooksByCategory()
    {
        $this->requireAuth();
        
        try {
            $bookModel = new BookModel();
            $categories = $bookModel->getBooksByCategory();

            $this->json(['categories' => $categories]);
        } catch (Exception $e) {
            error_log("Error in getBooksByCategory: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar distribuição por categoria'], 500);
        }
    }

    public function getRecentActivities()
    {
        $this->requireAuth();
        
        try {
            $borrowModel = new BorrowModel();
            $activities = $borrowModel->getRecentActivities(10);

            $this->json(['activities' => $activities]);
        } catch (Exception $e) {
            error_log("Error in getRecentActivities: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar atividades recentes'], 500);
        }
    }

    public function getUserProfileStats()
    {
        $this->requireAuth();
        
        try {
            // Debug: verificar se a sessão está correta
            if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
                $this->json(['error' => 'Usuário não encontrado na sessão'], 401);
                return;
            }
            
            $userId = $_SESSION['user']['id'];
            $borrowModel = new BorrowModel();
            $userModel = new UserModel();

            // Buscar estatísticas do usuário
            $activeBorrows = $borrowModel->getActiveBorrowsCountByUser($userId);
            $totalBorrows = $borrowModel->getTotalBorrowsCountByUser($userId);
            $userInfo = $userModel->getUserById($userId);

            // Calcular dias como membro
            $memberSinceDays = 0;
            $createdAt = null;
            if ($userInfo && isset($userInfo['created_at'])) {
                $createdAt = $userInfo['created_at'];
                $memberSinceDays = floor((time() - strtotime($createdAt)) / (60 * 60 * 24));
            }

            $stats = [
                'active_borrows' => $activeBorrows,
                'total_borrows' => $totalBorrows,
                'member_since_days' => $memberSinceDays,
                'created_at' => $createdAt
            ];

            $this->json(['stats' => $stats]);
        } catch (Exception $e) {
            error_log("Error in getUserProfileStats: " . $e->getMessage());
            $this->json(['error' => 'Erro ao carregar estatísticas do perfil'], 500);
        }
    }
}