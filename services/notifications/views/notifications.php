<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($title ?? 'Notifications Service - Virtual Library') ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 800px; 
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
        .notifications-list { 
            margin-top: 20px; 
        }
        .notification-item { 
            background: white; 
            padding: 20px; 
            margin-bottom: 10px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .notification-item:hover {
            transform: translateY(-1px);
        }
        .notification-unread { 
            border-left: 4px solid #007bff; 
        }
        .notification-read { 
            opacity: 0.7; 
        }
        .notification-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
        }
        .notification-title { 
            font-weight: bold; 
            color: #333; 
            margin: 0; 
        }
        .notification-date { 
            color: #666; 
            font-size: 12px; 
        }
        .notification-message { 
            color: #555; 
            margin: 10px 0; 
            line-height: 1.5;
        }
        .notification-type { 
            padding: 2px 8px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: bold; 
            display: inline-block;
            margin-top: 10px;
        }
        .type-info { 
            background: #d1ecf1; 
            color: #0c5460; 
        }
        .type-success { 
            background: #d4edda; 
            color: #155724; 
        }
        .type-warning { 
            background: #fff3cd; 
            color: #856404; 
        }
        .type-error { 
            background: #f8d7da; 
            color: #721c24; 
        }
        .empty-state { 
            text-align: center; 
            color: #666; 
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .unread-badge { 
            background: #dc3545; 
            color: white; 
            padding: 2px 6px; 
            border-radius: 10px; 
            font-size: 11px; 
            margin-left: 10px; 
        }
        .stats-bar {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-bar .stat {
            display: inline-block;
            margin: 0 15px;
            color: #666;
        }
        .stats-bar .stat strong {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="service-info">🔔 Notifications Service</div>
        
        <h1>Notificações</h1>
        
        <div class="model-info">
            <h3>Métodos disponíveis no NotificationModel:</h3>
            <ul>
                <li>getUserNotifications() - Retorna notificações do usuário</li>
            </ul>
        </div>
        
        <?php if (isset($notifications) && !empty($notifications)): ?>
            <?php
            $totalNotifications = count($notifications);
            $unreadCount = 0;
            foreach ($notifications as $notification) {
                if (!$notification['read_at']) {
                    $unreadCount++;
                }
            }
            ?>
            
            <div class="stats-bar">
                <div class="stat">
                    <strong><?= $totalNotifications ?></strong> Total
                </div>
                <div class="stat">
                    <strong><?= $unreadCount ?></strong> Não lidas
                </div>
                <div class="stat">
                    <strong><?= $totalNotifications - $unreadCount ?></strong> Lidas
                </div>
            </div>
            
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <?php 
                    $unreadClass = $notification['read_at'] ? 'notification-read' : 'notification-unread';
                    $typeClass = 'type-' . ($notification['type'] ?? 'info');
                    $readBadge = $notification['read_at'] ? '' : '<span class="unread-badge">NOVA</span>';
                    ?>
                    <div class="notification-item <?= $unreadClass ?>">
                        <div class="notification-header">
                            <h3 class="notification-title">
                                <?= View::escape($notification['title']) ?> 
                                <?= $readBadge ?>
                            </h3>
                            <span class="notification-date">
                                <?= View::formatDate($notification['created_at']) ?>
                            </span>
                        </div>
                        <div class="notification-message">
                            <?= View::escape($notification['message']) ?>
                        </div>
                        <div class="notification-type <?= $typeClass ?>">
                            <?= strtoupper($notification['type'] ?? 'info') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>🔔 Nenhuma notificação encontrada</h3>
                <p>Você não possui notificações no momento.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
