#!/bin/bash

# Fail the script if any command fails
set -e
sudo su
# update code
cd /var/www/AZANY-BE-2024

echo "Cleaning up untracked files..."
git clean -fd

echo "Stashing local changes..."
git stash --include-untracked

git pull origin deploy

# clear php cache
php artisan cache:clear

# Migrate DB tables
php artisan migrate --force

echo "Starting PHP-FPM service..."
sudo systemctl start php-fpm

echo "Starting Nginx service..."
sudo systemctl start nginx

echo "Server started successfully."
