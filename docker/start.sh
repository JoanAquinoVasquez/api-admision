#!/bin/bash
# Startup script: arranca PHP-FPM + Nginx

echo "=== Iniciando PHP-FPM + Nginx ==="

# Asegurar permisos de storage y cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null

# Cachear rutas y config de Laravel para mejor performance
php artisan config:cache 2>/dev/null
php artisan route:cache 2>/dev/null

# Iniciar PHP-FPM en background
php-fpm -D

echo "PHP-FPM iniciado con $(grep 'pm.max_children' /usr/local/etc/php-fpm.d/www.conf | head -1) workers"

# Iniciar Nginx en foreground (mantiene el container vivo)
nginx -g 'daemon off;'
