FROM php:8.2-apache

# Mise à jour + installation des dépendances pour MongoDB + PDO MySQL
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Installation de l'extension MongoDB via PECL
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Copie de tes fichiers PHP dans le conteneur (optionnel si tu utilises un volume)
COPY ./www /var/www/html