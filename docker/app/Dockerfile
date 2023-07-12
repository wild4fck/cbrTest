FROM php:8.2.4-fpm

RUN mkdir -p /usr/share/man/man1

# Install dependencies
RUN apt-get update && apt-get install -y \
    locales \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    libxml2-dev \
    libmemcached-dev \
    default-jdk \
    pdftk \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libfontconfig \
    libwebp-dev \
    libpng-dev

# Install extensions
RUN docker-php-ext-install bcmath mbstring zip opcache pdo_pgsql pgsql soap intl
RUN pecl install redis
RUN docker-php-ext-enable redis

# Install composer
RUN curl -fsSL https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer global require phpunit/phpunit ^9.0 --no-progress --no-scripts --no-interaction

# Dev
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs
RUN npm install --global yarn

RUN curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar && \
    mv phpcs.phar /usr/local/bin/phpcs && \
    chmod +x /usr/local/bin/phpcs

# Set working directory
WORKDIR /var/www

# User
ARG UID
ARG GID

RUN usermod -u ${UID} www-data
RUN groupmod -g ${GID} www-data

RUN usermod -aG www-data www-data
RUN chgrp www-data /var/www
RUN chmod g+rw /var/www

USER www-data
