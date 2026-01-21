# Use the official Nginx image as a base image
FROM nginx:alpine

# Update and install necessary packages
RUN apk update && apk upgrade && \
    apk --update add logrotate openssl bash

# Clean up to reduce image size
RUN rm -rf /var/cache/apk/*

# Create directory structure
RUN mkdir -p /var/www/public /var/www/deployment/frontend

# Copy Laravel public directory (for serving static assets and index.php)
COPY --chown=nginx:nginx public /var/www/public

# Copy Angular frontend build
COPY --chown=nginx:nginx deployment/frontend /var/www/deployment/frontend

# Copy and set permissions for entrypoint script
COPY deployment/nginx/scripts/docker-entrypoint.sh /docker-entrypoint.d/50-configure-ssl.sh
RUN chmod +x /docker-entrypoint.d/50-configure-ssl.sh

# Start Nginx when the container runs
CMD ["nginx", "-g", "daemon off;"]
