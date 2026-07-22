#!/bin/sh

set -e

echo "========================================="
echo " Démarrage du conteneur PHP Symfony"
echo "========================================="

cd /var/www/html

mkdir -p var/cache
mkdir -p var/log
mkdir -p var/sessions

chmod -R 775 var || true

echo "Le conteneur PHP est prêt."

exec "$@"