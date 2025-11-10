#!/bin/bash

# Script para inicializar certificados SSL com Let's Encrypt
# Uso: ./init-letsencrypt.sh [staging|production|self-signed]

set -e

MODE=${1:-self-signed}
DOMAIN=${DOMAIN:-localhost}
EMAIL=${SSL_EMAIL:-admin@example.com}

echo "==================================="
echo "Inicializando certificados SSL"
echo "Modo: $MODE"
echo "Domínio: $DOMAIN"
echo "Email: $EMAIL"
echo "==================================="

# Criar diretórios necessários
mkdir -p certbot/conf/live/virtualib
mkdir -p certbot/www

case $MODE in
  production)
    echo "→ Obtendo certificado PRODUCTION do Let's Encrypt..."

    # Validar que não estamos usando localhost em produção
    if [ "$DOMAIN" = "localhost" ]; then
      echo "❌ ERRO: Não é possível usar 'localhost' em modo production."
      echo "   Configure a variável DOMAIN com seu domínio real."
      exit 1
    fi

    # Subir apenas nginx e certbot temporariamente
    docker-compose up -d nginx

    # Obter certificado
    docker-compose run --rm certbot certonly \
      --webroot \
      --webroot-path=/var/www/certbot \
      --email $EMAIL \
      --agree-tos \
      --no-eff-email \
      -d $DOMAIN

    echo "✓ Certificado obtido com sucesso!"
    ;;

  staging)
    echo "→ Obtendo certificado STAGING do Let's Encrypt (para testes)..."

    if [ "$DOMAIN" = "localhost" ]; then
      echo "❌ ERRO: Não é possível usar 'localhost' em modo staging."
      echo "   Configure a variável DOMAIN com seu domínio real."
      exit 1
    fi

    docker-compose up -d nginx

    docker-compose run --rm certbot certonly \
      --webroot \
      --webroot-path=/var/www/certbot \
      --email $EMAIL \
      --agree-tos \
      --no-eff-email \
      --staging \
      -d $DOMAIN

    echo "✓ Certificado de teste obtido com sucesso!"
    ;;

  self-signed)
    echo "→ Criando certificado autoassinado para desenvolvimento..."

    # Criar certificado autoassinado
    docker run --rm -v "$(pwd)/certbot/conf:/etc/letsencrypt" \
      alpine/openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
      -keyout /etc/letsencrypt/live/virtualib/privkey.pem \
      -out /etc/letsencrypt/live/virtualib/fullchain.pem \
      -subj "/C=BR/ST=State/L=City/O=VirtuaLib/CN=$DOMAIN"

    echo "✓ Certificado autoassinado criado com sucesso!"
    echo "⚠️  ATENÇÃO: Certificados autoassinados não são confiáveis para produção."
    ;;

  *)
    echo "❌ Modo inválido: $MODE"
    echo "Modos disponíveis: production, staging, self-signed"
    exit 1
    ;;
esac

echo ""
echo "==================================="
echo "✓ Inicialização concluída!"
echo "==================================="
echo ""
echo "Próximos passos:"
echo "1. Verifique os certificados em: ./certbot/conf/live/virtualib/"
echo "2. Inicie todos os serviços: docker-compose up -d"
echo "3. Acesse: https://$DOMAIN"
echo ""

if [ "$MODE" = "self-signed" ]; then
  echo "Nota: Seu navegador irá mostrar um aviso de segurança."
  echo "      Isso é normal para certificados autoassinados em desenvolvimento."
  echo ""
fi
