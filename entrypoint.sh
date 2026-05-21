#!/bin/bash
set -e

# Désactiver tous les MPM
a2dismod mpm_prefork || true
a2dismod mpm_worker || true
a2dismod mpm_event || true

# Activer un seul MPM
a2enmod mpm_event

# Lancer Apache
exec apache2-foreground
