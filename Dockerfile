# Dockerfile
FROM php:7.4-apache

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libmagickwand-dev --no-install-recommends && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd && \
    pecl install imagick && docker-php-ext-enable imagick && \
    apt-get clean

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies
RUN composer install

# Copy Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod cache expires

# Copy the Apache configuration file
COPY apache-config.conf /etc/apache2/conf-available/

# Enable the configuration
RUN a2enconf apache-config

# Expose port 80 and start Apache server
EXPOSE 80
CMD ["apache2-foreground"]