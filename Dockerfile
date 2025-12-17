FROM php:8.2-apache

WORKDIR /var/www/html

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Active mod_rewrite pour les URLs propres et mod_headers pour les en-têtes HTTP
RUN a2enmod rewrite headers

# Configure Apache pour autoriser .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Installe les dépendances système et Composer
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installe Xdebug pour la couverture de code
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Crée les dossiers nécessaires pour éviter l’erreur
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# Copie tout le code dans le conteneur
COPY . /var/www/html

# Installe les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Donne les permissions appropriées
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]