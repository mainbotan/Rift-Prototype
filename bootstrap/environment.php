<?php

/*
 * |--------------------------------------------------------------------------
 * | Environment Setup
 * |--------------------------------------------------------------------------
 */

declare(strict_types=1);

// Загрузка .env в первую очередь
use Dotenv\Dotenv;
Dotenv::createImmutable(dirname(__DIR__, 1))->safeLoad();

// Настройки ошибок
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');

// Установка заголовков только для HTTP-запросов
if (php_sapi_name() !== 'cli') {
    header_remove('X-Powered-By');
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    
    if ($_ENV['APP_ENV'] === 'prod') {
        header('Strict-Transport-Security: max-age=63072000');
    }
}
