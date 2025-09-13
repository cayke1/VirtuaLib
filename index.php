<?php
// index.php - Arquivo principal

// Definir constantes de caminho
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Carregar autoloader básico
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . '/controllers/',
        APP_PATH . '/core/',
        APP_PATH . '/models/',
        APP_PATH . '/utils/',
        ROOT_PATH . '/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Carregar LoadEnv
try {
    require_once APP_PATH . '/utils/LoadEnv.php';
    LoadEnv::loadAll(ROOT_PATH . '/.env');
} catch (Exception $e) {
    error_log("Erro ao carregar .env: " . $e->getMessage());
    // Continuar mesmo sem .env para desenvolvimento
}

// Carregar Router
require_once APP_PATH . '/router/routes.php';

// Iniciar a aplicação
$router = new Router();
$router->handleRequest();
?>