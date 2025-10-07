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
            <!-- Notificações -->
            <div class="notification-container">
                <button class="notification-btn" id="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notification-badge">0</span>
                </button>
                <div class="notification-dropdown" id="notification-dropdown">
                    <div class="notification-header">
                        <h3>Notificações</h3>
                        <button class="mark-all-read-btn" id="mark-all-read-btn">Marcar todas como lidas</button>
                    </div>
                    <div class="notification-list" id="notification-list">
                        <div class="notification-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                    </div>
                    <div class="notification-empty" id="notification-empty" style="display: none;">
                        <i class="fas fa-bell-slash"></i>
                        <span>Nenhuma notificação</span>
                    </div>
                </div>
            </div>

            <a href="/profile" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Perfil</span>
            </a>
            <a href="/historico" class="nav-link">
                <i class="fas fa-history"></i>
                <span>Histórico</span>
            </a>
            <a href="#" class="nav-link logout" onclick="window.AuthService.logout(); return false;">
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
            <!-- Notificações Mobile -->
            <div class="notification-container-mobile">
                <button class="mobile-nav-link notification-btn-mobile" id="notification-btn-mobile">
                    <i class="fas fa-bell"></i>
                    <span>Notificações</span>
                    <span class="notification-badge-mobile" id="notification-badge-mobile">0</span>
                </button>
                <div class="notification-dropdown-mobile" id="notification-dropdown-mobile">
                    <div class="notification-header-mobile">
                        <h3>Notificações</h3>
                        <button class="mark-all-read-btn-mobile" id="mark-all-read-btn-mobile">Marcar todas como lidas</button>
                    </div>
                    <div class="notification-list-mobile" id="notification-list-mobile">
                        <div class="notification-loading-mobile">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                    </div>
                    <div class="notification-empty-mobile" id="notification-empty-mobile" style="display: none;">
                        <i class="fas fa-bell-slash"></i>
                        <span>Nenhuma notificação</span>
                    </div>
                </div>
            </div>

            <a href="/profile" class="mobile-nav-link">
                <i class="fas fa-user"></i>
                <span>Perfil</span>
            </a>
            <a href="/historico" class="mobile-nav-link">
                <i class="fas fa-history"></i>
                <span>Histórico</span>
            </a>
            <a href="#" class="mobile-nav-link logout" onclick="window.AuthService.logout(); return false;">
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

