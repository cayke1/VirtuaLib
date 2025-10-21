# Testes Unitários - VirtuaLib

## 📋 Visão Geral

Este documento descreve a estrutura e implementação dos testes unitários do projeto VirtuaLib, focando nas **regras de negócio** de cada service.

## 🏗️ Arquitetura de Testes

### Estrutura de Diretórios

```
services/
├── auth/test/           # Testes do Auth Service
├── books/test/          # Testes do Books Service  
├── dashboard/test/      # Testes do Dashboard Service
└── notifications/test/  # Testes do Notifications Service
```

### Framework e Ferramentas

- **Framework**: PHPUnit 12.4
- **Estratégia**: Mocks para isolamento de dependências
- **Foco**: Regras de negócio específicas de cada service

## 🧪 Services e Testes

### Auth Service

**Arquivo**: `services/auth/test/UserModelTest.php`

**Regras de Negócio Testadas**:
- ✅ **Autenticação de usuário**: Validação de senha e remoção de dados sensíveis
- ✅ **Criação de usuário**: Criptografia de senha antes do armazenamento
- ✅ **Fallback de sistema**: Funcionamento sem conexão com banco

**Métodos Testados**:
- `verifyPassword()` - Validação de credenciais
- `createUser()` - Criação de novos usuários
- `getUserData()` - Fallback quando banco indisponível

### Books Service

**Arquivo**: `services/books/test/BorrowModelTest.php`

**Regras de Negócio Testadas**:
- ✅ **Instanciação da classe**: Verificação de criação correta
- ✅ **Constantes da classe**: Validação de configurações
- ✅ **Métodos públicos**: Disponibilidade de funcionalidades
- ✅ **Tipos de retorno**: Verificação de interfaces

**Métodos Testados**:
- `requestBorrow()` - Solicitação de empréstimo
- `approveBorrow()` - Aprovação de empréstimo
- `returnBook()` - Devolução de livro
- `getHistory()` - Histórico de empréstimos
- `getPendingRequests()` - Solicitações pendentes

### Notifications Service

**Arquivo**: `services/notifications/test/NotificationModelTest.php`

**Regras de Negócio Testadas**:
- ✅ **Sistema de notificações**: Criação e marcação como lidas
- ✅ **Fallback de sistema**: Funcionamento sem conexão com banco

**Métodos Testados**:
- `create()` - Criação de notificações
- `markAsRead()` - Marcação como lida
- `countUnreadByUserId()` - Contagem de não lidas
- `getByUserId()` - Fallback quando banco indisponível

## 🔧 Configuração e Execução

### Pré-requisitos

```bash
# Instalar PHPUnit
composer require --dev phpunit/phpunit ^12.4
```

### Executar Testes

```bash
# Todos os testes
php vendor/bin/phpunit --testdox

# Service específico
php vendor/bin/phpunit services/auth/test/ --testdox
php vendor/bin/phpunit services/books/test/ --testdox
php vendor/bin/phpunit services/notifications/test/ --testdox

# Teste específico
php vendor/bin/phpunit services/auth/test/UserModelTest.php --testdox
```

### Scripts de Automação

**Windows** (`run-tests.bat`):
```batch
@echo off
php vendor/bin/phpunit --testdox
```

## 📊 Resultados dos Testes

### Status Atual

- **Total de Testes**: 9
- **Assertions**: 35
- **Taxa de Sucesso**: 100%
- **Cobertura**: Regras de negócio principais

### Relatório de Execução

```
Borrow Model
 ✔ Class instantiation
 ✔ Class constants  
 ✔ Public methods
 ✔ Return types

Notification Model
 ✔ Notification system business rules
 ✔ Fallback when database unavailable

User Model
 ✔ User authentication business rule
 ✔ User creation business rule
 ✔ Fallback when database unavailable
```

## 🎯 Estratégia de Mock

### Isolamento de Dependências

Os testes utilizam **mocks** para isolar os models das dependências externas:

```php
// Mock do PDO
$this->mockPdo = $this->createMock(PDO::class);
$this->mockStatement = $this->createMock(PDOStatement::class);

// Injeção via Reflection
$reflection = new ReflectionClass($model);
$pdoProperty = $reflection->getProperty('pdo');
$pdoProperty->setAccessible(true);
$pdoProperty->setValue($model, $this->mockPdo);
```

### Supressão de Conexões

Para evitar tentativas de conexão real com banco durante os testes:

```php
// Suprimir erros de conexão
$originalErrorReporting = error_reporting();
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$model = new Model();

// Restaurar configuração
error_reporting($originalErrorReporting);
```

## 📝 Convenções de Teste

### Nomenclatura

- **Classes**: `{ModelName}Test`
- **Métodos**: `test{BusinessRuleDescription}`
- **Arquivos**: `{ModelName}Test.php`

### Estrutura de Teste

```php
public function testBusinessRuleDescription(): void {
    // Arrange - Configurar dados e mocks
    
    // Act - Executar ação sendo testada
    
    // Assert - Verificar resultados esperados
}
```

### Documentação

Cada teste inclui:
- **Descrição clara** da regra de negócio testada
- **Comentários** explicando cenários específicos
- **Assertions** com mensagens descritivas

## 🔄 Manutenção e Evolução

### Adicionando Novos Testes

1. **Identificar regra de negócio** a ser testada
2. **Criar método de teste** seguindo convenções
3. **Configurar mocks** necessários
4. **Implementar assertions** específicas
5. **Executar e validar** resultados

### Boas Práticas

- ✅ **Foco em regras de negócio**, não em implementação
- ✅ **Uso de mocks** para isolamento
- ✅ **Nomenclatura clara** e descritiva
- ✅ **Documentação** adequada
- ✅ **Execução rápida** e confiável

---

**Última atualização**: Janeiro 2025  
**Versão**: 1.0  
**Framework**: PHPUnit 12.4
