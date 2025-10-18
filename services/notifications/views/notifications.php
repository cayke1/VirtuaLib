<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($title ?? 'Notifications Service - Virtual Library') ?></title>
    <link rel="stylesheet" href="/services/notifications/views/public/css/notifications.css">
</head>
<body>
    <div class="container">
        <div class="service-info">Notifications Service</div>
        
        <h1>Notificações</h1>
        
        <div class="model-info">
            <h3>Funcionalidades do Notification Service:</h3>
            <ul>
                <li>Listar notificações do usuário</li>
                <li>Marcar como lida/não lida</li>
                <li>Deletar notificações</li>
                <li>Contar notificações não lidas</li>
                <li>Processar eventos de empréstimo</li>
                <li>API REST completa</li>
            </ul>
        </div>
        
        <?php if (isset($notifications) && !empty($notifications)): ?>
            <?php
            $totalNotifications = count($notifications);
            $unreadCount = 0;
            foreach ($notifications as $notification) {
                if (!$notification['is_read']) {
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
                    $unreadClass = $notification['is_read'] ? 'notification-read' : 'notification-unread';
                    $typeClass = 'type-info'; // Default type
                    $readBadge = $notification['is_read'] ? '' : '<span class="unread-badge">NOVA</span>';
                    
                    // Determine type based on data
                    if (isset($notification['data']) && $notification['data']) {
                        $data = json_decode($notification['data'], true);
                        if (isset($data['type'])) {
                            $typeClass = 'type-' . $data['type'];
                        }
                    }
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
                            <?= strtoupper($notification['title']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhuma notificação encontrada</h3>
                <p>Você não possui notificações no momento.</p>
            </div>
        <?php endif; ?>
    </div>
    <script src="/services/notifications/views/public/js/notifications.js"></script>
</body>
</html>
