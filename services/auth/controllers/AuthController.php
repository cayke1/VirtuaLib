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
        view::display('/partials/header', ['title' => 'Perfil']);
        View::display('profile', ['user' => $user]);
        view::display('/partials/footer');
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
            header('Location: /login');
            exit;
        }
    }

}
