<?php
require_once 'config.php';
require_once 'classes/Exchange.php';
require_once 'classes/CryptoApi.php';

// Проверка запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Метод не разрешен');
}

// Получение и валидация данных
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$cardNumber = filter_input(INPUT_POST, 'card_number', FILTER_SANITIZE_STRING);
$currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING) ?: 'TON';

// Валидация данных
if (!$amount || $amount < 1000 || !preg_match('/^\d{16}$/', str_replace(' ', '', $cardNumber))) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Неверные данные. Проверьте сумму и номер карты.']));
}

// Инициализация классов
$exchange = new Exchange();
$cryptoApi = new CryptoApi(CRYPTO_API_KEY, $currency);

// Проверка минимальной суммы для выбранной валюты
$minAmount = $cryptoApi->getMinAmount();
if ($amount < $minAmount) {
    exit(json_encode([
        'error' => "Минимальная сумма для $currency: " . number_format($minAmount, 0, '.', ' ') . " ₽"
    ]));
}

try {
    // Поиск подходящего ордера
    $order = $exchange->findOrder($amount, $currency);
    
    if (!$order) {
        exit(json_encode(['error' => "Подходящий ордер для $currency не найден. Попробуйте изменить сумму или выбрать другую валюту."]));
    }
    
    // Запрос данных продавца
    $sellerData = $exchange->requestSellerCardDetails($order['id']);
    
    if (!$sellerData || empty($sellerData['card_number'])) {
        exit(json_encode(['error' => 'Не удалось получить данные продавца. Попробуйте позже.']));
    }
    
    // Создание сессии обмена
    $sessionId = $exchange->createExchangeSession([
        'order_id' => $order['id'],
        'buyer_card' => $cardNumber,
        'seller_card' => $sellerData['card_number'],
        'amount' => $amount,
        'crypto_amount' => $order['crypto_amount'],
        'currency' => $currency
    ]);
    
    // Отправка данных для подтверждения
    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'order' => [
            'id' => $order['id'],
            'amount' => $amount,
            'crypto_amount' => $order['crypto_amount'],
            'rate' => $order['rate'],
            'currency' => $currency
        ],
        'seller_card' => maskCardNumber($sellerData['card_number']),
        'next_step_url' => "confirm.php?session_id={$sessionId}"
    ]);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]));
}

// Функция для маскирования номера карты
function maskCardNumber($cardNumber) {
    $cardNumber = str_replace(' ', '', $cardNumber);
    return substr($cardNumber, 0, 4) . ' **** **** ' . substr($cardNumber, -4);
} 