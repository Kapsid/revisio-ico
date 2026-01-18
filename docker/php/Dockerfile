# PHP 8.4 FPM Alpine - Lightweight and fast
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    icu-dev \
    linux-headers \
    tidyhtml-dev \
    $PHPIZE_DEPS

# Install PHP extensions required by Laravel 12
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        intl \
        opcache \
        tidy

# Install SOAP extension (needed for some registry APIs)
RUN apk add --no-cache libxml2-dev \
    && docker-php-ext-install soap

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# Set proper permissions
RUN chown -R www:www /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]
