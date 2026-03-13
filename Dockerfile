FROM php:8.2-cli-alpine AS deps

RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libpq-dev \
    unzip \
    git

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    libxml2-dev \
    curl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_pgsql curl dom xml

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./

RUN npm ci --prefer-offline

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

FROM php:8.2-fpm-alpine AS runtime

LABEL maintainer="thiagogritti"
LABEL description="URL Shortener – Laravel 12 production image"

RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    libzip \
    icu-libs \
    oniguruma \
    libpq \
    nginx \
    supervisor \
    curl

RUN apk add --no-cache --virtual .build-deps \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libpq-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

COPY . .

COPY --from=deps /app/vendor ./vendor

COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
