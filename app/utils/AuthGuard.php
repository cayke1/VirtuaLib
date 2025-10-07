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
            $this->jsonError(401, 'Unauthorized');
        }
    }

    private function requireRole($role)
    {
        $this->requireAuth();
        $currentRole = $_SESSION['user']['role'] ?? 'user';
        if ($currentRole !== $role) {
            $this->jsonError(403, 'Forbidden');
        }
    }
}
