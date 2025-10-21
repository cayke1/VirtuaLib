<?php

trait AuthGuard
{
    private function jsonError($status, $message)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user'])) {
            // Se for uma requisição de API, retorna JSON
            if ($this->isApiRequest()) {
                $this->jsonError(401, 'Unauthorized');
            } else {
                // Se for uma view, redireciona para login
                header('Location: /auth/login');
                exit;
            }
        }
    }

    private function requireRole($role)
    {
        $this->requireAuth();
        $currentRole = $_SESSION['user']['role'] ?? 'user';
        if ($currentRole !== $role) {
            // Se for uma requisição de API, retorna JSON
            if ($this->isApiRequest()) {
                $this->jsonError(403, 'Forbidden');
            } else {
                // Se for uma view, redireciona para home
                header('Location: /');
                exit;
            }
        }
    }

    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
