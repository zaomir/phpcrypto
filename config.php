<?php
/**
 * Файл конфигурации
 */

// Режим отладки
define('DEBUG', true);

// API ключи
define('BANK_API_KEY', 'your_bank_api_key');
define('CRYPTO_API_KEY', 'your_crypto_api_key'); // Ключ для работы с криптовалютами

// Настройки базы данных
define('DB_PATH', __DIR__ . '/database/exchange.db');

// Настройки путей
define('LOG_PATH', __DIR__ . '/logs');
define('TEMPLATE_PATH', __DIR__ . '/templates');

// Настройки сессии
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Установите 1 для HTTPS

// Обработка ошибок
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

// Создание необходимых директорий
$directories = [
    dirname(DB_PATH),
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
} 