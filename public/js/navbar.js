// Controle do menu hambúrguer
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenuMobile = document.getElementById('nav-menu-mobile');
    
    if (hamburger && navMenuMobile) {
        // Toggle do menu mobile
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenuMobile.classList.toggle('active');
        });
        
        // Fechar menu ao clicar fora
        document.addEventListener('click', function(event) {
            const isClickInsideNav = navMenuMobile.contains(event.target);
            const isClickOnHamburger = hamburger.contains(event.target);
            
            if (!isClickInsideNav && !isClickOnHamburger) {
                hamburger.classList.remove('active');
                navMenuMobile.classList.remove('active');
            }
        });
        
        // Fechar menu ao redimensionar a tela para desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                hamburger.classList.remove('active');
                navMenuMobile.classList.remove('active');
            }
        });
    }
});
