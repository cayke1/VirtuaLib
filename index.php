<?php 
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

require_once __DIR__."/app/router/routes.php";
require_once __DIR__."/app/core/Core.php";
require_once __DIR__."/app/utils/LoadEnv.php";
require_once __DIR__."/app/utils/AuthGuard.php";


LoadEnv::loadAll(__DIR__."/.env");

spl_autoload_register(function ($file) {
    if(file_exists(__DIR__."/app/utils/$file.php")) {
        require_once __DIR__."/app/utils/$file.php";
    } else if (file_exists(__DIR__."/app/models/$file.php")) {
        require_once __DIR__."/app/models/$file.php";
    }
});

require_once __DIR__ . "/app/services/NotificationService.php";

$isAdmin = isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

$activeRoutes = $isAdmin ? $adminRoutes : $userRoutes;

$core = new Core($activeRoutes);
$core->run();