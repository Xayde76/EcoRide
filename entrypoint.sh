#!/bin/bash
set -e

# Désactiver tous les MPM
a2dismod mpm_prefork || true
a2dismod mpm_worker || true
a2dismod mpm_event || true

# Activer le seul MPM compatible avec PHP
a2enmod mpm_prefork

# Lancer Apache
exec apache2-foreground
