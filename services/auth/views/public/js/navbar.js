
    function redirecionarParaPorta(novaPorta, caminho = '/') {
    const { protocol, hostname, search, hash } = window.location;
    const url = `${protocol}//${hostname}:${novaPorta}${caminho}${search}${hash}`;
    window.location.href = url;
  }
  document.getElementById('rota').addEventListener('click', function (e) {
    e.preventDefault();
    redirecionarParaPorta(80, '/books');
  });

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenuMobile = document.getElementById('nav-menu-mobile');

    if (hamburger && navMenuMobile) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            hamburger.classList.toggle('active');
            navMenuMobile.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenuMobile.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenuMobile.classList.remove('active');
            }
        });
    }
});
