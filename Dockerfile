FROM php:8.2-fpm-alpine 

RUN apk add --no-cache \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    unzip \
    bash 

RUN curl -sSLo /usr/local/bin/symfony https://github.com/symfony-cli/symfony-cli/releases/latest/download/symfony_linux_amd64 \
    && chmod +x /usr/local/bin/symfony

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader

CMD ["tail", "-f", "/dev/null"] 