#!/usr/bin/env bash
set -e

echo "🎬 Deploying CinePulse (se_movie)..."

# Ensure SQLite database exists
mkdir -p database
touch database/database.sqlite

# Maintenance mode
(php artisan down --message "Deploying update. Back shortly.") || true

# Pull latest commits
git pull origin main

# Install production PHP dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Install NPM packages and build Vite assets
npm ci || npm install
npm run build

# Run migrations
php artisan migrate --force

# Optimize Laravel cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue if active
php artisan queue:restart || true

# Bring application back up
php artisan up

echo "✅ CinePulse deployed successfully!"
