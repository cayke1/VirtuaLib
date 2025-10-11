<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VirtuaLib - Login</title>
  <link rel="stylesheet" href="/public/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
  <?php include __DIR__ . '/public/css/auth.css'; ?>
</style>
<body>
  <div class="container">
    <div class="auth-card">
      <div class="header">
        <div class="logo-icon">
          <i class="fas fa-book"></i>
        </div>
        <h1 class="title">VirtuaLib</h1>
        <p class="subtitle">Acesse sua biblioteca virtual</p>
      </div>

      <!-- Mensagem de erro -->
      <div id="error-message" class="error-message" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span id="error-text">Credenciais inválidas. Verifique seu e-mail e senha.</span>
      </div>

      <form id="login-form" class="auth-form" novalidate>
        <div class="form-group">
          <label for="email" class="form-label">E-mail</label>
          <input type="text" id="email" name="email" class="form-input" placeholder="seu@email.com" required>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Senha</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="auth-button">
          <i class="fas fa-arrow-right"></i>
          Entrar
        </button>
      </form>

      <div class="links">
        <a href="./register" class="link">Ainda não tem conta? Cadastre-se</a>
      </div>

      <div class="footer">
        <p class="copyright">© 2024 VirtuaLib. Todos os direitos reservados.</p>
      </div>
    </div>
  </div>

  <script >
    <?php include __DIR__ . '/public/js/auth.js'; ?>
  </script>
  <script>
    
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('login-form');
      const errorMessage = document.getElementById('error-message');
      const errorText = document.getElementById('error-text');

      // Verifica se já está logado
      if (window.AuthService?.isAuthenticated) {
        redirecionarParaPorta(8080, '/books');
        return;
      }

      function showError(text) {
        errorText.textContent = text;
        errorMessage.style.display = 'flex';
      }

      function hideError() {
        errorMessage.style.display = 'none';
      }

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email || !password) {
          showError('Por favor, preencha todos os campos.');
          return;
        }

        // Validação básica de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          showError('Por favor, insira um email válido.');
          return;
        }

        try {
          const result = await window.AuthService.login(email, password);
          
          if (result.success) {
            redirecionarParaPorta(8080, '/books');
          } else {
            showError(result.error || 'Erro ao fazer login. Tente novamente.');
          }
        } catch (err) {
          console.error('Erro no login:', err);
          showError('Erro de conexão. Tente novamente.');
        }
      });
    });
  </script>
</body>

</html>