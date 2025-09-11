<?php
class Core
{
    private $routes;
    
    public function __construct($routes)
    {
        $this->setRoutes($routes);
    }
    
    public function run()
    {
        $url = $this->getCurrentUrl();
        $routerFound = false;
        
        // Debug: descomente para ver a URL sendo processada
        // echo "URL atual: '$url'<br>";
        
        foreach ($this->getRoutes() as $path => $controllerAndAction) {
            $pattern = $this->buildRoutePattern($path);
            
            // Debug: descomente para ver os padrões
            // echo "Testando rota '$path' com padrão '$pattern'<br>";
            
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches); // Remove a URL completa dos matches
                $routerFound = true;
                
                $this->executeController($controllerAndAction, $matches);
                break; // Importante: sair do loop após encontrar a rota
            }
        }
        
        if (!$routerFound) {
            $this->handleNotFound();
        }
    }
    
    /**
     * Obtém a URL atual de forma mais robusta
     */
    private function getCurrentUrl()
    {
        $url = $_SERVER['REQUEST_URI'];
        
        // if (isset($_GET['url']) && !empty($_GET['url'])) {
        //     $url = '/' . trim($_GET['url'], '/');
        // }
        
        return $url;
    }
    
    /**
     * Constrói o padrão regex para a rota
     */
    private function buildRoutePattern($path)
    {
        // Escapa caracteres especiais do regex
        $pattern = preg_quote($path, '#');
        
        // Substitui placeholders por grupos de captura
        $pattern = preg_replace('/\\\{id\\\}/', '([\w-]+)', $pattern);
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '([\w-]+)', $pattern); // Para outros placeholders
        
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Executa o controller e action
     */
    private function executeController($controllerAndAction, $matches = [])
    {
        try {
            [$currentController, $action] = explode('@', $controllerAndAction);
            
            $controllerPath = __DIR__ . "/../controllers/$currentController.php";
            
            if (!file_exists($controllerPath)) {
                throw new Exception("Controller file not found: $controllerPath");
            }
            
            require_once $controllerPath;
            
            if (!class_exists($currentController)) {
                throw new Exception("Controller class not found: $currentController");
            }
            
            $controller = new $currentController();
            
            if (!method_exists($controller, $action)) {
                throw new Exception("Action method not found: $action in $currentController");
            }
            
            // Passa os parâmetros da URL para o método
            call_user_func_array([$controller, $action], $matches);
            
        } catch (Exception $e) {
            error_log("Router Error: " . $e->getMessage());
            $this->handleNotFound();
        }
    }
    
    /**
     * Manipula páginas não encontradas
     */
    private function handleNotFound()
    {
        $notFoundPath = __DIR__ . "/../controllers/NotFoundController.php";
        
        if (file_exists($notFoundPath)) {
            require_once $notFoundPath;
            $controller = new NotFoundController();
            
            if (method_exists($controller, 'index')) {
                $controller->index();
            } else {
                $this->defaultNotFound();
            }
        } else {
            $this->defaultNotFound();
        }
    }
    
    /**
     * Resposta padrão para 404
     */
    private function defaultNotFound()
    {
        http_response_code(404);
        echo "404 - Página não encontrada";
    }
    
    /**
     * Adiciona uma rota dinamicamente
     */
    public function addRoute($path, $controllerAndAction)
    {
        $this->routes[$path] = $controllerAndAction;
    }
    
    /**
     * Remove uma rota
     */
    public function removeRoute($path)
    {
        if (isset($this->routes[$path])) {
            unset($this->routes[$path]);
        }
    }
    
    /**
     * Verifica se uma rota existe
     */
    public function routeExists($path)
    {
        return isset($this->routes[$path]);
    }
    
    /**
     * Obtém todas as rotas
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Define as rotas
     */
    public function setRoutes($routes)
    {
        if (!is_array($routes)) {
            throw new InvalidArgumentException("Routes must be an array");
        }
        
        $this->routes = $routes;
    }
}