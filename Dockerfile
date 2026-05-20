FROM php:8.2-apache

# Dépendances système
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Extension MongoDB
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Modules Apache
RUN a2enmod rewrite headers

# Configuration Apache production
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configuration PHP production
COPY php/production.ini /usr/local/etc/php/conf.d/production.ini

# Fichiers de l'application
COPY ./www /var/www/html

# Dossier uploads (sera monté par le volume Fly.io)
RUN mkdir -p /var/www/html/images/profil \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
