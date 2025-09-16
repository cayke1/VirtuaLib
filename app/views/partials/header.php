<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  
  <style>
    .main-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar {
      padding: 0;
    }

    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 70px;
    }

    .nav-logo {
      flex: 1;
    }

    .logo-link {
      color: white;
      text-decoration: none;
      font-size: 1.5rem;
      font-weight: 700;
      transition: opacity 0.3s ease;
    }

    .logo-link:hover {
      opacity: 0.8;
    }

    .nav-menu {
      display: flex;
      gap: 30px;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .nav-link {
      color: white;
      text-decoration: none;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-size: 1rem;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }

    .nav-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
    }

    .bar {
      width: 25px;
      height: 3px;
      background: white;
      margin: 3px 0;
      transition: 0.3s;
      border-radius: 2px;
    }

    @media (max-width: 768px) {
      .nav-menu {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 100%;
        text-align: center;
        transition: 0.3s;
        box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
        padding: 20px 0;
        gap: 20px;
      }

      .nav-menu.active {
        left: 0;
      }

      .nav-toggle {
        display: flex;
      }

      .nav-toggle.active .bar:nth-child(2) {
        opacity: 0;
      }

      .nav-toggle.active .bar:nth-child(1) {
        transform: translateY(8px) rotate(45deg);
      }

      .nav-toggle.active .bar:nth-child(3) {
        transform: translateY(-8px) rotate(-45deg);
      }
    }
  </style>
</head>
<body>
  <?php require_once 'navbar.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const navToggle = document.querySelector('.nav-toggle');
      const navMenu = document.querySelector('.nav-menu');

      if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
          navToggle.classList.toggle('active');
          navMenu.classList.toggle('active');
        });

        // Fechar menu ao clicar em um link
        document.querySelectorAll('.nav-link').forEach(link => {
          link.addEventListener('click', function() {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
          });
        });
      }
    });
  </script>
</body>
</html>