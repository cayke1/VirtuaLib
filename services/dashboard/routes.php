<?php

class DashboardRouter
{
    private $routes = [];

    public function __construct()
    {
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $this->routes = [
            '/' => ['DashboardController', 'showDashboard'],
            '/historico' => ['HistoryController', 'showHistory'],
            #'/stats' => ['DashboardController', 'showStats'],
            #'/analytics' => ['DashboardController', 'showAnalytics'],
            
            '/api/stats/general' => ['DashboardController', 'getGeneralStats'],
            '/api/stats/borrows-by-month' => ['DashboardController', 'getBorrowsByMonth'],
            '/api/stats/top-books' => ['DashboardController', 'getTopBooks'],
            '/api/stats/books-by-category' => ['DashboardController', 'getBooksByCategory'],
            '/api/stats/recent-activities' => ['DashboardController', 'getRecentActivities'],
            '/api/stats/user-profile' => ['DashboardController', 'getUserProfileStats'],
            '/api/stats/history' => ['HistoryController', 'getHistory'],
            '/api/stats/fallback' => ['DashboardController', 'getFallbackStatsData'],
            '/api/pending-requests' => ['DashboardController', 'getPendingRequests'],
            
            '/api/approve/{requestId}' => ['DashboardController', 'approveBorrow'],
            '/api/reject/{requestId}' => ['DashboardController', 'rejectRequest'],
            
            '/livros' => ['DashboardController', 'showBooksManagement'],
            '/api/books' => ['DashboardController', 'getBooks'],
            '/api/books/create' => ['DashboardController', 'createBook'],
            '/api/books/{id}' => ['DashboardController', 'getBook'],
            '/api/books/{id}/update' => ['DashboardController', 'updateBook'],
            '/api/books/{id}/delete' => ['DashboardController', 'deleteBook'],

            '/api/overdue/update' => ['OverdueController', 'updateOverdueStatus'],
            '/api/overdue/user/{userId}' => ['OverdueController', 'getOverdueByUser'],
            '/api/overdue/all' => ['OverdueController', 'getAllOverdue'],
        ];
    }

    public function run()
    {
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

    private function matchRoute($route, $uri)
    {
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
                $params = $this->extractParams($handler, $uri);
                $controllerInstance->$method(...$params);
                return;
            }
        }

        $this->notFound();
    }

    private function extractParams($handler, $uri)
    {
        foreach ($this->routes as $route => $routeHandler) {
            if ($routeHandler === $handler) {
                $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
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
            echo "<h1>404 - Dashboard Service - Route not found</h1>";
        }
    }

    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}