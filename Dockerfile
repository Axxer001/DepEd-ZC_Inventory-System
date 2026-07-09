# ==============================================================================
# STAGE 1: Frontend asset builder
# ==============================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

# Define build arguments for Vite (required because Vite builds assets statically)
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

# Set environment variables for the build process
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

# Copy dependency definition files
COPY package.json package-lock.json ./

# Install npm dependencies
RUN npm ci

# Copy configuration files and assets
COPY vite.config.js tailwind.config.js* postcss.config.js* ./
COPY resources/ ./resources/
COPY public/ ./public/

# Build assets with Vite
RUN npm run build


# ==============================================================================
# STAGE 2: Composer dependency builder
# ==============================================================================
FROM composer:2.7 AS composer-builder
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install project vendor dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-plugins \
    --no-scripts


# ==============================================================================
# STAGE 3: Final application PHP-FPM runtime image
# ==============================================================================
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www/html

# Install required system packages
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    shadow

# Install PHP extensions using official extension installer for safety and reliability
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql gd zip bcmath exif pcntl opcache redis

# Copy production OPcache configuration
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy entrypoint script and make it executable (as root)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application source code
COPY --chown=www-data:www-data . .

# Copy vendor packages and compiled assets from previous builder stages
COPY --from=composer-builder --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build ./public/build

# Set permissions for storage & bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Switch to standard Alpine non-root user (www-data)
USER www-data

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]


# ==============================================================================
# STAGE 4: Final Nginx web server image
# ==============================================================================
FROM nginx:alpine AS webserver
WORKDIR /var/www/html

# Copy Nginx server configuration
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy public static files from frontend builder
COPY --from=frontend-builder /app/public /var/www/html/public
