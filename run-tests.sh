#!/bin/bash
# Script para executar os testes unitários

echo "🧪 Executando testes unitários do VirtualLib..."
echo "=============================================="

# Verifica se o PHPUnit está instalado
if ! command -v ./vendor/bin/phpunit &> /dev/null; then
    echo "❌ PHPUnit não encontrado. Instalando..."
    composer install
fi

echo ""
echo "📋 Resumo dos testes:"
echo "- Auth Service: UserModel (autenticação, criação de usuário, fallback)"
echo "- Auth Service: BorrowModel (solicitação, aprovação, devolução de empréstimos)"
echo "- Notifications Service: NotificationModel (sistema de notificações)"
echo ""

# Executa os testes
echo "🚀 Executando testes..."
./vendor/bin/phpunit --testdox

echo ""
echo "✅ Testes concluídos!"
