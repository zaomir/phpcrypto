# P2P Криптовалютный обменник

Система для проведения P2P обменов криптовалюты через банковские карты.

## Основные функции

* Ввод суммы и выбор криптовалюты для покупки
* Ввод номера банковской карты
* Автоматический поиск подходящего ордера
* Проведение P2P перевода
* Получение подтверждения от биржи

## Технические требования

* PHP 7.4+
* MySQL/MariaDB
* Современный браузер с поддержкой JavaScript

## Установка

1. Клонировать репозиторий
2. Настроить базу данных (использовать скрипт в database/schema.sql)
3. Обновить настройки в config.php (API ключи и данные для доступа к БД)
4. Запустить init_test_data.php для инициализации тестовых данных
5. Запустить локальный сервер PHP или настроить веб-сервер

## Структура проекта

* `assets/` - статические файлы (CSS, JavaScript, изображения)
* `classes/` - классы PHP для работы с API, базой данных и обработки обменов
* `database/` - скрипты SQL для создания базы данных
* `logs/` - логи работы системы
* `index.php` - главная страница с формой
* `process.php` - обработка запросов на обмен
* `confirm.php` - подтверждение обмена
* `init_test_data.php` - инициализация тестовых данных

## Поддерживаемые криптовалюты

* TON (The Open Network)
* BTC (Bitcoin)
* ETH (Ethereum)

## Безопасность

* Система использует безопасные методы передачи и хранения данных
* Все транзакции логируются для аудита
* Реализована защита от основных видов атак 