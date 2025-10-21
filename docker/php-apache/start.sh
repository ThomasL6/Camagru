#!/bin/bash
# Configure ssmtp with environment variables
/usr/local/bin/configure-ssmtp.sh

# Create uploads directory and set proper permissions
mkdir -p /var/www/html/public/uploads/images

# Set ownership to www-data but with group write permissions
chown -R www-data:www-data /var/www/html/public/uploads

# 777 = full permissions for everyone (owner, group, others)
chmod -R 777 /var/www/html/public/uploads

# Set the umask so new files created by Apache are also accessible
# umask 000 means new files will have 777 permissions by default
umask 000

# Start Apache
apache2-foreground
