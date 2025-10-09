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
        
        // Set the base path for views
        View::setBasePath(__DIR__ . '/../views/');
    }
    
    /**
     * Renderizar view de autenticação
     */
    public function showLogin() {
        $user = $this->userModel->getUserData();
        
        $data = [
            'title' => 'Auth Service - Virtual Library',
            'user' => $user,
            'error' => $_SESSION['auth_error'] ?? null,
            'success' => $_SESSION['auth_success'] ?? null
        ];
        
        // Clear session messages
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
        
        // Render the view
        View::display('auth', $data);
    }
}
