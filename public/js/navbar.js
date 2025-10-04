// Controle do menu mobile
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenuMobile = document.getElementById('nav-menu-mobile');

    // Toggle do menu mobile
    if (hamburger && navMenuMobile) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            hamburger.classList.toggle('active');
            navMenuMobile.classList.toggle('active');
        });

        // Fechar menu mobile ao clicar fora
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenuMobile.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenuMobile.classList.remove('active');
            }
        });
    }
});
