# Utilise PHP + Apache
FROM php:8.2-apache

# Dossier de travail
WORKDIR /var/www/html

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Active mod_rewrite pour les URLs propres
RUN a2enmod rewrite

# Copie tout le code dans le conteneur
COPY . /var/www/html

# Installe Composer
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Installe les dépendances Composer (si nécessaire)
RUN composer install --no-dev --optimize-autoloader
# Donne les permissions appropriées
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache          
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
# Expose le port 80
EXPOSE 80
# Démarre Apache en mode premier plan
CMD ["apache2-foreground"]  
# Utilise PHP + Apache
FROM php:8.2-apache 
# Dossier de travail
WORKDIR /var/www/html
# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql
# Active mod_rewrite pour les URLs propres
RUN a2enmod rewrite     

# Copie tout le code dans le conteneur
COPY . /var/www/html
# Installe Composer
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer             
# Installe les dépendances Composer (si nécessaire)
RUN composer install --no-dev --optimize-autoloader         

