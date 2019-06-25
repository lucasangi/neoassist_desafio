FROM php:7.2.6-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y apt-utils git zip openssl libcurl4-openssl-dev pkg-config libssl-dev

RUN chown -R www-data:www-data /var/www/html 

RUN pecl install mongodb \
    &&  echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongo.ini

RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install 

COPY . ./

RUN composer update && composer dump-autoload -o && rm -rf /root/.composer