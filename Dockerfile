FROM php:8.2-apache

# Fix: empêcher Apache de charger plusieurs MPM
RUN a2dismod mpm_prefork mpm_worker mpm_event || true \
    && a2enmod mpm_prefork

# Dépendances système
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Extension MongoDB
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Modules Apache
RUN a2enmod rewrite headers

# Configuration Apache production
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configuration sécurité globale
COPY apache/security.conf /etc/apache2/conf-available/security.conf
RUN a2enconf security

# Configuration PHP production
COPY php/production.ini /usr/local/etc/php/conf.d/production.ini

# Fichiers de l'application
COPY ./www /var/www/html

# Dépendances PHP (PHPMailer) — installées dans www/vendor/
COPY composer.json composer.lock /var/www/html/
RUN composer install --no-dev --no-interaction --working-dir=/var/www/html

# Dossier uploads
RUN mkdir -p /var/www/html/images/profil \
    && chown -R www-data:www-data /var/www/html

# Entrypoint pour forcer le bon MPM au runtime
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
