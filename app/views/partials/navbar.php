<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtuaLib - Biblioteca Digital</title>
    <link rel="stylesheet" href="/app/views/public/css/navbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            
            <div class="nav-brand">
                <div class="logo">
                    <i class="fas fa-book"></i>
                </div>
                <span class="brand-name">VirtuaLib</span>
            </div>

            
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Buscar por título ou autor..." class="search-input">
                    <div id="results"></div>
                </div>
            </div>
        </div>
    </nav>
    <script src="/public/js/searchBook.js"></script>