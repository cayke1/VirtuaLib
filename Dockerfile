FROM php:8.2-apache


RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql


COPY . /var/www/html/


RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite


RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf


EXPOSE 80


CMD ["apache2-foreground"]