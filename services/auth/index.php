<?php
/**
 * Auth Service - Ponto de entrada
 */

// Configurar autoload
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/controllers/$class.php",
        __DIR__ . "/models/$class.php",
        __DIR__ . "/../utils/$class.php"
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Carregar dependências
require_once __DIR__ . "/../utils/LoadEnv.php";
require_once __DIR__ . "/../utils/AuthGuard.php";
require_once __DIR__ . "/../utils/Database.php";
require_once __DIR__ . '/../utils/EventDispatcher.php';
require_once __DIR__ . "/routes.php";

// Carregar configurações
LoadEnv::loadAll(__DIR__."/../../.env");

// Inicializar roteador
$router = new AuthRouter();
$router->run();
