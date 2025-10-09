<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($title ?? 'Dashboard Service - Virtual Library') ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .service-info { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #666; 
            font-size: 14px; 
        }
        h1 { 
            color: #333; 
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin: 20px 0; 
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-number { 
            font-size: 2.5em; 
            font-weight: bold; 
            color: #007bff; 
            margin-bottom: 10px; 
        }
        .stat-label { 
            color: #666; 
            font-size: 14px; 
            margin-bottom: 5px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-description { 
            color: #999; 
            font-size: 12px; 
        }
        .model-info { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .model-info h3 {
            margin-top: 0;
            color: #333;
        }
        .model-info ul {
            text-align: left;
            color: #555;
        }
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-placeholder {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
            text-align: center;
            color: #666;
        }
        .chart-placeholder h3 {
            color: #333;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="service-info">📊 Dashboard Service</div>
        
        <h1>Dashboard e Estatísticas</h1>
        
        <div class="model-info">
            <h3>Métodos disponíveis no StatsModel:</h3>
            <ul>
                <li>getGeneralStats() - Retorna estatísticas gerais do sistema</li>
            </ul>
        </div>
        
        <?php if (empty($stats)): ?>
            <div class="empty-state">
                <h3>📊 Nenhuma estatística disponível</h3>
                <p>Os dados do sistema ainda não foram coletados.</p>
            </div>
        <?php else: ?>
            <div class="stats-grid">
                <?php foreach ($stats as $key => $value): ?>
                    <?php 
                    $label = ucwords(str_replace('_', ' ', $key));
                    $icon = '';
                    
                    // Add icons based on stat type
                    switch (strtolower($key)) {
                        case 'total_users':
                        case 'users':
                            $icon = '👥';
                            break;
                        case 'total_books':
                        case 'books':
                            $icon = '📚';
                            break;
                        case 'total_borrows':
                        case 'borrows':
                        case 'active_borrows':
                            $icon = '📖';
                            break;
                        case 'total_notifications':
                        case 'notifications':
                            $icon = '🔔';
                            break;
                        default:
                            $icon = '📈';
                    }
                    ?>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?= $icon ?> <?= is_numeric($value) ? number_format($value) : View::escape($value) ?>
                        </div>
                        <div class="stat-label"><?= View::escape($label) ?></div>
                        <div class="stat-description">Estatística do sistema</div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="chart-placeholder">
                <h3>📊 Gráficos e Analytics</h3>
                <p>Os gráficos detalhados serão implementados em futuras versões.</p>
                <p>Por enquanto, você pode visualizar as estatísticas básicas acima.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
