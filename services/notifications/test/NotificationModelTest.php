<?php
/**
 * Testes unitários para NotificationModel - Notifications Service (Regras de Negócio)
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationModelTest extends TestCase {
    
    private $mockPdo;
    private $mockStatement;
    
    protected function setUp(): void {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);
        
        $this->mockPdo->method('query')
            ->willReturn($this->mockStatement);
    }
    
    protected function tearDown(): void {
        $this->mockPdo = null;
        $this->mockStatement = null;
    }
    
    /**
     * Create NotificationModel without database connection
     */
    private function createNotificationModelWithoutConnection(): NotificationModel {
        // Suppress database connection errors during testing
        $originalErrorReporting = error_reporting();
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
        
        $notificationModel = new NotificationModel();
        
        // Restore error reporting
        error_reporting($originalErrorReporting);
        
        return $notificationModel;
    }
    
    /**
     * Inject mock PDO into NotificationModel using reflection
     */
    private function injectMockPdo(NotificationModel $notificationModel): void {
        $reflection = new ReflectionClass($notificationModel);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($notificationModel, $this->mockPdo);
    }
    
    /**
     * Teste da regra de negócio: Sistema de notificações
     * Verifica se notificações são criadas, marcadas como lidas e contadas corretamente
     */
    public function testNotificationSystemBusinessRules(): void {
        // Cenário 1: Criar notificação
        $this->mockPdo->method('lastInsertId')
            ->willReturn('123');
        
        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);
        
        $result = $notificationModel->create(1, 'Livro Disponível', 'O livro "1984" está disponível para empréstimo.', ['book_id' => 1]);
        
        $this->assertEquals(123, $result);
        
        // Cenário 2: Buscar notificações do usuário
        $mockNotifications = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Livro Disponível',
                'message' => 'O livro "1984" está disponível para empréstimo.',
                'data' => '{"book_id": 1}',
                'is_read' => 0,
                'created_at' => '2024-01-15 14:30:00'
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'title' => 'Empréstimo Aprovado',
                'message' => 'Seu empréstimo foi aprovado.',
                'data' => null,
                'is_read' => 1,
                'created_at' => '2024-01-15 09:45:00'
            ]
        ];
        
        $this->mockStatement->method('fetchAll')
            ->willReturn($mockNotifications);
        
        $result = $notificationModel->getByUserId(1);
        
        $this->assertCount(2, $result);
        $this->assertEquals('Livro Disponível', $result[0]['title']);
        $this->assertEquals(0, $result[0]['is_read']); // Não lida
        $this->assertEquals(1, $result[1]['is_read']); // Lida
        
        // Cenário 3: Contar notificações não lidas
        $this->mockStatement->method('fetch')
            ->willReturn(['cnt' => 1]);
        
        $unreadCount = $notificationModel->countUnreadByUserId(1);
        
        $this->assertEquals(1, $unreadCount);
        
        // Cenário 4: Marcar todas como lidas
        $this->mockStatement->method('execute')
            ->willReturn(true);
        
        $result = $notificationModel->markAllReadByUserId(1);
        
        $this->assertTrue($result);
    }
    
    /**
     * Teste da regra de negócio: Fallback quando banco não está disponível
     * Verifica se o sistema continua funcionando com dados simulados
     */
    public function testFallbackWhenDatabaseUnavailable(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->getByUserId(1);

        // Regra: Sistema deve continuar funcionando com dados de fallback
        $this->assertIsArray($result);
        $this->assertCount(3, $result); // Dados de fallback
        $this->assertEquals('Livro Disponível', $result[0]['title']);
        $this->assertEquals('Empréstimo Aprovado', $result[1]['title']);
    }

    /**
     * Teste: Mark as read - sucesso
     */
    public function testMarkAsReadSuccess(): void {
        $this->mockStatement->method('execute')->willReturn(true);
        $this->mockPdo->method('prepare')->willReturn($this->mockStatement);

        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);

        $result = $notificationModel->markAsRead(1, 1);

        $this->assertTrue($result);
    }

    /**
     * Teste: Mark as read - sem PDO retorna false
     */
    public function testMarkAsReadWithoutPdoReturnsFalse(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->markAsRead(1, 1);

        $this->assertFalse($result);
    }

    /**
     * Teste: Delete notification - sucesso
     */
    public function testDeleteNotificationSuccess(): void {
        $this->mockStatement->method('execute')->willReturn(true);
        $this->mockPdo->method('prepare')->willReturn($this->mockStatement);

        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);

        $result = $notificationModel->delete(1, 1);

        $this->assertTrue($result);
    }

    /**
     * Teste: Delete notification - sem PDO retorna false
     */
    public function testDeleteNotificationWithoutPdoReturnsFalse(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->delete(1, 1);

        $this->assertFalse($result);
    }

    /**
     * Teste: Create bulk notifications - sucesso
     */
    public function testCreateBulkNotificationsSuccess(): void {
        $notifications = [
            [
                'user_id' => 1,
                'title' => 'Teste 1',
                'message' => 'Mensagem 1',
                'data' => ['key' => 'value']
            ],
            [
                'user_id' => 2,
                'title' => 'Teste 2',
                'message' => 'Mensagem 2',
                'data' => null
            ]
        ];

        $this->mockPdo->method('beginTransaction')->willReturn(true);
        $this->mockPdo->method('commit')->willReturn(true);
        $this->mockStatement->method('execute')->willReturn(true);
        $this->mockPdo->method('prepare')->willReturn($this->mockStatement);

        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);

        $result = $notificationModel->createBulk($notifications);

        $this->assertTrue($result);
    }

    /**
     * Teste: Create bulk notifications - sem PDO retorna false
     */
    public function testCreateBulkNotificationsWithoutPdoReturnsFalse(): void {
        $notifications = [
            ['user_id' => 1, 'title' => 'Teste', 'message' => 'Mensagem']
        ];

        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->createBulk($notifications);

        $this->assertFalse($result);
    }

    /**
     * Teste: Create notification - sem PDO retorna false
     */
    public function testCreateNotificationWithoutPdoReturnsFalse(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->create(1, 'Título', 'Mensagem');

        $this->assertFalse($result);
    }

    /**
     * Teste: Count unread - sem PDO retorna 0
     */
    public function testCountUnreadWithoutPdoReturnsZero(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->countUnreadByUserId(1);

        $this->assertEquals(0, $result);
    }

    /**
     * Teste: Mark all read - sem PDO retorna false
     */
    public function testMarkAllReadWithoutPdoReturnsFalse(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->markAllReadByUserId(1);

        $this->assertFalse($result);
    }

    /**
     * Teste: Get user notifications retorna fallback data
     */
    public function testGetUserNotificationsReturnsFallbackData(): void {
        $notificationModel = $this->createNotificationModelWithoutConnection();

        $result = $notificationModel->getUserNotifications();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('Livro Disponível', $result[0]['title']);
    }

    /**
     * Teste: Create notification com data null
     */
    public function testCreateNotificationWithNullData(): void {
        $this->mockPdo->method('lastInsertId')->willReturn('456');
        $this->mockStatement->method('execute')->willReturn(true);
        $this->mockPdo->method('prepare')->willReturn($this->mockStatement);

        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);

        $result = $notificationModel->create(1, 'Título', 'Mensagem', null);

        $this->assertEquals(456, $result);
    }

    /**
     * Teste: Create notification com erro no PDO
     */
    public function testCreateNotificationWithPdoError(): void {
        $this->mockStatement->method('execute')->willThrowException(new PDOException());
        $this->mockPdo->method('prepare')->willReturn($this->mockStatement);

        $notificationModel = $this->createNotificationModelWithoutConnection();
        $this->injectMockPdo($notificationModel);

        $result = $notificationModel->create(1, 'Título', 'Mensagem');

        $this->assertFalse($result);
    }
}
