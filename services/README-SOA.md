# Virtual Library - SOA Architecture

## Visão Geral

Este projeto foi estruturado em uma arquitetura orientada a serviços (SOA) com os seguintes serviços:

### 🏗️ Estrutura dos Serviços

```
services/
├── api-gateway.php          # Gateway principal para roteamento
├── utils/                   # Utilitários compartilhados
│   └── View.php             # Sistema de renderização de views
├── auth/                    # Serviço de Autenticação
│   ├── index.php
│   ├── routes.php
│   ├── controllers/
│   ├── models/
│   └── views/               # Views do serviço de autenticação
│       └── auth.php
├── books/                   # Serviço de Livros
│   ├── index.php
│   ├── routes.php
│   ├── controllers/
│   ├── models/
│   └── views/               # Views do serviço de livros
│       └── books.php
├── notifications/           # Serviço de Notificações
│   ├── index.php
│   ├── routes.php
│   ├── controllers/
│   ├── models/
│   └── views/               # Views do serviço de notificações
│       └── notifications.php
└── dashboard/              # Serviço de Dashboard
    ├── index.php
    ├── routes.php
    ├── controllers/
    ├── models/
    └── views/               # Views do serviço de dashboard
        └── dashboard.php
```

## 🚀 Como Executar

### Opção 1: Script Automático
```bash
chmod +x services/start-soa.sh
./services/start-soa.sh
```

### Opção 2: Docker Compose Manual
```bash
docker-compose -f docker-compose.soa.yml up --build -d
```

## 🌐 Endpoints Disponíveis

### API Gateway (Porta 8080)
- **Principal**: http://localhost:8080
- Roteia automaticamente para os serviços apropriados

### Serviços Individuais

#### Auth Service (Porta 8081)
- **Autenticação**: http://localhost:8080/auth/
- **Login**: http://localhost:8080/auth/login
- **Registro**: http://localhost:8080/auth/register
- **Perfil**: http://localhost:8080/auth/profile
- **API Login**: http://localhost:8080/auth/api/login
- **API Register**: http://localhost:8080/auth/api/register

#### Books Service (Porta 8082)
- **Catálogo de Livros**: http://localhost:8080/books/ (ou http://localhost:8080/)
- **Buscar Livros**: http://localhost:8080/books/search
- **Detalhes**: http://localhost:8080/books/details/{id}
- **API Livros**: http://localhost:8080/books/api/books
- **Solicitar Empréstimo**: http://localhost:8080/books/api/books/{id}/request

#### Notifications Service (Porta 8083)
- **Notificações**: http://localhost:8080/notifications/
- **Não Lidas**: http://localhost:8080/notifications/unread
- **API Notificações**: http://localhost:8080/notifications/api/notifications

#### Dashboard Service (Porta 8084)
- **Dashboard**: http://localhost:8080/dashboard/
- **Estatísticas**: http://localhost:8080/dashboard/stats
- **Histórico**: http://localhost:8080/dashboard/historico
- **API Stats**: http://localhost:8080/dashboard/api/stats/general

## 🔧 Funcionalidades por Serviço

### Auth Service
- ✅ Login/Logout
- ✅ Registro de usuários
- ✅ Gerenciamento de perfil
- ✅ Autenticação via sessão
- ✅ Rotas de view e API

### Books Service
- ✅ Listagem de livros
- ✅ Busca de livros
- ✅ Detalhes do livro
- ✅ Empréstimo/Devolução
- ✅ Dashboard administrativo
- ✅ Histórico de empréstimos

### Notifications Service
- ✅ Listagem de notificações
- ✅ Marcar como lida
- ✅ Deletar notificações
- ✅ Contagem de não lidas
- ✅ Criação de notificações (admin)

### Dashboard Service
- ✅ Estatísticas gerais
- ✅ Gráficos e analytics
- ✅ Relatórios por período
- ✅ Top livros mais emprestados
- ✅ Atividades recentes

## 🐳 Configuração Docker

### Arquivos Docker
- `docker-compose.soa.yml` - Configuração dos serviços
- `Dockerfile.soa` - Imagem customizada para SOA
- `apache-soa.conf` - Configuração do Apache
- `nginx.conf` - Load balancer (opcional)

### Variáveis de Ambiente
```env
SERVICE_NAME=auth|books|notifications|dashboard
SERVICE_PORT=8081|8082|8083|8084
DB_HOST=mysql
DB_NAME=virtualib
DB_USER=root
DB_PASSWORD=password
```

## 📊 Monitoramento

### Verificar Status dos Serviços
```bash
docker-compose -f docker-compose.soa.yml ps
```

### Ver Logs
```bash
# Todos os serviços
docker-compose -f docker-compose.soa.yml logs -f

# Serviço específico
docker-compose -f docker-compose.soa.yml logs -f auth-service
```

### Health Check
```bash
curl http://localhost:8080/health
```

## 🔄 Migração do Sistema Original

O sistema original continua funcionando normalmente. A estrutura SOA foi criada como uma extensão:

1. **Sistema Original**: Continua em `/index.php` (estrutura monolítica)
2. **Sistema SOA**: Novos serviços em `/services/`
3. **API Gateway**: Roteia entre sistemas conforme necessário

### Roteamento
- **Rotas por Prefixo**: Cada serviço tem seu prefixo específico (`/auth`, `/books`, `/notifications`, `/dashboard`)
- **Rota Raiz**: A rota `/` redireciona automaticamente para o serviço de livros (`/books`)
- **Fallback**: Rotas não reconhecidas redirecionam para o sistema original
- **API Gateway**: Centraliza o roteamento e remove prefixos antes de enviar para os serviços

## 🛠️ Desenvolvimento

### Sistema de Views

O projeto agora utiliza um sistema de views separado dos controllers:

#### View Utility (`services/utils/View.php`)
- Classe utilitária para renderização de views
- Métodos: `render()`, `display()`, `escape()`, `formatDate()`
- Suporte a variáveis de template
- Escape automático de HTML para segurança

#### Estrutura de Views
- Cada serviço possui sua própria pasta `views/`
- Views são arquivos PHP com HTML e PHP embebido
- Controllers usam `View::display()` para renderizar

#### Exemplo de Uso
```php
// No controller
View::setBasePath(__DIR__ . '/../views/');
View::display('minha-view', ['dados' => $dados]);

// Na view (minha-view.php)
<h1><?= View::escape($dados['titulo']) ?></h1>
```

### Adicionar Novo Serviço
1. Criar pasta em `/services/novo-servico/`
2. Implementar estrutura MVC com views separadas
3. Adicionar ao `docker-compose.soa.yml`
4. Configurar roteamento no API Gateway

### Testar Serviços Individualmente
```bash
# Testar Auth Service
curl http://localhost:8081/api/me

# Testar Books Service  
curl http://localhost:8082/api/books

# Testar Notifications Service
curl http://localhost:8083/api/notifications

# Testar Dashboard Service
curl http://localhost:8084/api/stats/general
```

## 📝 Notas Importantes

- Cada serviço é independente e pode ser desenvolvido/deployado separadamente
- O banco de dados MySQL é compartilhado entre todos os serviços
- As sessões são compartilhadas via API Gateway
- A estrutura mantém compatibilidade com o sistema original
