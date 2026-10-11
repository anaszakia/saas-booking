FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libpng-dev \
    zip unzip git curl \
    nodejs npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy & build NPM dulu
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# Install Laravel
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

EXPOSE 8080

CMD sh -c "php artisan migrate --force && php artisan db:seed --force && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"