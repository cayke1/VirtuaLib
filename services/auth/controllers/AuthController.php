<?php
/**
 * Auth Controller - Serviço de Autenticação
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
     private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function register()
    {
        $input = $this->readJsonBody();
        if (!$input || empty($input['name']) || empty($input['email']) || empty($input['password'])) {
            return $this->json(['error' => 'Invalid input'], 400);
        }

        $existing = $this->userModel->findByEmail($input['email']);
        if ($existing) {
            return $this->json(['error' => 'Email already in use'], 409);
        }

        $userId = $this->userModel->createUser($input['name'], $input['email'], $input['password']);
        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $input['name'],
            'email' => $input['email'],
            'role' => 'user',
        ];

        return $this->json(['message' => 'Registered', 'user' => $_SESSION['user']], 201);
    }

    public function login()
    {
        
        $input = $this->readJsonBody();
        if (!$input || empty($input['email']) || empty($input['password'])) {
            return $this->json(['error' => 'Invalid credentials'], 400);
        }

        [$ok, $user] = $this->userModel->verifyPassword($input['email'], $input['password']);
        if (!$ok) {
            return $this->json(['error' => 'Email or password incorrect'], 401);
        }

        $_SESSION['user'] = $user;
        return $this->json(['message' => 'Logged in', 'user' => $user]);
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        return $this->json(['message' => 'Logged out']);
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

    public function me()
    {   
        
        if (empty($_SESSION['user'])) {
            return $this->json(['user' => null], 200);
        }
        return $this->json(['user' => $_SESSION['user']]);
    }

    public function showLogin()
    {
        View::display('login');
    }

    public function showRegister()
    {
        View::display('register');
    }

    public function showProfile()
    {
        $this->requireAuth();
        $user = $_SESSION['user'];
        view::display('partials/header', ['title' => 'Perfil']);
        View::display('profile', ['user' => $user]);
        view::display('partials/footer');
    }

    private function readJsonBody()
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /auth/login');
            exit;
        }
    }

}
