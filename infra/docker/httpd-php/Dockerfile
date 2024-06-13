FROM php:8.2.8-apache

# timezone environment
ENV TZ=Asia/Tokyo \
  LANG=en_US.UTF-8 \
  LANGUAGE=en_US:en \
  LC_ALL=en_US.UTF-8 \
  COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_HOME=/composer \
  PHPREDIS_VERSION=5.3.5

RUN mkdir -p /usr/src/php/ext/redis
RUN curl -L https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1
RUN echo 'redis' >> /usr/src/php-available-exts
RUN docker-php-ext-install redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV PATH="/root/.composer/vendor/bin:${PATH}"

RUN apt-get update
RUN apt-get -y install git libicu-dev libonig-dev libzip-dev unzip locales gnupg
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libwebp-dev
RUN apt-get install -y imagemagick libmagickwand-dev
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install intl pdo_mysql zip bcmath exif opcache
RUN docker-php-ext-install pcntl
RUN rm -rf /var/lib/apt/lists/*

RUN locale-gen en_US.UTF-8
RUN localedef -f UTF-8 -i en_US en_US.UTF-8

RUN mkdir -p /tmp/cache/views && \
    chmod 777 -R /tmp/cache
RUN composer config -g process-timeout 3600
RUN composer config -g repos.packagist composer https://packagist.org

RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/*

COPY ./infra/docker/httpd-php/conf.d/apache2.conf /etc/apache2/apache2.conf
COPY ./infra/docker/httpd-php/conf.d/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./infra/docker/httpd-php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html/src/

# Apache Modules
RUN a2enmod deflate expires rewrite
