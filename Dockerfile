# Use official PHP 8.2 FPM image
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    npm \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring


# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Install Node packages & build assets
RUN npm install && npm run build

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port
EXPOSE 9000

# Run Laravel scheduler & queue (optional: use Render Background Worker)
CMD ["php-fpm"]
