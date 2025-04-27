#!/bin/bash

# Fail the script if any command fails
set -e
# Install Laravel dependencies
echo "Installing Laravel dependencies..."
cd /var/www/AZANY-BE-2024
/usr/local/bin/composer install --no-dev --optimize-autoloader

# generate app key
php artisan key:generate

# set permissions
chown -R nginx:nginx /var/www/AZANY-BE-2024
chmod -R 777 /var/www/AZANY-BE-2024
chmod -R 777 /var/www/AZANY-BE-2024/storage
chmod -R 777 /var/www/AZANY-BE-2024/bootstrap/cache

echo "Validating PHP-FPM service..."
if ! systemctl is-active --quiet php-fpm; then
    echo "PHP-FPM is not running. Starting PHP-FPM..."
    sudo systemctl start php-fpm
fi

echo "Validating Nginx service..."
if ! systemctl is-active --quiet nginx; then
    echo "Nginx is not running. Starting Nginx..."
    sudo systemctl start nginx
fi

echo "All services are running properly."
