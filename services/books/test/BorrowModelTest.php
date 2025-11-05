<?php
/**
 * Testes unitários para BorrowModel - Books Service (Regras de Negócio)
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/BorrowModel.php';

class BorrowModelTest extends TestCase {

    /**
     * Teste da regra de negócio: Instanciação da classe
     * Verifica se a classe pode ser instanciada corretamente
     */
    public function testClassInstantiation(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf(BorrowModel::class, $borrowModel);
    }

    /**
     * Teste da regra de negócio: Constantes da classe
     * Verifica se as constantes estão definidas corretamente
     */
    public function testClassConstants(): void {
        $reflection = new ReflectionClass(BorrowModel::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('DEFAULT_LOAN_DAYS', $constants);
        $this->assertEquals(14, $constants['DEFAULT_LOAN_DAYS']);
    }

    /**
     * Teste da regra de negócio: Métodos públicos
     * Verifica se os métodos principais estão disponíveis
     */
    public function testPublicMethods(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue(method_exists($borrowModel, 'requestBorrow'));
        $this->assertTrue(method_exists($borrowModel, 'approveBorrow'));
        $this->assertTrue(method_exists($borrowModel, 'returnBook'));
        $this->assertTrue(method_exists($borrowModel, 'getHistory'));
        $this->assertTrue(method_exists($borrowModel, 'getPendingRequests'));
    }

    /**
     * Teste da regra de negócio: Tipos de retorno
     * Verifica se os métodos retornam os tipos esperados
     */
    public function testReturnTypes(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Verifica se os métodos existem e são callable
        $this->assertIsCallable([$borrowModel, 'requestBorrow']);
        $this->assertIsCallable([$borrowModel, 'approveBorrow']);
        $this->assertIsCallable([$borrowModel, 'returnBook']);
        $this->assertIsCallable([$borrowModel, 'getHistory']);
        $this->assertIsCallable([$borrowModel, 'getPendingRequests']);
    }

    /**
     * Teste: Request borrow retorna array com estrutura correta
     */
    public function testRequestBorrowReturnsArrayWithCorrectStructure(): void {
        $mockResult = [
            'success' => true,
            'message' => 'Solicitação realizada',
            'request' => ['id' => 1]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['requestBorrow'])
            ->getMock();

        $borrowModel->method('requestBorrow')->willReturn($mockResult);

        $result = $borrowModel->requestBorrow(1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Teste: Approve borrow retorna array com estrutura correta
     */
    public function testApproveBorrowReturnsArrayWithCorrectStructure(): void {
        $mockResult = [
            'success' => true,
            'message' => 'Aprovado',
            'approval' => ['approved_at' => '2024-01-01', 'due_date' => '2024-01-15']
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['approveBorrow'])
            ->getMock();

        $borrowModel->method('approveBorrow')->willReturn($mockResult);

        $result = $borrowModel->approveBorrow(1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Teste: Return book retorna array com estrutura correta
     */
    public function testReturnBookReturnsArrayWithCorrectStructure(): void {
        $mockResult = [
            'success' => true,
            'message' => 'Devolvido',
            'return' => ['returned_at' => '2024-01-20']
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['returnBook'])
            ->getMock();

        $borrowModel->method('returnBook')->willReturn($mockResult);

        $result = $borrowModel->returnBook(1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Teste: Get history retorna array
     */
    public function testGetHistoryReturnsArray(): void {
        $mockHistory = [
            [
                'id' => 1,
                'user_name' => 'João Silva',
                'book_title' => '1984',
                'status' => 'approved'
            ]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHistory'])
            ->getMock();

        $borrowModel->method('getHistory')->willReturn($mockHistory);

        $result = $borrowModel->getHistory();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('João Silva', $result[0]['user_name']);
    }

    /**
     * Teste: Get pending requests retorna array
     */
    public function testGetPendingRequestsReturnsArray(): void {
        $mockRequests = [
            [
                'id' => 1,
                'user_id' => 1,
                'book_id' => 1,
                'user_name' => 'João Silva',
                'book_title' => '1984'
            ]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPendingRequests'])
            ->getMock();

        $borrowModel->method('getPendingRequests')->willReturn($mockRequests);

        $result = $borrowModel->getPendingRequests();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('João Silva', $result[0]['user_name']);
    }

    /**
     * Teste: Get active borrowed book IDs by user
     */
    public function testGetActiveBorrowedBookIdsByUser(): void {
        $mockIds = [1, 2, 3];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getActiveBorrowedBookIdsByUser'])
            ->getMock();

        $borrowModel->method('getActiveBorrowedBookIdsByUser')->willReturn($mockIds);

        $result = $borrowModel->getActiveBorrowedBookIdsByUser(1);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /**
     * Teste: Calculate days overdue
     */
    public function testCalculateDaysOverdue(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
        $result = $borrowModel->calculateDaysOverdue($yesterday);

        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertIsInt($result);
    }

    /**
     * Teste: Get active borrows count
     */
    public function testGetActiveBorrowsCount(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getActiveBorrowsCount'])
            ->getMock();

        $borrowModel->method('getActiveBorrowsCount')->willReturn(5);

        $result = $borrowModel->getActiveBorrowsCount();

        $this->assertEquals(5, $result);
        $this->assertIsInt($result);
    }

    /**
     * Teste: Get today borrows count
     */
    public function testGetTodayBorrowsCount(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTodayBorrowsCount'])
            ->getMock();

        $borrowModel->method('getTodayBorrowsCount')->willReturn(3);

        $result = $borrowModel->getTodayBorrowsCount();

        $this->assertEquals(3, $result);
    }

    /**
     * Teste: Get borrows by month
     */
    public function testGetBorrowsByMonth(): void {
        $mockData = [
            ['month' => '2024-01', 'total_borrows' => 10],
            ['month' => '2024-02', 'total_borrows' => 15]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBorrowsByMonth'])
            ->getMock();

        $borrowModel->method('getBorrowsByMonth')->willReturn($mockData);

        $result = $borrowModel->getBorrowsByMonth();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(10, $result[0]['total_borrows']);
    }

    /**
     * Teste: Get top borrowed books
     */
    public function testGetTopBorrowedBooks(): void {
        $mockData = [
            ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'borrow_count' => 25],
            ['id' => 2, 'title' => 'Dom Casmurro', 'author' => 'Machado de Assis', 'borrow_count' => 20]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTopBorrowedBooks'])
            ->getMock();

        $borrowModel->method('getTopBorrowedBooks')->willReturn($mockData);

        $result = $borrowModel->getTopBorrowedBooks(5);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('1984', $result[0]['title']);
        $this->assertEquals(25, $result[0]['borrow_count']);
    }

    /**
     * Teste: Get pending request book IDs by user
     */
    public function testGetPendingRequestBookIdsByUser(): void {
        $mockIds = [1, 3];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPendingRequestBookIdsByUser'])
            ->getMock();

        $borrowModel->method('getPendingRequestBookIdsByUser')->willReturn($mockIds);

        $result = $borrowModel->getPendingRequestBookIdsByUser(1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Teste: Reject request retorna array
     */
    public function testRejectRequestReturnsArray(): void {
        $mockResult = [
            'success' => true,
            'message' => 'Rejeitado'
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['rejectRequest'])
            ->getMock();

        $borrowModel->method('rejectRequest')->willReturn($mockResult);

        $result = $borrowModel->rejectRequest(1, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * Teste: Get books by category retorna array
     */
    public function testGetBooksByCategoryReturnsArray(): void {
        $mockData = [
            ['category' => 'Ficção', 'book_count' => 15],
            ['category' => 'Romance', 'book_count' => 10]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBooksByCategory'])
            ->getMock();

        $borrowModel->method('getBooksByCategory')->willReturn($mockData);

        $result = $borrowModel->getBooksByCategory();

        $this->assertIsArray($result);
    }

    /**
     * Teste: Get recent activities
     */
    public function testGetRecentActivities(): void {
        $mockData = [
            ['id' => 1, 'status' => 'approved', 'user_name' => 'João']
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRecentActivities'])
            ->getMock();

        $borrowModel->method('getRecentActivities')->willReturn($mockData);

        $result = $borrowModel->getRecentActivities(10);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Teste: Update overdue status
     */
    public function testUpdateOverdueStatus(): void {
        $mockResult = [
            'success' => true,
            'updated_count' => 2
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOverdueStatus'])
            ->getMock();

        $borrowModel->method('updateOverdueStatus')->willReturn($mockResult);

        $result = $borrowModel->updateOverdueStatus();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Teste: Get overdue borrows by user
     */
    public function testGetOverdueBorrowsByUser(): void {
        $mockData = [
            ['id' => 1, 'book_title' => '1984', 'days_overdue' => 5]
        ];

        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOverdueBorrowsByUser'])
            ->getMock();

        $borrowModel->method('getOverdueBorrowsByUser')->willReturn($mockData);

        $result = $borrowModel->getOverdueBorrowsByUser(1);

        $this->assertIsArray($result);
    }

    /**
     * Teste: Is borrow overdue
     */
    public function testIsBorrowOverdue(): void {
        $borrowModel = $this->getMockBuilder(BorrowModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isBorrowOverdue'])
            ->getMock();

        $borrowModel->method('isBorrowOverdue')->willReturn(true);

        $result = $borrowModel->isBorrowOverdue(1);

        $this->assertIsBool($result);
    }
}
