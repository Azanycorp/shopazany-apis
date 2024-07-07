#!/bin/bash

# Fail the script if any command fails
set -e
sudo su
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