<?php

class StatsModel extends Database
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
    }

    public function getGeneralStats()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_users FROM Users");
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

            $stmt = $this->pdo->query("SELECT COUNT(*) as total_books FROM Books");
            $totalBooks = $stmt->fetch(PDO::FETCH_ASSOC)['total_books'];

            $stmt = $this->pdo->query("SELECT COUNT(*) as available_books FROM Books WHERE available = 1");
            $availableBooks = $stmt->fetch(PDO::FETCH_ASSOC)['available_books'];

            $stmt = $this->pdo->query("SELECT COUNT(*) as borrowed_books FROM Borrows WHERE status = 'borrowed'");
            $borrowedBooks = $stmt->fetch(PDO::FETCH_ASSOC)['borrowed_books'];

            $stmt = $this->pdo->query("SELECT COUNT(*) as total_borrows FROM Borrows");
            $totalBorrows = $stmt->fetch(PDO::FETCH_ASSOC)['total_borrows'];

            $stmt = $this->pdo->query("SELECT COUNT(*) as late_borrows FROM Borrows WHERE status = 'late' OR (status = 'borrowed' AND due_date < CURDATE())");
            $lateBorrows = $stmt->fetch(PDO::FETCH_ASSOC)['late_borrows'];

            return [
                'total_users' => (int)$totalUsers,
                'total_books' => (int)$totalBooks,
                'available_books' => (int)$availableBooks,
                'borrowed_books' => (int)$borrowedBooks,
                'total_borrows' => (int)$totalBorrows,
                'late_borrows' => (int)$lateBorrows,
                'borrow_rate' => $totalBooks > 0 ? round(($borrowedBooks / $totalBooks) * 100, 2) : 0
            ];
        } catch (PDOException $e) {
            error_log("Database error in getGeneralStats: " . $e->getMessage());
            return [];
        }
    }


    public function getBooksByGenre()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    genre, 
                    COUNT(*) as total_books,
                    SUM(CASE WHEN available = 1 THEN 1 ELSE 0 END) as available_books,
                    SUM(CASE WHEN available = 0 THEN 1 ELSE 0 END) as borrowed_books
                FROM Books 
                GROUP BY genre 
                ORDER BY total_books DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBooksByGenre: " . $e->getMessage());
            return [];
        }
    }

    public function getBooksByYear()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    year, 
                    COUNT(*) as total_books,
                    SUM(CASE WHEN available = 1 THEN 1 ELSE 0 END) as available_books
                FROM Books 
                GROUP BY year 
                ORDER BY year DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBooksByYear: " . $e->getMessage());
            return [];
        }
    }

    public function getBorrowsByPeriod($period = 'month')
    {
        try {
            $dateFormat = match($period) {
                'day' => '%Y-%m-%d',
                'week' => '%Y-%u',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m'
            };

            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(borrowed_at, :date_format) as period,
                    COUNT(*) as total_borrows,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_borrows,
                    SUM(CASE WHEN status = 'late' OR (status = 'borrowed' AND due_date < CURDATE()) THEN 1 ELSE 0 END) as late_borrows
                FROM Borrows 
                WHERE borrowed_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY period 
                ORDER BY period DESC
                LIMIT 12
            ");
            
            $stmt->bindValue(':date_format', $dateFormat, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBorrowsByPeriod: " . $e->getMessage());
            return [];
        }
    }

    public function getMostBorrowedBooks($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.id,
                    b.title,
                    b.author,
                    b.genre,
                    COUNT(bor.id) as borrow_count,
                    b.available
                FROM Books b
                LEFT JOIN Borrows bor ON b.id = bor.book_id
                GROUP BY b.id, b.title, b.author, b.genre, b.available
                ORDER BY borrow_count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getMostBorrowedBooks: " . $e->getMessage());
            return [];
        }
    }

    public function getMostActiveUsers($limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id,
                    u.name,
                    u.email,
                    COUNT(b.id) as total_borrows,
                    SUM(CASE WHEN b.status = 'returned' THEN 1 ELSE 0 END) as completed_borrows,
                    SUM(CASE WHEN b.status = 'late' OR (b.status = 'borrowed' AND b.due_date < CURDATE()) THEN 1 ELSE 0 END) as late_borrows
                FROM Users u
                LEFT JOIN Borrows b ON u.id = b.user_id
                GROUP BY u.id, u.name, u.email
                HAVING total_borrows > 0
                ORDER BY total_borrows DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getMostActiveUsers: " . $e->getMessage());
            return [];
        }
    }

    public function getLateStats()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.name,
                    u.email,
                    b.title as book_title,
                    b.author as book_author,
                    bor.due_date,
                    DATEDIFF(CURDATE(), bor.due_date) as days_late
                FROM Borrows bor
                INNER JOIN Users u ON u.id = bor.user_id
                INNER JOIN Books b ON b.id = bor.book_id
                WHERE (bor.status = 'late' OR (bor.status = 'borrowed' AND bor.due_date < CURDATE()))
                ORDER BY days_late DESC
            ");
            $lateBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_late_borrows,
                    AVG(DATEDIFF(CURDATE(), due_date)) as avg_days_late,
                    MAX(DATEDIFF(CURDATE(), due_date)) as max_days_late
                FROM Borrows 
                WHERE (status = 'late' OR (status = 'borrowed' AND due_date < CURDATE()))
            ");
            $lateStats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'late_borrows' => $lateBorrows,
                'stats' => $lateStats
            ];
        } catch (PDOException $e) {
            error_log("Database error in getLateStats: " . $e->getMessage());
            return ['late_borrows' => [], 'stats' => []];
        }
    }
    
    public function getPerformanceStats()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    AVG(DATEDIFF(returned_at, borrowed_at)) as avg_borrow_duration,
                    MIN(DATEDIFF(returned_at, borrowed_at)) as min_borrow_duration,
                    MAX(DATEDIFF(returned_at, borrowed_at)) as max_borrow_duration,
                    COUNT(DISTINCT DATE(borrowed_at)) as active_days,
                    COUNT(*) as total_completed_borrows
                FROM Borrows 
                WHERE status = 'returned' AND returned_at IS NOT NULL
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Database error in getPerformanceStats: " . $e->getMessage());
            return [];
        }
    }
}
