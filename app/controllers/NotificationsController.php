<?php

class NotificationsController
{
    use AuthGuard;

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function listForUser()
    {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $model = new NotificationModel();
        $notifications = $model->getByUserId($userId);

        $this->json(['notifications' => $notifications]);
    }

    public function create()
    {
        $this->requireRole('admin');

        $raw = file_get_contents('php://input');
        $payload = $raw ? json_decode($raw, true) : null;
        if (!is_array($payload) || empty($payload['user_id']) || empty($payload['title']) || empty($payload['message'])) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        $model = new NotificationModel();
        $data = $payload['data'] ?? null;
        $id = $model->create((int)$payload['user_id'], $payload['title'], $payload['message'], $data);

        if (!$id) {
            return $this->json(['error' => 'Failed to create notification'], 500);
        }

        $this->json(['id' => $id, 'message' => 'Notification created'], 201);
    }

    public function createBulk()
    {
        // Only allow admins
        $this->requireRole('admin');

        $raw = file_get_contents('php://input');
        $payload = $raw ? json_decode($raw, true) : null;
        if (!is_array($payload) || empty($payload)) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        $model = new NotificationModel();
        $ok = $model->createBulk($payload);
        if (!$ok) {
            return $this->json(['error' => 'Failed to create notifications'], 500);
        }
        $this->json(['message' => 'Notifications created']);
    }

    public function unreadCount()
    {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $model = new NotificationModel();
        $count = $model->countUnreadByUserId($userId);
        $this->json(['unread' => $count]);
    }

    public function markAllRead()
    {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $model = new NotificationModel();
        $ok = $model->markAllReadByUserId($userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to mark all as read'], 400);
        }
        $this->json(['message' => 'All marked as read']);
    }

    public function markAsRead($id)
    {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $model = new NotificationModel();
        $ok = $model->markAsRead((int)$id, $userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to mark as read'], 400);
        }
        $this->json(['message' => 'Marked as read']);
    }

    public function delete($id)
    {
        $this->requireAuth('user');
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $model = new NotificationModel();
        $ok = $model->delete((int)$id, $userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to delete notification'], 400);
        }
        $this->json(['message' => 'Deleted']);
    }
}
