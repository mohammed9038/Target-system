#!/bin/bash

# Professional Laravel Deployment Script
# Target Management System v2.0

set -e

echo "🚀 Starting professional deployment process..."

# Configuration
APP_NAME="Target Management System"
BACKUP_DIR="/var/backups/target-system"
LOG_FILE="/var/log/target-deployment.log"

# Functions
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

error() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" | tee -a $LOG_FILE
    exit 1
}

# Pre-deployment checks
log "Performing pre-deployment checks..."

# Check PHP version
php_version=$(php -r "echo PHP_VERSION;")
log "PHP Version: $php_version"

# Check Laravel installation
if [ ! -f "artisan" ]; then
    error "Laravel artisan file not found. Are you in the correct directory?"
fi

# Check environment file
if [ ! -f ".env" ]; then
    error "Environment file (.env) not found"
fi

# Database backup
log "Creating database backup..."
mkdir -p $BACKUP_DIR
mysqldump -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > "$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

# Maintenance mode
log "Enabling maintenance mode..."
php artisan down --render="errors::503" --secret="deployment-key-2024"

# Update code (if using Git)
if [ -d ".git" ]; then
    log "Pulling latest changes from Git..."
    git pull origin main
fi

# Install/Update dependencies
log "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run database migrations
log "Running database migrations..."
php artisan migrate --force

# Clear all caches
log "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan queue:restart

# Optimize for production
log "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set proper permissions
log "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Run tests (optional)
if [ "$RUN_TESTS" = "true" ]; then
    log "Running test suite..."
    php artisan test --stop-on-failure
fi

# Disable maintenance mode
log "Disabling maintenance mode..."
php artisan up

# Final checks
log "Running post-deployment health checks..."
curl -f http://localhost/health || error "Health check failed"

log "✅ Deployment completed successfully!"
log "Application is now live and accessible"

# Send notification (optional)
if [ ! -z "$SLACK_WEBHOOK" ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"🚀 $APP_NAME deployed successfully at $(date)\"}" \
        $SLACK_WEBHOOK
fi

echo "🎉 Deployment finished! Check $LOG_FILE for detailed logs."
