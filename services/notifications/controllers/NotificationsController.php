<?php
/**
 * Notifications Controller - Serviço de Notificações
 */

// Include the View utility
require_once __DIR__ . '/../../utils/View.php';

class NotificationsController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new NotificationModel();
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
    /**
     * Renderizar view de notificações
     */
    public function listNotifications() {
        $notifications = $this->notificationModel->getUserNotifications();
        
        $data = [
            'title' => 'Notifications Service - Virtual Library',
            'notifications' => $notifications
        ];
        
        // Render the view
        View::display('notifications', $data);
    }
}
