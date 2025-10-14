<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/public/js/auth.js"></script>
    




    <title><?php echo $title; ?> - VirtuaLib</title>
    
    <style><?php
        include __DIR__ . "../../public/css/base.css";
        include __DIR__ . "../../public/css/navbar.css";
        include __DIR__ . "../../public/css/layout.css";        
        include __DIR__ . "../../public/css/profile.css";
        include __DIR__ . "../../public/css/notifications.css";
    ?>
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/navbar.php'; ?>
    <script>
        <?php 
            include __DIR__ . '../../public/js/auth.js';
            include __DIR__ . "../../public/js/navbar.js";
            include __DIR__ . "../../public/js/bookmark.js";
            include __DIR__ . "../../public/js/button.js";
            include __DIR__ . "../../public/js/notifications.js";
        ?>
    </script>
    