@echo off
echo 🧪 Executando testes unitários do VirtualLib...
echo ==============================================

REM Verifica se o PHPUnit está instalado
if not exist "vendor\bin\phpunit.bat" (
    echo ❌ PHPUnit não encontrado. Instalando...
    composer install
)

echo.
echo 📋 Resumo dos testes:
echo - Auth Service: UserModel (autenticação, criação de usuário, fallback)
echo - Auth Service: BorrowModel (solicitação, aprovação, devolução de empréstimos)
echo - Notifications Service: NotificationModel (sistema de notificações)
echo.

REM Executa os testes
echo 🚀 Executando testes...
vendor\bin\phpunit.bat --testdox

echo.
echo ✅ Testes concluídos!
pause
