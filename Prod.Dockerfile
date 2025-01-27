FROM jkaninda/laravel-php-fpm:8.1

ENV WWWUSER=www-data
ENV WWWGROUP=www-data

# Copy Laravel project files
COPY . /var/www/html
VOLUME /var/www/html/storage
WORKDIR /var/www/html

# Fix permissions
RUN chown -R ${WWWUSER}:${WWWGROUP} /var/www/html && \
    chown -R ${WWWUSER}:${WWWGROUP} /var/log

USER ${WWWUSER}
