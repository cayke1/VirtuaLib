    <nav class="navbar">
    <div class="nav-container">
        <!-- Logo e nome da marca -->
        <a href="/" class="nav-brand">
            <div class="logo">
                <div class="logo-icon">
                    <div class="square square-1"></div>
                    <div class="square square-2"></div>
                </div>
            </div>
            <span class="brand-name">VirtualLib</span>
        </a>

        <!-- Barra de pesquisa -->
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Buscar por título ou autor..." class="search-input" id="search-input">
                <div class="search-results" id="search-results">
                    <div class="search-loading" id="search-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Buscando...</span>
                    </div>
                    <div class="search-items" id="search-items"></div>
                </div>
            </div>
        </div>

        <!-- Menu de navegação desktop -->
        <div class="nav-menu-desktop">
            <a href="/profile" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Perfil</span>
            </a>
            <a href="/historico" class="nav-link">
                <i class="fas fa-history"></i>
                <span>Histórico</span>
            </a>
            <a href="/login" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>

        <!-- Botão hambúrguer -->
        <div class="hamburger" id="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>

    <!-- Menu mobile -->
    <div class="nav-menu-mobile" id="nav-menu-mobile">
        <div class="search-container-mobile">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Buscar por título ou autor..." class="search-input" id="search-input-mobile">
                <div class="search-results" id="search-results-mobile">
                    <div class="search-loading" id="search-loading-mobile">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Buscando...</span>
                    </div>
                    <div class="search-items" id="search-items-mobile"></div>
                </div>
            </div>
        </div>
        
        <!-- Menu de navegação mobile -->
        <div class="nav-menu-mobile-links">
            <a href="/profile" class="mobile-nav-link">
                <i class="fas fa-user"></i>
                <span>Perfil</span>
            </a>
            <a href="/historico" class="mobile-nav-link">
                <i class="fas fa-history"></i>
                <span>Histórico</span>
            </a>
            <a href="/login" class="mobile-nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>
</nav>
<style>
    .nav-link {
        color: #1e293b;
    }
    .mobile-nav-link {
        color: #1e293b !important;
    }
</style>

<script src="/public/js/searchBook.js"></script>
<script src="/public/js/navbar.js"></script>