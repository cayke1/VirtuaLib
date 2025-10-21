# VirtuaLib - Biblioteca Digital

## Universidade Federal do Tocantins (UFT)

**Curso:** Ciência da Computação  
**Disciplina:** Engenharia de Software  
**Semestre:** 1º Semestre de 2025  
**Professor:** Edeilson Milhomem  

---

## 👥 Integrantes do Time

- Cayke ([GitHub](https://github.com/cayke1))
- Lucas Yudi ([GitHub](https://github.com/Yuud1))
- Lucas Gabriel ([GitHub](https://github.com/Kl4uz))
- Filipe ([GitHub](https://github.com/Purazika))
- Gabryel ([GitHub](https://github.com/dellannegabryel-commits))

---

## 📌 Navegação

- [📖 Sobre o Projeto](#-sobre-o-projeto)
- [📚 Requisitos Funcionais](#-requisitos-funcionais-rf)
- [⚙️ Requisitos Não Funcionais](#-requisitos-não-funcionais-rnf)
- [🎭 User Stories](#-user-stories)
- [📝 Planejamento de Tasks](#-sprint-1--planejamento-de-tasks)
- [🧪 Testes Unitários](#-testes-unitários)
- [🔗 Links Úteis](#-links-úteis)

---

## 📖 Sobre o Projeto

O **VirtuaLib** é uma **biblioteca digital** que permite aos usuários:

- Visualizar uma lista de livros disponíveis 📚  
- Buscar por título ou autor 🔎  
- Emprestar e devolver livros de forma simples 🔄  
- Acessar detalhes completos de cada obra (sinopse, ano, autor, categoria) 📖  

O sistema é projetado para oferecer uma **experiência intuitiva e responsiva**, acessível em **desktop** e **dispositivos móveis**.

---

## 📚 Requisitos Funcionais (RF)

- **RF01:** O sistema deve permitir ao usuário visualizar uma lista de livros, exibindo status (disponível/emprestado).  
- **RF02:** O sistema deve oferecer uma barra de busca por título ou autor.  
- **RF03:** O sistema deve permitir ao usuário marcar um livro como emprestado, alterando seu status no catálogo.  
- **RF04:** O sistema deve permitir ao usuário marcar um livro como devolvido, alterando seu status no catálogo.  
- **RF05:** O sistema deve exibir detalhes completos de um livro selecionado (sinopse, ano, autor, categoria).  

---

## ⚙️ Requisitos Não Funcionais (RNF)

- **RNF01:** A listagem e a busca devem retornar resultados em tempo real (tempo de resposta ≤ 2 segundos).  
- **RNF02:** A interface deve ser intuitiva e responsiva.  
- **RNF03:** O sistema deve manter consistência nos dados (não mostrar livro como disponível se estiver emprestado).  
- **RNF04:** O sistema deve armazenar os dados em um banco confiável com suporte a múltiplos usuários.  

---

## 🎭 User Stories

1. **US01 – Ver a Lista de Livros**  
   Como usuário, quero visualizar uma lista de todos os livros disponíveis na biblioteca para saber quais posso pegar emprestado.

2. **US02 – Encontrar um Livro Facilmente**  
   Como usuário, quero buscar livros pelo título ou autor para localizar rapidamente o livro que desejo.

3. **US03 – Emprestar um Livro**  
   Como usuário, quero marcar um livro como “emprestado” para que fique registrado que ele não está mais disponível.

4. **US04 – Devolver um Livro**  
   Como usuário, quero marcar um livro como “devolvido” para que ele fique disponível novamente para outros usuários.

5. **US05 – Ver Detalhes de um Livro**  
   Como usuário, quero visualizar informações detalhadas de um livro (sinopse, ano, autor, categoria) para decidir se desejo pegá-lo emprestado.

---

## 📝 Sprint 1 – Planejamento de Tasks

### US01 – Ver a Lista de Livros
- **Task 1.1:** Implementar listagem básica de livros (front-end).  
  - Dev: *Lucas Yudi* | Revisor: *Cayke*  
- **Task 1.2:** Conectar listagem ao backend (API).  
  - Dev: *Lucas Gabriel* | Revisor: *Filipe*  

### US02 – Encontrar um Livro Facilmente
- **Task 2.1:** Campo de busca (front-end).  
  - Dev: *Filipe* | Revisor: *Gabryel*  
- **Task 2.2:** Endpoint de busca otimizada (backend).  
  - Dev: *Cayke* | Revisor: *Lucas Yudi*  

### US03 – Emprestar um Livro
- **Task 3.1:** Botão de “emprestar” na interface.  
  - Dev: *Gabryel* | Revisor: *Lucas Gabriel*  
- **Task 3.2:** Atualização de status no backend (emprestado).  
  - Dev: *Lucas Yudi* | Revisor: *Filipe*  

### US04 – Devolver um Livro
- **Task 4.1:** Botão de “devolver” na interface.  
  - Dev: *Lucas Gabriel* | Revisor: *Gabryel*  
- **Task 4.2:** Atualização de status no backend (disponível).  
  - Dev: *Cayke* | Revisor: *Lucas Yudi*  

### US05 – Ver Detalhes de um Livro
- **Task 5.1:** Página/modal com detalhes do livro.  
  - Dev: *Filipe* | Revisor: *Cayke*  
- **Task 5.2:** Endpoint para detalhes de um livro específico.  
  - Dev: *Gabryel* | Revisor: *Lucas Gabriel*  

---

## 🧪 Testes Unitários

O projeto VirtuaLib implementa **testes unitários** focados nas **regras de negócio** de cada service, utilizando PHPUnit com mocks para isolamento de dependências.

### 📊 Status dos Testes

- **✅ 9 testes** implementados
- **✅ 35 assertions** executadas  
- **✅ 100% de sucesso** nos testes
- **✅ Cobertura** das regras de negócio principais

### 🏗️ Services Testados

| Service | Arquivo de Teste | Regras Testadas |
|---------|------------------|-----------------|
| **Auth** | `UserModelTest.php` | Autenticação, criação de usuário, fallback |
| **Books** | `BorrowModelTest.php` | Empréstimos, validações, estrutura |
| **Notifications** | `NotificationModelTest.php` | Sistema de notificações, fallback |

### 🚀 Execução dos Testes

```bash
# Instalar dependências
composer require --dev phpunit/phpunit ^12.4

# Executar todos os testes
php vendor/bin/phpunit --testdox

# Executar testes por service
php vendor/bin/phpunit services/auth/test/ --testdox
php vendor/bin/phpunit services/books/test/ --testdox
php vendor/bin/phpunit services/notifications/test/ --testdox
```

### 📚 Documentação Completa

Para informações detalhadas sobre a implementação, estratégias de mock e convenções de teste, consulte:

**[📖 Documentação Completa dos Testes](./docs/testing/README.md)**

---

## 🔗 Links Úteis

- [📌 Trello do Projeto](https://trello.com/invite/b/689d4d47bab2daad9f60e335/ATTIc8f30abdc1bea10d466d116378b9c226F9DC5DA6/virtualib)  
- [🎨 Protótipo no Figma](https://www.figma.com/design/7xDDLk1pqLlJ8qGoq74Suh/Untitled?node-id=0-1&t=d5UdozK2nkhF82av-1)  
- [PDF - SPRINT 1](./docs/sprints//Planejamento%20Tec%20Sprint%201%20-%20ES.pdf)
- [PDF - SPRINT 2](./docs//sprints/Planejamento%20Tec%20Sprint%202%20-%20ES.pdf)
- [PDF - SPRINT 3](./docs/sprints/Planejamento%20Tec%20Sprint%203%20-%20ES.pdf)

---
