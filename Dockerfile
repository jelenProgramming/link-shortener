# 8.4: the locked symfony 8 components require php >= 8.4.1
FROM php:8.4-cli

# reliable extension installer: pulls all system deps automatically
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_sqlite pdo_pgsql mbstring zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && cp -n .env.example .env \
    && touch database/database.sqlite \
    && mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan key:generate --force

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
