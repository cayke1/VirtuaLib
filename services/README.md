# SOA Services Structure

Esta pasta contém os serviços organizados em uma arquitetura orientada a serviços (SOA).

## Estrutura dos Serviços

Cada serviço possui sua própria estrutura MVC:
- `controllers/` - Controladores específicos do serviço
- `models/` - Modelos de dados do serviço
- `views/` - Views/templates do serviço
- `routes.php` - Definição de rotas do serviço
- `index.php` - Ponto de entrada do serviço

## Serviços Disponíveis

- **Auth** - Autenticação e autorização de usuários
- **Books** - Gerenciamento de livros e empréstimos
- **Notifications** - Sistema de notificações
- **Dashboard** - Painel administrativo e estatísticas

## API Gateway

O `api-gateway.php` é responsável por rotear as requisições para os serviços apropriados.
