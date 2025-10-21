<?php
/**
 * Rotas do serviço Dashboard
 */

class DashboardRouter {
    private $routes = [];
    
    public function __construct() {
        $this->defineRoutes();
    }
    
    private function defineRoutes() {
        $this->routes = [
            // Rotas de view
            '/' => ['DashboardController', 'showDashboard'],
            '/historico' => ['HistoryController', 'showHistory'],
            #'/stats' => ['DashboardController', 'showStats'],
            #'/analytics' => ['DashboardController', 'showAnalytics'],
            
            // Rotas de API
            '/api/stats/general' => ['DashboardController', 'getGeneralStats'],
            '/api/stats/borrows-by-month' => ['DashboardController', 'getBorrowsByMonth'],
            '/api/stats/top-books' => ['DashboardController', 'getTopBooks'],
            '/api/stats/books-by-category' => ['DashboardController', 'getBooksByCategory'],
            '/api/stats/recent-activities' => ['DashboardController', 'getRecentActivities'],
            '/api/stats/user-profile' => ['DashboardController', 'getUserProfileStats'],
            '/api/stats/history' => ['HistoryController', 'getHistory'],
            '/api/stats/fallback' => ['DashboardController', 'getFallbackStatsData'],

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
            echo "<h1>404 - Dashboard Service - Route not found</h1>";
        }
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}