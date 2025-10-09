<?php
/**
 * Rotas do serviço Auth
 */

class AuthRouter {
    private $routes = [];
    
    public function __construct() {
        $this->defineRoutes();
    }
    
    private function defineRoutes() {
        $this->routes = [
            // Rotas de view
            '/login' => ['AuthController', 'showLogin'],
            '/register' => ['AuthController', 'showRegister'],
            '/profile' => ['AuthController', 'showProfile'],
            
            // Rotas de API
            '/api/register' => ['AuthController', 'register'],
            '/api/login' => ['AuthController', 'login'],
            '/api/logout' => ['AuthController', 'logout'],
            '/api/me' => ['AuthController', 'me'],
            '/api/update-profile' => ['AuthController', 'updateProfile'],
        ];
    }
    
    public function run() {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remover query string
        $uri = strtok($uri, '?');
        
        // Encontrar rota correspondente
        foreach ($this->routes as $route => $handler) {
            if ($this->matchRoute($route, $uri)) {
                $this->executeHandler($handler, $uri);
                return;
            }
        }
        
        // Rota não encontrada
        $this->notFound();
    }
    
    private function matchRoute($route, $uri) {
        // Implementação simples de matching
        return $route === $uri;
    }
    
    private function executeHandler($handler, $uri) {
        list($controller, $method) = $handler;
        
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                $controllerInstance->$method();
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function notFound() {
        http_response_code(404);
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found']);
        } else {
            echo "<h1>404 - Auth Service - Route not found</h1>";
        }
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
