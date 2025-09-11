<?php 
require_once __DIR__."/app/router/routes.php";
require_once __DIR__."/app/core/Core.php";
require_once __DIR__."/app/utils/LoadEnv.php";


LoadEnv::loadAll(__DIR__."/.env");

spl_autoload_register(function ($file) {
    if(file_exists(__DIR__."/app/utils/$file.php")) {
        require_once __DIR__."/app/utils/$file.php";
    } else if (file_exists(__DIR__."/app/models/$file.php")) {
        require_once __DIR__."/app/models/$file.php";
    }
});

$core = new Core($routes);
$core->run();