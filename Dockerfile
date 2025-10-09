FROM php:8.2-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libonig-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar Apache
RUN a2enmod rewrite headers proxy proxy_http \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar VirtualHost para SOA
COPY apache-soa.conf /etc/apache2/sites-available/000-default.conf

# Configurar entrypoint para diferentes serviços
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copiar arquivos da aplicação (exceto arquivos desnecessários)
COPY app/ /var/www/html/app/
COPY services/ /var/www/html/services/
COPY public/ /var/www/html/public/
COPY apache-soa.conf /var/www/html/
COPY docker-entrypoint.sh /var/www/html/

# Criar arquivo index.php básico
RUN echo "<?php echo 'Virtual Library API Gateway'; ?>" > /var/www/html/index.php

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
