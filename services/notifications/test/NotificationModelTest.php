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
}
