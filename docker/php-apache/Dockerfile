FROM php:8.2-apache

# Installation des dépendances pour PHP + SSL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    libonig-dev \
    openssl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install mbstring

# Activer SSL
RUN a2enmod ssl rewrite

# Générer certificat auto-signé
RUN mkdir /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/apache-selfsigned.key \
    -out /etc/apache2/ssl/apache-selfsigned.crt \
    -subj "/C=FR/ST=IDF/L=Paris/O=42/OU=Camagru/CN=localhost"

# Configurer Apache uniquement en SSL
RUN echo '<IfModule mod_ssl.c>\n\
    <VirtualHost *:443>\n\
        ServerAdmin admin@localhost\n\
        DocumentRoot /var/www/html\n\
        SSLEngine on\n\
        SSLCertificateFile /etc/apache2/ssl/apache-selfsigned.crt\n\
        SSLCertificateKeyFile /etc/apache2/ssl/apache-selfsigned.key\n\
        <Directory /var/www/html>\n\
            Options Indexes FollowSymLinks\n\
            AllowOverride All\n\
            Require all granted\n\
        </Directory>\n\
    </VirtualHost>\n\
</IfModule>' > /etc/apache2/sites-available/ssl-only.conf \
    && a2ensite ssl-only \
    && a2dissite 000-default

# Permissions
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

EXPOSE 443
CMD ["apache2-foreground"]
