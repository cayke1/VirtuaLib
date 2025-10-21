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
                'path' => __DIR__ . '/auth',
                'patterns' => ['/auth', '/auth/*']
            ],
            'books' => [
                'prefix' => '/books', 
                'port' => 8082,
                'path' => __DIR__ . '/books',
                'patterns' => ['/books', '/books/*', '/api/search*', '/api/request/*', '/api/return/*', '/api/create*', '/api/pending-requests*', '/']
            ],
            'notifications' => [
                'prefix' => '/notifications',
                'port' => 8083, 
                'path' => __DIR__ . '/notifications',
                'patterns' => ['/notifications/*', '/api/notifications*']
            ],
            'dashboard' => [
                'prefix' => '/dashboard',
                'port' => 8084,
                'path' => __DIR__ . '/dashboard',
                'patterns' => ['/dashboard', '/dashboard/*', '/api/approve/*', '/api/reject/*', '/api/stats/*']
            ]
        ];
    }
    
    public function route() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Determinar qual serviço deve processar a requisição
        $service = $this->determineService($requestUri);
        
        if (!$service) {
            // Serviço não encontrado
            $this->sendError("Service not found", 404);
        }
        
        // Redirecionar para o serviço apropriado
        $this->forwardToService($service, $requestUri);
    }
    
    private function determineService($uri) {
        // Remover query string para comparação
        $cleanUri = strtok($uri, '?');
        
        // Verificar cada serviço e seus padrões
        foreach ($this->services as $serviceName => $config) {
            foreach ($config['patterns'] as $pattern) {
                if ($this->matchesPattern($pattern, $cleanUri)) {
                    return $serviceName;
                }
            }
        }
        
        return null;
    }
    
    private function matchesPattern($pattern, $uri) {
        // Converter padrão wildcard para regex
        $regex = str_replace('*', '.*', $pattern);
        $regex = preg_quote($regex, '/');
        $regex = str_replace('\\.\\*', '.*', $regex);
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $uri);
    }
    
    private function forwardToService($serviceName, $originalUri) {
        $service = $this->services[$serviceName];
        $servicePath = $service['path'];
        
        // Determinar a URI do serviço baseada no padrão
        $serviceUri = $this->transformUriForService($originalUri, $service);
        
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
    
    private function transformUriForService($uri, $service) {
        $cleanUri = strtok($uri, '?');
        
        // Se a URI começa com o prefixo do serviço, removê-lo
        if (strpos($cleanUri, $service['prefix']) === 0) {
            $transformed = substr($cleanUri, strlen($service['prefix']));
            return empty($transformed) ? '/' : $transformed;
        }
        
        // Para outras rotas (como /api/*), manter a URI original
        return $cleanUri;
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
