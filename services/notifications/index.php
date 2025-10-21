<?php
/**
 * Notifications Service - Ponto de entrada
 */

// Carregar dependências primeiro (antes do autoloader)
require_once __DIR__ . "/../utils/LoadEnv.php";
require_once __DIR__ . "/../utils/AuthGuard.php";
require_once __DIR__ . "/../utils/Database.php";
require_once __DIR__ . '/../utils/EventDispatcher.php';

// Inicializar o serviço de notificações para registrar os listeners
require_once __DIR__ . '/services/NotificationService.php';

// Configurar autoload
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/controllers/$class.php",
        __DIR__ . "/models/$class.php",
        __DIR__ . "/services/$class.php",
        __DIR__ . "/../utils/$class.php"
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once __DIR__ . "/routes.php";

// Carregar configurações
LoadEnv::loadAll(__DIR__."/../../.env");

// Inicializar roteador
$router = new NotificationsRouter();
$router->run();
