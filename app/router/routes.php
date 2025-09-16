<?php

class Router {
    private $routes = [];
    
    public function __construct() {
        $this->setupRoutes();
    }
    
    private function setupRoutes() {
        $this->routes = [
            '/' => ['controller' => 'BookController', 'method' => 'index'],
            '/home' => ['controller' => 'BookController', 'method' => 'index'],
            '/book/(\d+)' => ['controller' => 'BookController', 'method' => 'detail']
        ];
    }
    
    public function handleRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover o base path se necessário
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath != '/') {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        foreach ($this->routes as $route => $config) {
            $pattern = '#^' . $route . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                $controllerName = $config['controller'];
                $methodName = $config['method'];
                
                // Verificar se a classe existe
                if (!class_exists($controllerName)) {
                    $this->showError("Controller $controllerName não encontrado");
                    return;
                }
                
                $controller = new $controllerName();
                
                // Passar o ID como parâmetro se existir
                $params = array_slice($matches, 1);
                
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $params);
                } else {
                    $this->showError("Método $methodName não existe no controller $controllerName");
                }
                return;
            }
        }
        
        // 404 - Route not found
        $this->show404();
    }
    
    private function show404() {
        http_response_code(404);
        if (class_exists('NotFoundController')) {
            $controller = new NotFoundController();
            $controller->index();
        } else {
            echo "404 - Página não encontrada";
        }
    }
    
    private function showError($message) {
        http_response_code(500);
        echo "Erro: " . htmlspecialchars($message);
    }
}
?>