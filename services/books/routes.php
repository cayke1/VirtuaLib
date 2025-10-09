<?php
/**
 * Rotas do serviço Books
 */

class BooksRouter {
    private $routes = [];
    
    public function __construct() {
        $this->defineRoutes();
    }
    
    private function defineRoutes() {
        $this->routes = [
            // Rotas de view
            '/' => ['BookController', 'listBooks'],
            '/search' => ['BookController', 'searchBooks'],
            '/details/{id}' => ['BookController', 'viewBookDetails'],
            '/dashboard' => ['BookController', 'viewDashboard'],
            '/history' => ['BookController', 'viewHistory'],
            
            // Rotas de API
            '/api/books' => ['BookController', 'listBooksApi'],
            '/api/books/search' => ['BookController', 'searchBooksApi'],
            '/api/books/{id}' => ['BookController', 'getBookDetails'],
            '/api/books/{id}/request' => ['BookController', 'requestBook'],
            '/api/books/{id}/return' => ['BookController', 'returnBook'],
            '/api/books/{id}/approve' => ['BookController', 'approveBorrow'],
            '/api/books/{id}/reject' => ['BookController', 'rejectRequest'],
            '/api/books/create' => ['BookController', 'createBook'],
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
                $this->executeHandler($handler, $uri, $route);
                return;
            }
        }
        
        // Rota não encontrada
        $this->notFound();
    }
    
    private function matchRoute($route, $uri) {
        // Implementação simples de matching com parâmetros
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        return preg_match('#^' . $routePattern . '$#', $uri);
    }
    
    private function executeHandler($handler, $uri, $route) {
        list($controller, $method) = $handler;
        
        // Extrair parâmetros da URL
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
            echo "<h1>404 - Books Service - Route not found</h1>";
        }
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
