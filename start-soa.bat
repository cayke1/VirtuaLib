@echo off
echo 🚀 Iniciando Virtual Library SOA...

REM Configurar ambiente se necessário
if not exist .env (
    echo ❌ .env não encontrado.
    pause
    exit /b 1
)

REM Verificar se o Docker está rodando
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker não está rodando. Por favor, inicie o Docker primeiro.
    pause
    exit /b 1
)

REM Parar containers existentes
echo 🛑 Parando containers existentes...
docker-compose -f docker-compose.yml down

REM Construir e iniciar os serviços
echo 🔨 Construindo e iniciando serviços SOA...
docker-compose -f docker-compose.yml up --build -d

REM Aguardar os serviços iniciarem
echo ⏳ Aguardando serviços iniciarem...
timeout /t 10 /nobreak >nul

REM Verificar status dos serviços
echo 📊 Status dos serviços:
docker-compose -f docker-compose.yml ps

echo.
echo ✅ Estrutura SOA iniciada com sucesso!
echo.
echo 🌐 Serviços disponíveis:
echo    • API Gateway: http://localhost:8080
echo    • Auth Service: http://localhost:8081
echo    • Books Service: http://localhost:8082
echo    • Notifications Service: http://localhost:8083
echo    • Dashboard Service: http://localhost:8084
echo    • MySQL Database: localhost:3306
echo.
echo 🔧 Para parar os serviços:
echo    docker-compose -f docker-compose.yml down
echo.
echo 📝 Logs dos serviços:
echo    docker-compose -f docker-compose.yml logs -f
echo.
pause
