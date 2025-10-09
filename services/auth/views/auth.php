<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($title ?? 'Auth Service - Virtual Library') ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 400px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { 
            text-align: center; 
            color: #333; 
            margin-bottom: 30px; 
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            color: #555; 
        }
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
        }
        button:hover { 
            background: #0056b3; 
        }
        .error { 
            color: #dc3545; 
            margin-top: 10px; 
            text-align: center; 
        }
        .success { 
            color: #28a745; 
            margin-top: 10px; 
            text-align: center; 
        }
        .service-info { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #666; 
            font-size: 14px; 
        }
        .user-info { 
            background: #e9ecef; 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .model-info {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .model-info h3 {
            margin-top: 0;
            color: #333;
        }
        .model-info ul {
            text-align: left;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="service-info">🔐 Auth Service</div>
        
        <h1>Dados do Usuário</h1>
        
        <?php if (isset($user) && $user): ?>
            <div class="user-info">
                <p><strong>Nome:</strong> <?= View::escape($user['name']) ?></p>
                <p><strong>Email:</strong> <?= View::escape($user['email']) ?></p>
                <p><strong>Função:</strong> <?= View::escape($user['role']) ?></p>
                <p><strong>Status:</strong> <?= View::escape($user['status']) ?></p>
                <p><strong>Criado em:</strong> <?= View::formatDate($user['created_at']) ?></p>
            </div>
        <?php else: ?>
            <div class="user-info">
                <p>Nenhum usuário autenticado.</p>
            </div>
        <?php endif; ?>
        
        <div class="model-info">
            <h3>Métodos disponíveis no UserModel:</h3>
            <ul>
                <li>getUserData() - Retorna dados do usuário</li>
            </ul>
        </div>
        
        <?php if (isset($error) && $error): ?>
            <div class="error"><?= View::escape($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($success) && $success): ?>
            <div class="success"><?= View::escape($success) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
