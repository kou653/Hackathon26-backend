FROM php:8.2-apache

# Dépendances système nécessaires à gd
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Activer mod_rewrite (Laravel)
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
COPY . /var/www/html

WORKDIR /var/www/html

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
