#!/bin/bash
# Configure ssmtp with environment variables
/usr/local/bin/configure-ssmtp.sh

# Create uploads directory and set proper permissions
mkdir -p /var/www/html/public/uploads/images
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 755 /var/www/html/public/uploads

# Start Apache
apache2-foreground
