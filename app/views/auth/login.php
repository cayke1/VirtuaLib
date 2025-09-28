<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtualLib - Login</title>
    <link rel="stylesheet" href="./public/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <!-- Cabeçalho -->
            <div class="header">
                <div class="logo-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h1 class="title">VirtualLib</h1>
                <p class="subtitle">Acesse sua biblioteca virtual</p>
            </div>



            <!-- Formulário de Login -->
            <form class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">E-mail do usuário</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="seu@email.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>

            <!-- Links -->
            <div class="links">
                <p class="register-link">Não possui uma conta? <a href="/register" class="link">Cadastre-se</a></p>
            </div>

            <!-- Rodapé -->
            <div class="footer">
                <p class="copyright">© 2024 VirtualLib. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>