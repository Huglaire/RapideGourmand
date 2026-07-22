#!/bin/sh

set -e

echo "========================================="
echo " Démarrage du conteneur Symfony"
echo "========================================="

cd /var/www/html

# Préparation des dossiers Symfony
mkdir -p var/cache var/log var/sessions

chown -R www-data:www-data var || true
chmod -R 775 var || true

# Si Railway fournit un port, on l'injecte dans la config nginx
if [ -n "$PORT" ]; then
    echo "Utilisation du port Railway : $PORT"
    sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/conf.d/default.conf
fi

echo "========================================="
echo " Lancement de Supervisor"
echo "========================================="

exec "$@"