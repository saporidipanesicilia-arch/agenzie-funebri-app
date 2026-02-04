#!/bin/sh
set -e

# Clear cache first to ensure we pick up Runtime ENV vars
php artisan config:clear

# Run migrations (force for production)
php artisan migrate --force

# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
apache2-foreground
