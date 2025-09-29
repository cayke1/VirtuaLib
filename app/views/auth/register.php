<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtuaLib - Cadastro</title>
    <link rel="stylesheet" href="/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
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
                <span id="error-text">Erro no cadastro. Verifique os dados informados.</span>
            </div>

            <form id="register-form" class="auth-form" novalidate>
                <div class="form-group">
                    <label for="fullname" class="form-label">Nome completo</label>
                    <input type="text" id="fullname" name="fullname" class="form-input" placeholder="Seu nome completo" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Nome de usuário</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="usuario123" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="confirm-password" class="form-label">Confirmar senha</label>
                    <input type="password" id="confirm-password" name="confirm-password" class="form-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="auth-button">
                    <i class="fas fa-arrow-right"></i>
                    Criar conta
                </button>
            </form>

            <div class="links">
                <a href="./login" class="forgot-link">Já possui uma conta? Faça login</a>
            </div>

            <div class="footer">
                <p class="copyright">© 2024 VirtuaLib. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>

<script>
(() => {
  const form = document.getElementById('register-form');
  const errorMessage = document.getElementById('error-message');
  const errorText = document.getElementById('error-text');

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

    const name = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm-password').value;

    if (!name || !email || !username || !password || !confirm) {
      showError('Preencha todos os campos obrigatórios.');
      return;
    }
    if (password !== confirm) {
      showError('As senhas não coincidem. Verifique e tente novamente.');
      return;
    }

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name, email, username, password })
      });

      const data = await res.json().catch(() => ({}));

      if (res.status === 201) {
        if (data.token) localStorage.setItem('auth_token', data.token);
        if (data.user) localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = '/';
      } else if (res.status === 409) {
        showError('E-mail já está em uso. Tente outro e-mail.');
      } else {
        showError(data.error || 'Erro interno do servidor. Tente novamente.');
      }
    } catch (err) {
      showError('Erro de conexão. Verifique sua internet e tente novamente.');
    }
  });
})();
</script>
</body>
</html>