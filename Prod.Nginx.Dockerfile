# Use the official Nginx image as a base image
FROM nginx:alpine

# Update and install necessary packages
RUN apk update && apk upgrade && \
    apk --update add logrotate openssl bash

# Clean up to reduce image size
RUN rm -rf /var/cache/apk/*

# Start Nginx when the container runs
CMD ["nginx", "-g", "daemon off;"]
