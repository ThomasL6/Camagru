FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip libonig-dev openssl ssmtp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mbstring

# Activer SSL et rewrite
RUN a2enmod ssl rewrite headers

# Générer un certificat SSL unique à chaque build avec timestamp complet
RUN mkdir -p /etc/apache2/ssl && \
    BUILD_TIME=$(date +%Y%m%d%H%M%S) && \
    RANDOM_ID=$(openssl rand -hex 6) && \
    SERIAL="${BUILD_TIME}${RANDOM_ID}" && \
    CN="camagru-${RANDOM_ID}-${BUILD_TIME}.localhost" && \
    echo "Generating SSL cert with CN=${CN} Serial=${SERIAL}" && \
    openssl req -x509 -nodes -days 1 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/apache-selfsigned.key \
    -out /etc/apache2/ssl/apache-selfsigned.crt \
    -subj "/C=FR/ST=Paris/L=Paris/O=Camagru-${BUILD_TIME}/OU=Dev-${RANDOM_ID}/CN=${CN}"

# Copier les scripts de configuration
COPY configure-ssmtp.sh /usr/local/bin/
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/configure-ssmtp.sh /usr/local/bin/start.sh

# Configuration PHP pour utiliser ssmtp
RUN echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/mail.ini

# Copier la configuration Apache personnalisée
COPY ssl-only.conf /etc/apache2/sites-available/ssl-only.conf
RUN a2ensite ssl-only && a2dissite 000-default

# Permissions
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

EXPOSE 443
CMD ["/usr/local/bin/start.sh"]
