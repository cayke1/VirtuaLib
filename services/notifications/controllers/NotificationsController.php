<?php

require_once __DIR__ . '/../../utils/AuthGuard.php';
require_once __DIR__ . '/../services/NotificationService.php';

class NotificationsController
{
    use AuthGuard;

    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function listForUser() {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $notifications = $this->notificationModel->getByUserId($userId);
        $this->json(['notifications' => $notifications]);
    }
    
    public function unreadCount() {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $count = $this->notificationModel->countUnreadByUserId($userId);
        $this->json(['unread' => $count]);
    }
    
    public function markAllRead() {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $ok = $this->notificationModel->markAllReadByUserId($userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to mark all as read'], 400);
        }
        $this->json(['message' => 'All marked as read']);
    }
    
    public function markAsRead($id) {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $notificationId = (int)$id;
        
        if (!$notificationId) {
            return $this->json(['error' => 'Invalid notification ID'], 400);
        }
        
        $ok = $this->notificationModel->markAsRead($notificationId, $userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to mark as read'], 400);
        }
        $this->json(['message' => 'Marked as read']);
    }
    
    public function delete($id) {
        $this->requireAuth();
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $notificationId = (int)$id;
        
        if (!$notificationId) {
            return $this->json(['error' => 'Invalid notification ID'], 400);
        }

        $ok = $this->notificationModel->delete($notificationId, $userId);
        if (!$ok) {
            return $this->json(['error' => 'Unable to delete notification'], 400);
        }
        $this->json(['message' => 'Deleted']);
    }

    public function createNotification() {
        $this->requireRole('admin');
        
        $input = $this->readJsonBody();
        
        if (!$input || !isset($input['user_id']) || !isset($input['title']) || !isset($input['message'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }
        
        $userId = (int)$input['user_id'];
        $title = $input['title'];
        $message = $input['message'];
        $data = $input['data'] ?? null;
        
        $notificationId = $this->notificationModel->create($userId, $title, $message, $data);
        
        if ($notificationId) {
            $this->json(['message' => 'Notification created', 'id' => $notificationId]);
        } else {
            $this->json(['error' => 'Failed to create notification'], 500);
        }
    }
    
    public function processEvent() {
        $input = $this->readJsonBody();
        
        if (!$input || !isset($input['type'])) {
            return $this->json(['error' => 'Missing event type'], 400);
        }
        
        $notificationService = new NotificationService();
        $result = $notificationService->processEvent($input);
        
        if ($result) {
            $this->json(['message' => 'Event processed successfully']);
        } else {
            $this->json(['error' => 'Failed to process event'], 500);
        }
    }

    private function readJsonBody() {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
    
}
