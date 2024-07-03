#!/bin/bash

# Fail the script if any command fails
set -e

# clear php cache
cd /var/www/AZANY-BE-2024
php artisan cache:clear

# Migrate DB tables
php artisan migrate --force

echo "Starting PHP-FPM service..."
sudo systemctl start php-fpm

echo "Starting Nginx service..."
sudo systemctl start nginx

echo "Server started successfully."
