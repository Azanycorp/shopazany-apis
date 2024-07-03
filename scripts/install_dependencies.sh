#!/bin/bash

# Fail the script if any command fails
set -e

echo "Updating package lists..."
sudo yum update -y

echo "Installing required packages..."
# Install PHP and necessary extensions
sudo amazon-linux-extras enable php8.3
sudo yum clean metadata
sudo yum install -y php php-fpm php-mysqlnd php-pdo php-gd php-mbstring

echo "Installing Composer..."
# Install Composer
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r 'echo hash_file("sha384", "composer-setup.php");')"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Install Laravel dependencies
echo "Installing Laravel dependencies..."
cd /var/www/AZANY-BE-2024
composer install --no-dev --optimize-autoloader

echo "Dependencies installed successfully."
