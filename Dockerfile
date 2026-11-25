FROM php:8.3-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update the default Apache site config to use /var/www/html/public as DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set permissions for storage
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
