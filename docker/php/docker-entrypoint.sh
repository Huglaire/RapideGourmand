#!/bin/sh

set -e

echo "========================================="
echo " Démarrage du conteneur Symfony"
echo "========================================="

cd /var/www/html

echo "Configuration des clés JWT..."

mkdir -p config/jwt

if [ -n "$JWT_PRIVATE_KEY" ]; then
    printf "%s" "$JWT_PRIVATE_KEY" > config/jwt/private.pem
    chmod 600 config/jwt/private.pem
    echo "Clé privée JWT générée."
fi

if [ -n "$JWT_PUBLIC_KEY_CONTENT" ]; then
    printf "%s" "$JWT_PUBLIC_KEY_CONTENT" > config/jwt/public.pem
    chmod 644 config/jwt/public.pem
    echo "Clé publique JWT générée."
fi

# Configuration JWT pour Symfony/Lexik
cat > .env.local <<EOF
APP_ENV=prod
APP_DEBUG=0

JWT_SECRET_KEY=/var/www/html/config/jwt/private.pem
JWT_PUBLIC_KEY=/var/www/html/config/jwt/public.pem
JWT_PASSPHRASE=${JWT_PASSPHRASE}
EOF

echo "Configuration JWT terminée."

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