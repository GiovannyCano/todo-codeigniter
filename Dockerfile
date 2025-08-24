FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libonig-dev libpq-dev \
 && docker-php-ext-install intl mbstring mysqli pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data || true

COPY docker/app/entrypoint.sh /usr/local/bin/ci-boot.sh
RUN sed -i 's/\r$//' /usr/local/bin/ci-boot.sh && chmod +x /usr/local/bin/ci-boot.sh

ENTRYPOINT ["ci-boot.sh"]
CMD ["php-fpm"]
