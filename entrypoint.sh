#!/bin/bash
set -e

# Railway injecte $PORT, sinon on utilise 80
PORT="${PORT:-80}"

# Adapter Apache au port dynamique
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Désactiver tous les MPM puis activer prefork (seul compatible avec PHP)
a2dismod mpm_prefork mpm_worker mpm_event || true
a2enmod mpm_prefork

exec apache2-foreground
