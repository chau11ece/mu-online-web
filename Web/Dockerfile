# DMNCMS / MuCMS Pro Dockerfile
# PHP 7.4 with IonCube Loader and FreeTDS for SQL Server

FROM php:7.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    gnupg \
    curl \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    freetds-dev \
    freetds-bin \
    unixodbc \
    unixodbc-dev \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions including PDO DBLIB for SQL Server
# First configure and install GD, then install other extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) zip pdo pdo_mysql

# Create symlink for libsybdb (FreeTDS library) in the standard location
RUN ln -s /usr/lib/x86_64-linux-gnu/libsybdb.so /usr/lib/libsybdb.so || true

# Install pdo_dblib separately (requires freetds-dev which was installed above)
RUN docker-php-ext-install pdo_dblib

# Install IonCube Loader for PHP 7.4
COPY ioncube_loader_lin_7.4.so /usr/local/lib/php/extensions/no-debug-non-zts-20190902/ioncube_loader_lin_7.4.so
RUN echo "zend_extension=ioncube_loader_lin_7.4.so" > /usr/local/etc/php/conf.d/ioncube.ini

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy existing application
COPY . /var/www/html/

# Install PHP dependencies with Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/application/data \
    && chmod -R 777 /var/www/html/application/config \
    && chmod -R 777 /var/www/html/application/logs

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
