FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/agrosangapati agrosangapati
RUN mkdir -p /home/agrosangapati/.composer && \
    chown -R agrosangapati:agrosangapati /home/agrosangapati

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

USER agrosangapati

EXPOSE 9000
CMD ["php-fpm"]
