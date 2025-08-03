#!/bin/bash

# Production Optimization Script for Target Management System
# This script optimizes the application for production deployment

echo "🚀 Starting Production Optimization..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
echo "🔧 Optimizing Composer autoloader..."
composer install --optimize-autoloader --no-dev

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "🔑 Copying environment file..."
    cp .env.production .env
fi

if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Run database migrations
echo "🗃️ Running database migrations..."
php artisan migrate --force

echo "✅ Production optimization complete!"
echo "🌐 Your application is ready for production deployment."
