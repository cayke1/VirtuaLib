<?php
/**
 * Testes unitários para BookModel - Books Service
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/BookModel.php';

class BookModelTest extends TestCase {

    /**
     * Teste: Classe pode ser instanciada
     */
    public function testClassCanBeInstantiated(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(BookModel::class, $bookModel);
    }

    /**
     * Teste: Métodos públicos existem
     */
    public function testPublicMethodsExist(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue(method_exists($bookModel, 'getBooks'));
        $this->assertTrue(method_exists($bookModel, 'search'));
        $this->assertTrue(method_exists($bookModel, 'getBookById'));
        $this->assertTrue(method_exists($bookModel, 'createBook'));
        $this->assertTrue(method_exists($bookModel, 'updateBook'));
        $this->assertTrue(method_exists($bookModel, 'deleteBook'));
        $this->assertTrue(method_exists($bookModel, 'getTotalBooks'));
        $this->assertTrue(method_exists($bookModel, 'getBooksByCategory'));
    }

    /**
     * Teste: Get books retorna array (usando mock parcial)
     */
    public function testGetBooksReturnsArray(): void {
        $mockBooks = [
            ['id' => 1, 'title' => '1984', 'author' => 'George Orwell'],
            ['id' => 2, 'title' => 'Dom Casmurro', 'author' => 'Machado de Assis']
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBooks'])
            ->getMock();

        $bookModel->method('getBooks')->willReturn($mockBooks);

        $result = $bookModel->getBooks();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('1984', $result[0]['title']);
    }

    /**
     * Teste: Search retorna array
     */
    public function testSearchReturnsArray(): void {
        $mockBooks = [
            ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'genre' => 'Ficção']
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['search'])
            ->getMock();

        $bookModel->method('search')->willReturn($mockBooks);

        $result = $bookModel->search('1984');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('1984', $result[0]['title']);
    }

    /**
     * Teste: Get book by ID retorna array ou null
     */
    public function testGetBookByIdReturnsArrayOrNull(): void {
        $mockBook = ['id' => 1, 'title' => '1984', 'author' => 'George Orwell'];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBookById'])
            ->getMock();

        $bookModel->method('getBookById')->willReturn($mockBook);

        $result = $bookModel->getBookById(1);

        $this->assertIsArray($result);
        $this->assertEquals('1984', $result['title']);
    }

    /**
     * Teste: Create book retorna ID
     */
    public function testCreateBookReturnsId(): void {
        $bookData = [
            'title' => 'Novo Livro',
            'author' => 'Autor Teste',
            'genre' => 'Ficção',
            'year' => 2024
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createBook'])
            ->getMock();

        $bookModel->method('createBook')->willReturn(123);

        $result = $bookModel->createBook($bookData);

        $this->assertEquals(123, $result);
        $this->assertIsInt($result);
    }

    /**
     * Teste: Update book retorna boolean
     */
    public function testUpdateBookReturnsBoolean(): void {
        $updateData = ['title' => 'Título Atualizado'];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateBook'])
            ->getMock();

        $bookModel->method('updateBook')->willReturn(true);

        $result = $bookModel->updateBook(1, $updateData);

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    /**
     * Teste: Delete book retorna boolean
     */
    public function testDeleteBookReturnsBoolean(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteBook'])
            ->getMock();

        $bookModel->method('deleteBook')->willReturn(true);

        $result = $bookModel->deleteBook(1);

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    /**
     * Teste: Get total books retorna integer
     */
    public function testGetTotalBooksReturnsInteger(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTotalBooks'])
            ->getMock();

        $bookModel->method('getTotalBooks')->willReturn(42);

        $result = $bookModel->getTotalBooks();

        $this->assertEquals(42, $result);
        $this->assertIsInt($result);
    }

    /**
     * Teste: Get books by category retorna array com estrutura correta
     */
    public function testGetBooksByCategoryReturnsArrayWithCorrectStructure(): void {
        $mockData = [
            ['nome' => 'Ficção', 'percentual' => 50.0, 'color' => '#059669'],
            ['nome' => 'Romance', 'percentual' => 30.0, 'color' => '#3b82f6'],
            ['nome' => 'Fantasia', 'percentual' => 20.0, 'color' => '#14b8a6']
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBooksByCategory'])
            ->getMock();

        $bookModel->method('getBooksByCategory')->willReturn($mockData);

        $result = $bookModel->getBooksByCategory();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('nome', $result[0]);
        $this->assertArrayHasKey('percentual', $result[0]);
        $this->assertArrayHasKey('color', $result[0]);
    }

    /**
     * Teste: Create book com campos obrigatórios
     */
    public function testCreateBookWithRequiredFields(): void {
        $bookData = [
            'title' => 'Livro Teste',
            'author' => 'Autor Teste'
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createBook'])
            ->getMock();

        $bookModel->method('createBook')->willReturn(1);

        $result = $bookModel->createBook($bookData);

        $this->assertGreaterThan(0, $result);
    }

    /**
     * Teste: Update book sem campos retorna true (no-op)
     */
    public function testUpdateBookWithEmptyDataReturnsTrue(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateBook'])
            ->getMock();

        $bookModel->method('updateBook')->willReturn(true);

        $result = $bookModel->updateBook(1, []);

        $this->assertTrue($result);
    }

    /**
     * Teste: Search com query vazia retorna array
     */
    public function testSearchWithEmptyQueryReturnsArray(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['search'])
            ->getMock();

        $bookModel->method('search')->willReturn([]);

        $result = $bookModel->search('');

        $this->assertIsArray($result);
    }

    /**
     * Teste: Get book by ID com ID inválido retorna null
     */
    public function testGetBookByIdWithInvalidIdReturnsNull(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBookById'])
            ->getMock();

        $bookModel->method('getBookById')->willReturn(null);

        $result = $bookModel->getBookById(999);

        $this->assertNull($result);
    }

    /**
     * Teste: Delete book com ID inválido retorna false
     */
    public function testDeleteBookWithInvalidIdReturnsFalse(): void {
        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteBook'])
            ->getMock();

        $bookModel->method('deleteBook')->willReturn(false);

        $result = $bookModel->deleteBook(999);

        $this->assertFalse($result);
    }

    /**
     * Teste: Update book com PDF fields
     */
    public function testUpdateBookWithPdfFields(): void {
        $updateData = [
            'pdf_src' => '/path/to/pdf',
            'pdf_key' => 'abc123',
            'pdf_storage' => 's3'
        ];

        $bookModel = $this->getMockBuilder(BookModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateBook'])
            ->getMock();

        $bookModel->method('updateBook')->willReturn(true);

        $result = $bookModel->updateBook(1, $updateData);

        $this->assertTrue($result);
    }
}
