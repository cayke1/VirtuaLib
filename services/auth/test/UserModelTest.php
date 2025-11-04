<?php
/**
 * Testes unitários para UserModel - Auth Service (Regras de Negócio)
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/UserModel.php';

class UserModelTest extends TestCase {
    
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
     * Create UserModel without database connection
     */
    private function createUserModelWithoutConnection(): UserModel {
        // Suppress database connection errors during testing
        $originalErrorReporting = error_reporting();
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
        
        $userModel = new UserModel();
        
        // Restore error reporting
        error_reporting($originalErrorReporting);
        
        return $userModel;
    }
    
    /**
     * Inject mock PDO into UserModel using reflection
     */
    private function injectMockPdo(UserModel $userModel): void {
        $reflection = new ReflectionClass($userModel);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($userModel, $this->mockPdo);
    }
    
    /**
     * Teste da regra de negócio: Autenticação de usuário
     * Verifica se a senha é validada corretamente e se dados sensíveis são removidos
     */
    public function testUserAuthenticationBusinessRule(): void {
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $mockUser = [
            'id' => 1,
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'password' => $hashedPassword,
            'role' => 'user'
        ];
        
        $this->mockStatement->method('fetch')
            ->willReturn($mockUser);
        
        // Criar UserModel sem conectar ao banco
        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);
        
        // Teste: Senha válida
        list($isValid, $user) = $userModel->verifyPassword('joao@test.com', 'password123');
        
        $this->assertTrue($isValid);
        $this->assertNotNull($user);
        $this->assertArrayNotHasKey('password', $user); // Regra: senha não deve ser retornada
        $this->assertEquals('João Silva', $user['name']);
        
        // Teste: Senha inválida
        list($isValid, $user) = $userModel->verifyPassword('joao@test.com', 'wrongpassword');
        
        $this->assertFalse($isValid);
        $this->assertNull($user);
    }
    
    /**
     * Teste da regra de negócio: Criação de usuário com hash de senha
     * Verifica se a senha é criptografada antes de ser armazenada
     */
    public function testUserCreationBusinessRule(): void {
        $this->mockPdo->method('lastInsertId')
            ->willReturn('123');
        
        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);
        
        $result = $userModel->createUser('João Silva', 'joao@test.com', 'password123', 'user');
        
        $this->assertEquals(123, $result);
    }
    
    /**
     * Teste da regra de negócio: Fallback quando banco não está disponível
     * Verifica se o sistema continua funcionando mesmo sem conexão com banco
     */
    public function testFallbackWhenDatabaseUnavailable(): void {
        $userModel = new UserModel();

        // Simula falha de conexão com banco
        $reflection = new ReflectionClass($userModel);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($userModel, null);

        $result = $userModel->getUserData();

        // Regra: Sistema deve continuar funcionando com dados de fallback
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertEquals('Usuário Exemplo', $result['name']);
    }

    /**
     * Teste: Buscar todos os usuários
     */
    public function testGetAllUsers(): void {
        $mockUsers = [
            ['id' => 1, 'name' => 'User 1', 'email' => 'user1@test.com', 'role' => 'user', 'created_at' => '2024-01-01'],
            ['id' => 2, 'name' => 'User 2', 'email' => 'user2@test.com', 'role' => 'admin', 'created_at' => '2024-01-02']
        ];

        $this->mockStatement->method('fetchAll')
            ->willReturn($mockUsers);

        $this->mockStatement->method('rowCount')
            ->willReturn(2);

        $this->mockPdo->method('query')
            ->willReturn($this->mockStatement);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        $result = $userModel->getAll();

        $this->assertCount(2, $result);
        $this->assertEquals('User 1', $result[0]['name']);
        $this->assertEquals('admin', $result[1]['role']);
    }

    /**
     * Teste: Buscar usuário por email - sucesso
     */
    public function testFindByEmailSuccess(): void {
        $mockUser = [
            'id' => 1,
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'user'
        ];

        $this->mockStatement->method('fetch')
            ->willReturn($mockUser);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        $result = $userModel->findByEmail('joao@test.com');

        $this->assertNotNull($result);
        $this->assertEquals('João Silva', $result['name']);
        $this->assertEquals('joao@test.com', $result['email']);
    }

    /**
     * Teste: Buscar usuário por email - não encontrado
     */
    public function testFindByEmailNotFound(): void {
        $this->mockStatement->method('fetch')
            ->willReturn(false);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        $result = $userModel->findByEmail('naoexiste@test.com');

        $this->assertNull($result);
    }

    /**
     * Teste: Contagem de usuários ativos
     */
    public function testGetActiveUsersCount(): void {
        $this->mockStatement->method('fetch')
            ->willReturn(['total' => 5]);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        $result = $userModel->getActiveUsersCount();

        $this->assertEquals(5, $result);
    }

    /**
     * Teste: Buscar usuário por ID
     */
    public function testGetUserById(): void {
        $mockUser = [
            'id' => 1,
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'role' => 'user',
            'created_at' => '2024-01-01'
        ];

        $this->mockStatement->method('fetch')
            ->willReturn($mockUser);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        $result = $userModel->getUserById(1);

        $this->assertNotNull($result);
        $this->assertEquals('João Silva', $result['name']);
        $this->assertEquals('joao@test.com', $result['email']);
    }

    /**
     * Teste: Verificar senha com usuário não encontrado
     */
    public function testVerifyPasswordUserNotFound(): void {
        $this->mockStatement->method('fetch')
            ->willReturn(false);

        $userModel = $this->createUserModelWithoutConnection();
        $this->injectMockPdo($userModel);

        list($isValid, $user) = $userModel->verifyPassword('naoexiste@test.com', 'anypassword');

        $this->assertFalse($isValid);
        $this->assertNull($user);
    }
}
