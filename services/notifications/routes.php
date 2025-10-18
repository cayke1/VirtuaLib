<?php
/**
 * Rotas do serviço Notifications
 */

class NotificationsRouter {
    private $routes = [];
    
    public function __construct() {
        $this->defineRoutes();
    }
    
    private function defineRoutes() {
        $this->routes = [
            // Rotas de view
            '/' => ['NotificationsController', 'listNotifications'],
            '/unread' => ['NotificationsController', 'listUnreadNotifications'],
            
            // Rotas de API
            '/api/notifications' => ['NotificationsController', 'listForUser'],
            '/api/notifications/unread-count' => ['NotificationsController', 'unreadCount'],
            '/api/notifications/{id}/read' => ['NotificationsController', 'markAsRead'],
            '/api/notifications/{id}/delete' => ['NotificationsController', 'delete'],
            '/api/notifications/mark-all-read' => ['NotificationsController', 'markAllRead'],
            '/api/notifications/create' => ['NotificationsController', 'createNotification'],
            '/api/notifications/event' => ['NotificationsController', 'processEvent'],
        ];
    }
    
    public function run() {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        $uri = strtok($uri, '?');
        
        foreach ($this->routes as $route => $handler) {
            if ($this->matchRoute($route, $uri)) {
                $this->executeHandler($handler, $uri, $route);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function matchRoute($route, $uri) {
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        return preg_match('#^' . $routePattern . '$#', $uri);
    }
    
    private function executeHandler($handler, $uri, $route) {
        list($controller, $method) = $handler;
        
        $params = $this->extractParams($route, $uri);
        
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                $controllerInstance->$method($params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function extractParams($route, $uri) {
        $params = [];
        $routeParts = explode('/', trim($route, '/'));
        $uriParts = explode('/', trim($uri, '/'));
        
        for ($i = 0; $i < count($routeParts); $i++) {
            if (isset($routeParts[$i]) && preg_match('/\{([^}]+)\}/', $routeParts[$i], $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $uriParts[$i] ?? null;
            }
        }
        
        return $params;
    }
    
    private function notFound() {
        http_response_code(404);
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found']);
        } else {
            echo "<h1>404 - Notifications Service - Route not found</h1>";
        }
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
