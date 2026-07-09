#!/bin/sh
set -e

# Wait for the database connection using Laravel's connection configuration
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection ($DB_HOST)..."
    php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        
        for (\$i = 0; \$i < 30; \$i++) {
            try {
                Illuminate\Support\Facades\DB::connection()->getPdo();
                exit(0);
            } catch (Exception \$e) {
                echo 'Retrying database connection (' . (\$i + 1) . '/30)... ' . \$e->getMessage() . PHP_EOL;
                sleep(2);
            }
        }
        exit(1);
    "
fi

# Ensure Laravel storage and bootstrap cache directories exist
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage bootstrap/cache

# Create storage symlink
echo "Verifying storage symbolic link..."
php artisan storage:link --force || true

# Run database migrations if configured to do so
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Optimize Laravel for production (only for the main application container)
if [ "$1" = "php-fpm" ]; then
    echo "Optimizing Laravel config, routes, and views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Execute the container command
echo "Executing: $@"
exec "$@"
