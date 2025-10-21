<?php

/**
 * Rotas do serviço Books
 */

class BooksRouter
{
    private $routes = [];

    public function __construct()
    {
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $this->routes = [
            // Rotas de view
            '/' => ['BookController', 'listBooks'],
            '/books' => ['BookController', 'listBooks'],
            '/details/{id}' => ['BookController', 'viewBookDetails'],

            // Rotas de API
            '/api/search' => ['BookController', 'searchBooks'],
            '/api/request/{id}' => ['BookController', 'requestBook'],
            '/api/return/{id}' => ['BookController', 'returnBook'],
            '/api/approve/{requestId}' => ['BookController', 'approveBorrow'],
            '/api/reject/{requestId}' => ['BookController', 'rejectRequest'],
            '/api/pending-requests' => ['BookController', 'getPendingRequests'],
            '/api/create' => ['BookController', 'createBook'],
        ];
    }

    public function run()
    {
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

    private function matchRoute($route, $uri)
    {
        // Converter rota com parâmetros para regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $uri);
    }

    private function executeHandler($handler, $uri)
    {
        list($controller, $method) = $handler;

        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                // Extrair parâmetros da URI se necessário
                $params = $this->extractParams($handler, $uri);
                $controllerInstance->$method(...$params);
                return;
            }
        }

        $this->notFound();
    }

    private function extractParams($handler, $uri)
    {
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

    private function notFound()
    {
        http_response_code(404);
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found']);
        } else {
            echo "<h1>404 - Books Service - Route not found</h1>";
        }
    }

    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
