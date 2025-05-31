<?php
require_once 'config.php';
require_once 'classes/Exchange.php';
require_once 'classes/TonApi.php';

// Проверка наличия ID сессии
$sessionId = filter_input(INPUT_GET, 'session_id', FILTER_SANITIZE_STRING);

if (!$sessionId) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Отсутствует ID сессии']));
}

// Инициализация классов
$exchange = new Exchange();
$tonApi = new TonApi(TON_API_KEY);

// Получение данных сессии
$session = $exchange->getSession($sessionId);

if (!$session) {
    header('HTTP/1.1 404 Not Found');
    exit(json_encode(['error' => 'Сессия не найдена']));
}

// Проверка статуса обмена, если платеж был успешным
if ($session['payment_status'] === 'success') {
    // Обновление статуса обмена, если он еще обрабатывается
    if ($session['exchange_status'] === 'processing') {
        // Эмуляция случайного обновления статуса
        $rand = mt_rand(1, 10);
        
        if ($rand <= 3) { // 30% вероятность завершения обмена
            $newStatus = 'success';
        } else {
            $newStatus = 'processing';
        }
        
        if ($newStatus !== $session['exchange_status']) {
            $exchange->updateExchangeStatus($sessionId, $newStatus);
            $session['exchange_status'] = $newStatus;
        }
    }
}

// Формирование ответа
$response = [
    'payment_status' => $session['payment_status'],
    'exchange_status' => $session['exchange_status'],
    'updated_at' => date('Y-m-d H:i:s')
];

// Отправка ответа
header('Content-Type: application/json');
echo json_encode($response); 