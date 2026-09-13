FROM php:8.5-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    git \
    unzip \
    libzip-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    intl

# Install pcov (coverage) and redis (RedisCacher functional tests)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install pcov redis \
    && docker-php-ext-enable pcov redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

RUN composer install --no-interaction --no-progress

# Run the full test gate (PHPCS + PHPUnit) by default.
# pcov is available in the image for `composer test-cover`.
CMD ["composer", "test"]
