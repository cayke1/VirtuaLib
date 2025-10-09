<?php
/**
 * API Gateway para roteamento entre serviços SOA
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Carregar configurações
require_once __DIR__."/utils/LoadEnv.php";
LoadEnv::loadAll(__DIR__."/../.env");

class ApiGateway {
    private $services = [];
    
    public function __construct() {
        $this->registerServices();
    }
    
    private function registerServices() {
        $this->services = [
            'auth' => [
                'prefix' => '/auth',
                'port' => 8081,
                'path' => __DIR__ . '/auth'
            ],
            'books' => [
                'prefix' => '/books', 
                'port' => 8082,
                'path' => __DIR__ . '/books'
            ],
            'notifications' => [
                'prefix' => '/notifications',
                'port' => 8083, 
                'path' => __DIR__ . '/notifications'
            ],
            'dashboard' => [
                'prefix' => '/dashboard',
                'port' => 8084,
                'path' => __DIR__ . '/dashboard'
            ]
        ];
    }
    
    public function route() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Determinar qual serviço deve processar a requisição
        $service = $this->determineService($requestUri);
        
        if (!$service) {
            // Fallback para o sistema original
            return $this->fallbackToOriginal();
        }
        
        // Redirecionar para o serviço apropriado
        $this->forwardToService($service, $requestUri);
    }
    
    private function determineService($uri) {
        // Primeiro, verificar se a URI começa com algum prefixo de serviço
        foreach ($this->services as $serviceName => $config) {
            if (strpos($uri, $config['prefix']) === 0) {
                return $serviceName;
            }
        }
        
        // Se for a raiz (/) ou não corresponder a nenhum serviço, redirecionar para books
        if ($uri === '/' || strpos($uri, '/') === 0) {
            return 'books';
        }
        
        return null;
    }
    
    private function forwardToService($serviceName, $originalUri) {
        $service = $this->services[$serviceName];
        $servicePath = $service['path'];
        
        // Modificar a URI para o contexto do serviço
        $serviceUri = $originalUri;
        
        // Se a URI começa com o prefixo do serviço, removê-lo
        if (strpos($originalUri, $service['prefix']) === 0) {
            $serviceUri = substr($originalUri, strlen($service['prefix']));
        }
        
        // Se a URI ficou vazia, definir como raiz
        if (empty($serviceUri)) {
            $serviceUri = '/';
        }
        
        // Salvar URI original para contexto
        $_SERVER['ORIGINAL_REQUEST_URI'] = $originalUri;
        $_SERVER['REQUEST_URI'] = $serviceUri;
        
        // Incluir o serviço
        if (file_exists($servicePath . '/index.php')) {
            include $servicePath . '/index.php';
        } else {
            $this->sendError("Service $serviceName not available", 503);
        }
    }
    
    private function fallbackToOriginal() {
        // Redirecionar para o sistema original
        require_once __DIR__."/../index.php";
    }
    
    private function sendError($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code
        ]);
        exit;
    }
}

// Inicializar o gateway
$gateway = new ApiGateway();
$gateway->route();
