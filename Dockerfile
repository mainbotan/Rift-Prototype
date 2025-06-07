FROM php:8.2-fpm

# Добавляем эти строки в начале
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt-get update && apt-get install -y \
    libpq-dev git unzip \
    && docker-php-ext-install pdo pdo_mysql

# Создаем пользователя и группу с указанными ID
RUN groupadd -g ${GROUP_ID} appuser \
    && useradd -u ${USER_ID} -g appuser -m appuser \
    && mkdir -p /app && chown appuser:appuser /app

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Переключаемся на пользователя appuser
USER appuser