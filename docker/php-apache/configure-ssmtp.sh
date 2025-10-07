#!/bin/bash
cat > /etc/ssmtp/ssmtp.conf << EOF
root=${SMTP_FROM}
mailhub=${SMTP_HOST}:${SMTP_PORT}
hostname=camagru.com
AuthUser=${SMTP_USERNAME}
AuthPass=${SMTP_PASSWORD}
UseSTARTTLS=YES
FromLineOverride=YES
EOF
