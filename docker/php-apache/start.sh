#!/bin/bash
# Configurer ssmtp avec les variables d'environnement
/usr/local/bin/configure-ssmtp.sh

# Démarrer Apache
apache2-foreground
