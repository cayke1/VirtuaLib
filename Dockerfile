FROM php:8.2-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libonig-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    unzip \
    curl \
    git \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite headers proxy proxy_http \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar VirtualHost para SOA
COPY apache-soa.conf /etc/apache2/sites-available/000-default.conf

# Copiar arquivos do Composer primeiro
COPY composer.json composer.lock /var/www/html/

# Instalar dependências do Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar arquivos da aplicação
COPY services/ /var/www/html/services/
COPY public/ /var/www/html/public/
COPY apache-soa.conf /var/www/html/

# Garantir que o vendor seja preservado mesmo com volumes
RUN chown -R www-data:www-data /var/www/html/vendor

# Criar arquivo index.php básico
RUN echo "<?php echo 'Virtual Library API Gateway'; ?>" > /var/www/html/index.php

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Criar script de inicialização
RUN echo '#!/bin/bash' > /usr/local/bin/init-service.sh && \
    echo 'if [ -n "$SERVICE_NAME" ]; then' >> /usr/local/bin/init-service.sh && \
    echo '  echo "Configurando container para servico: $SERVICE_NAME"' >> /usr/local/bin/init-service.sh && \
    echo '  case $SERVICE_NAME in' >> /usr/local/bin/init-service.sh && \
    echo '    "auth") ln -sf /var/www/html/services/auth/index.php /var/www/html/index.php ;;' >> /usr/local/bin/init-service.sh && \
    echo '    "books") ln -sf /var/www/html/services/books/index.php /var/www/html/index.php ;;' >> /usr/local/bin/init-service.sh && \
    echo '    "notifications") ln -sf /var/www/html/services/notifications/index.php /var/www/html/index.php ;;' >> /usr/local/bin/init-service.sh && \
    echo '    "dashboard") ln -sf /var/www/html/services/dashboard/index.php /var/www/html/index.php ;;' >> /usr/local/bin/init-service.sh && \
    echo '    *) echo "Usando API Gateway como padrao" ;;' >> /usr/local/bin/init-service.sh && \
    echo '  esac' >> /usr/local/bin/init-service.sh && \
    echo 'else' >> /usr/local/bin/init-service.sh && \
    echo '  echo "Nenhum servico especifico configurado, usando API Gateway"' >> /usr/local/bin/init-service.sh && \
    echo 'fi' >> /usr/local/bin/init-service.sh && \
    echo 'exec "$@"' >> /usr/local/bin/init-service.sh && \
    chmod +x /usr/local/bin/init-service.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/init-service.sh"]
CMD ["apache2-foreground"]
