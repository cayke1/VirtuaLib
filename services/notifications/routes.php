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
                $this->executeHandler($handler, $uri);
                return;
            }
        }
        $this->notFound();
    }
    
    private function matchRoute($route, $uri) {
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        return preg_match('#^' . $routePattern . '$#', $uri);
    }
    
    private function executeHandler($handler, $uri) {
        list($controller, $method) = $handler;
        
        $params = $this->extractParams($handler, $uri);
        
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                $controllerInstance->$method(...$params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function extractParams($handler, $uri) {
        // Encontrar a rota correspondente para extrair parâmetros
        foreach ($this->routes as $route => $routeHandler) {
            if ($routeHandler === $handler) {
                // Converter rota com parâmetros para regex
                $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';
                
                if (preg_match($pattern, $uri, $matches)) {
                    // Remover o primeiro match (string completa) e retornar apenas os parâmetros
                    array_shift($matches);
                    return $matches;
                }
            }
        }
        
        return [];
    }
    
    private function notFound() {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
    }
}
