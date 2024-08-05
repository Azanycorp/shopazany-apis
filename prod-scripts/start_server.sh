#!/bin/bash

# Fail the script if any command fails
set -e
sudo su
# update code
cd /var/www/AZANY-BE-2024

echo "Updating Git remote URL to use PAT..."
git remote set-url origin https://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@github.com/Azanycorp/shopazany-apis.git

echo "Cleaning up untracked files..."
git clean -fd

echo "Stashing local changes..."
git stash --include-untracked

git pull origin deploy --rebase

# composer update
composer update

# composer update
composer update

# clear php cache
php artisan optimize

# download env from s3
aws s3 cp s3://azany-env/prod/be.env ./.env

# Migrate DB tables
php artisan migrate --force

# activate cron jobs
php artisan schedule:run

echo "Starting PHP-FPM service..."
sudo systemctl start php-fpm

echo "Starting Nginx service..."
sudo systemctl start nginx

echo "Server started successfully."
