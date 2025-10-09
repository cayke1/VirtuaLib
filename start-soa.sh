#!/bin/bash

# Script para iniciar a estrutura SOA
echo "🚀 Iniciando Virtual Library SOA..."

# Configurar ambiente se necessário
if [ ! -f .env ]; then
    echo "🔧 .env não encontrado."
    exit 1
fi

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker primeiro."
    exit 1
fi

# Parar containers existentes
echo "🛑 Parando containers existentes..."
docker-compose -f docker-compose.yml down

# Construir e iniciar os serviços
echo "🔨 Construindo e iniciando serviços SOA..."
docker-compose -f docker-compose.yml up --build -d

# Aguardar os serviços iniciarem
echo "⏳ Aguardando serviços iniciarem..."
sleep 10

# Verificar status dos serviços
echo "📊 Status dos serviços:"
docker-compose -f docker-compose.yml ps

echo ""
echo "✅ Estrutura SOA iniciada com sucesso!"
echo ""
echo "🌐 Serviços disponíveis:"
echo "   • API Gateway: http://localhost:8080"
echo "   • Auth Service: http://localhost:8081"
echo "   • Books Service: http://localhost:8082"
echo "   • Notifications Service: http://localhost:8083"
echo "   • Dashboard Service: http://localhost:8084"
echo "   • MySQL Database: localhost:3306"
echo ""
echo "🔧 Para parar os serviços:"
echo "   docker-compose -f docker-compose.yml down"
echo ""
echo "📝 Logs dos serviços:"
echo "   docker-compose -f docker-compose.yml logs -f"
