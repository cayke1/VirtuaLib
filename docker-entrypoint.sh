#!/bin/bash

# Configurar variáveis de ambiente baseadas no serviço
if [ -n "$SERVICE_NAME" ]; then
    echo "Configurando container para servico: $SERVICE_NAME"
    
    # Criar link simbólico para o serviço específico
    case $SERVICE_NAME in
        "auth")
            ln -sf /var/www/html/services/auth/index.php /var/www/html/index.php
            ;;
        "books")
            ln -sf /var/www/html/services/books/index.php /var/www/html/index.php
            ;;
        "notifications")
            ln -sf /var/www/html/services/notifications/index.php /var/www/html/index.php
            ;;
        "dashboard")
            ln -sf /var/www/html/services/dashboard/index.php /var/www/html/index.php
            ;;
        *)
            # API Gateway - usar o arquivo padrão
            echo "Usando API Gateway como padrao"
            ;;
    esac
else
    echo "Nenhum servico especifico configurado, usando API Gateway"
fi

# Executar comando original
exec "$@"
