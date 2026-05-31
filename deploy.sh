#!/bin/bash
# Forge deployment script — paste this into Forge > Site > Deployment Script
# Forge runs this script from the release directory automatically.
# Web Directory in Forge must be set to: backend/public
# Linked Files in Forge must include: backend/.env
# Linked Directories in Forge must include: backend/storage

set -e

# Use relative path — Forge runs the script from the repo root of the release
cd backend

echo "==> Installing PHP dependencies..."
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader

echo "==> Running migrations..."
$FORGE_PHP artisan migrate --force

echo "==> Caching config, routes, views..."
$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache

echo "==> Linking storage..."
$FORGE_PHP artisan storage:link

echo "==> Restarting PHP-FPM..."
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service "$FORGE_PHP_FPM" restart ) 9>/tmp/fpmlock

echo "==> Backend deployment complete."
