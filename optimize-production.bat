@echo off
REM Production Optimization Script for Target Management System (Windows)
REM This script optimizes the application for production deployment

echo 🚀 Starting Production Optimization...

REM Clear all caches
echo 📦 Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Optimize for production
echo ⚡ Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM Optimize Composer autoloader
echo 🔧 Optimizing Composer autoloader...
composer install --optimize-autoloader --no-dev

REM Copy environment file if not exists
if not exist .env (
    echo 🔑 Copying environment file...
    copy .env.production .env
)

REM Generate application key if needed
findstr /C:"APP_KEY=" .env | findstr /C:"APP_KEY=$" >nul
if %errorlevel% equ 0 (
    echo 🔑 Generating application key...
    php artisan key:generate
)

REM Run database migrations
echo 🗃️ Running database migrations...
php artisan migrate --force

echo ✅ Production optimization complete!
echo 🌐 Your application is ready for production deployment.
pause
