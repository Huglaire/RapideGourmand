#!/bin/sh

set -e

echo "========================================="
echo " Démarrage du conteneur Symfony"
echo "========================================="

cd /var/www/html

echo "Configuration des clés JWT..."

mkdir -p config/jwt

if [ -n "$JWT_PRIVATE_KEY" ]; then
    printf '%s' "$JWT_PRIVATE_KEY" \
    | sed 's/-----BEGIN ENCRYPTED PRIVATE KEY----- /-----BEGIN ENCRYPTED PRIVATE KEY-----\n/' \
    | sed 's/ -----END ENCRYPTED PRIVATE KEY-----/\n-----END ENCRYPTED PRIVATE KEY-----/' \
    > config/jwt/private.pem

    chown www-data:www-data config/jwt/private.pem
    chmod 640 config/jwt/private.pem

    echo "Clé privée JWT générée."
fi

if [ -n "$JWT_PUBLIC_KEY_CONTENT" ]; then
    printf '%s' "$JWT_PUBLIC_KEY_CONTENT" \
    | sed 's/-----BEGIN PUBLIC KEY----- /-----BEGIN PUBLIC KEY-----\n/' \
    | sed 's/ -----END PUBLIC KEY-----/\n-----END PUBLIC KEY-----/' \
    > config/jwt/public.pem

    chown www-data:www-data config/jwt/public.pem
    chmod 644 config/jwt/public.pem

    echo "Clé publique JWT générée."
fi

# Configuration JWT pour Symfony/Lexik
cat > .env.local <<EOF
APP_ENV=prod
APP_DEBUG=0

JWT_PRIVATE_KEY=/var/www/html/config/jwt/private.pem
JWT_PUBLIC_KEY_CONTENT=/var/www/html/config/jwt/public.pem
JWT_PASSPHRASE=${JWT_PASSPHRASE}
EOF

echo "Configuration JWT terminée."

# Vérification configuration JWT
echo "Vérification configuration JWT..."
php bin/console debug:config lexik_jwt_authentication --env=prod || true

# Nettoyage du cache Symfony pour prendre en compte la configuration
echo "Vidage du cache Symfony..."
rm -rf var/cache/prod

# Préparation des dossiers Symfony
mkdir -p var/cache var/log var/sessions

chown -R www-data:www-data var || true
chmod -R 775 var || true

# Si Railway fournit un port, on l'injecte dans la configuration nginx
if [ -n "$PORT" ]; then
    echo "Utilisation du port Railway : $PORT"
    sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/conf.d/default.conf
fi

echo "========================================="
echo " Lancement de Supervisor"
echo "========================================="

exec "$@"