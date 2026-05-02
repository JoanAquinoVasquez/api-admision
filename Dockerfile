FROM php:8.3-fpm

# Install system dependencies + Nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and install dependencies
# This leverages Docker cache for vendor folder
COPY composer.json composer.lock /var/www/
RUN composer install --no-scripts --no-autoloader

# Copy existing application directory contents
COPY . /var/www

# Finish composer
RUN composer dump-autoload --optimize

# Copy Nginx and PHP-FPM configs
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 8000
EXPOSE 8000

# Start Nginx + PHP-FPM
CMD ["/usr/local/bin/start.sh"]

