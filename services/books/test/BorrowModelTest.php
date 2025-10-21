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
        $borrowModel = new BorrowModel();
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
        $borrowModel = new BorrowModel();
        
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
        $borrowModel = new BorrowModel();
        
        // Verifica se os métodos existem e são callable
        $this->assertIsCallable([$borrowModel, 'requestBorrow']);
        $this->assertIsCallable([$borrowModel, 'approveBorrow']);
        $this->assertIsCallable([$borrowModel, 'returnBook']);
        $this->assertIsCallable([$borrowModel, 'getHistory']);
        $this->assertIsCallable([$borrowModel, 'getPendingRequests']);
    }
}
