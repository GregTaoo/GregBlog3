FROM php:7.4-apache

RUN docker-php-ext-install mysqli
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/
COPY server/EmotionsBackup.json /var/www/html/config/Emotions.json

COPY _installer/init_config.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/init_config.sh

WORKDIR /var/www/html/public

RUN /usr/local/bin/init_config.sh

EXPOSE 80
