<?php

class AuthController extends RenderView
{
    use AuthGuard;

    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
        include __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister()
    {
        include __DIR__ . '/../views/auth/register.php';
    }

    public function showProfile()
    {
        $this->requireAuth();

        $this->loadView('partials/header', ['title' => 'Meu Perfil']);
        $this->render('profile', ['user' => $_SESSION['user']]);
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
}
