<?php

class NotificationsRouter
{
    private $routes = [];

    public function __construct()
    {
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $this->routes = [
            '/api/notifications' => ['NotificationsController', 'listForUser'],
            '/api/notifications/unread-count' => ['NotificationsController', 'unreadCount'],
            '/api/notifications/{id}/read' => ['NotificationsController', 'markAsRead'],
            '/api/notifications/{id}/delete' => ['NotificationsController', 'delete'],
            '/api/notifications/mark-all-read' => ['NotificationsController', 'markAllRead'],
            '/api/notifications/create' => ['NotificationsController', 'createNotification'],
            '/api/notifications/event' => ['NotificationsController', 'processEvent'],
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
            echo "<h1>404 - Notifications Service - Route not found</h1>";
        }
    }

    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
