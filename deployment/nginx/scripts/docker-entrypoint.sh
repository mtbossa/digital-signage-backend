#!/bin/sh
set -e

# Default certificate name if not provided
SSL_CERT_NAME=${SSL_CERT_NAME:-cloudflare-paroquia-piox.app.br}

echo "Configuring nginx with SSL certificate: ${SSL_CERT_NAME}"

# Replace SSL_CERT_NAME placeholder in all nginx configs
find /etc/nginx/conf.d/ -type f -name "*.conf" -exec sed -i "s/SSL_CERT_NAME/${SSL_CERT_NAME}/g" {} \;

# Test nginx configuration
nginx -t

# Execute the main command (start nginx)
exec "$@"
