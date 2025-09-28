<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtualLib - Cadastro</title>
    <link rel="stylesheet" href="./public/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="register-card">
            <!-- Cabeçalho -->
            <div class="header">
                <div class="logo-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h1 class="title">VirtualLib</h1>
                <p class="subtitle">Crie sua conta na biblioteca virtual</p>
            </div>

            <!-- Formulário de Cadastro -->
            <form class="register-form">
                <div class="form-group">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        class="form-input" 
                        placeholder="Seu nome completo"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
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

                <div class="form-group">
                    <label for="confirm-password" class="form-label">Confirmar senha</label>
                    <input 
                        type="password" 
                        id="confirm-password" 
                        name="confirm-password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <button type="submit" class="register-button">
                    <i class="fas fa-user-plus"></i>
                    Criar conta
                </button>
            </form>

            <!-- Link para Login -->
            <div class="login-link">
                <p>Já possui uma conta? <a href="/login" class="link">Faça login</a></p>
            </div>

            <!-- Rodapé -->
            <div class="footer">
                <p class="copyright">© 2024 VirtualLib. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>