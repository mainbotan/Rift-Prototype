FROM php:8.2-fpm

# Аргументы для пользователя
ARG USER_ID=1000
ARG GROUP_ID=1000

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip

# Установка Redis расширения (новый способ)
RUN pecl install redis && \
    docker-php-ext-enable redis

# Создание пользователя
RUN groupadd -g ${GROUP_ID} appuser \
    && useradd -u ${USER_ID} -g appuser -m appuser \
    && mkdir -p /app && chown appuser:appuser /app

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
USER appuser