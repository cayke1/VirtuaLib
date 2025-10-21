# Controle de Perfis e Rotas

## O que mudou?

O sistema agora separa as rotas acessíveis para usuários comuns e administradores, garantindo que cada perfil só acesse as funcionalidades permitidas.

### 1. Separação das rotas
- No arquivo `services/*/routes.php`, foram criados dois arrays:
  - `$userRoutes`: contém apenas as rotas acessíveis para usuários comuns.
  - `$adminRoutes`: inclui todas as rotas de usuário e adiciona rotas exclusivas de administrador (ex: criação de livros).

### 2. Seleção dinâmica das rotas
- No arquivo `services/*/index.php`, o sistema verifica o perfil do usuário logado (campo `role` na sessão):
  - Se for `admin`, utiliza `$adminRoutes`.
  - Caso contrário, utiliza `$userRoutes`.
- O roteador (`Core`) recebe apenas as rotas permitidas para o perfil atual.

### 3. Benefícios
- Usuários comuns não conseguem acessar rotas administrativas.
- Administradores têm acesso total às funcionalidades do sistema.
- O controle é feito de forma centralizada e fácil de manter.

---

Se precisar de mais detalhes sobre como expandir ou customizar os perfis, consulte este documento ou peça suporte!
