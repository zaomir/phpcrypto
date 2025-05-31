<?php
require_once 'config.php';
require_once 'classes/Exchange.php';
require_once 'classes/BankApi.php';
require_once 'classes/CryptoApi.php';

// Проверка наличия ID сессии
$sessionId = filter_input(INPUT_GET, 'session_id', FILTER_SANITIZE_STRING);

if (!$sessionId) {
    header('Location: index.php');
    exit;
}

// Инициализация классов
$exchange = new Exchange();
$bankApi = new BankApi(BANK_API_KEY);

// Получение данных сессии
$session = $exchange->getSession($sessionId);

if (!$session) {
    header('Location: index.php?error=session_not_found');
    exit;
}

// Инициализация API криптовалюты
$cryptoApi = new CryptoApi(CRYPTO_API_KEY, $session['currency']);

// Получение статуса ордера
$orderStatus = $exchange->getOrderStatus($session['order_id']);

// Обработка отправки формы
$paymentStatus = null;
$exchangeStatus = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Попытка выполнить перевод через API банка
    $paymentResult = $bankApi->processCardToCardPayment([
        'from_card' => $session['buyer_card'],
        'to_card' => $session['seller_card'],
        'amount' => $session['amount'],
        'description' => 'P2P обмен ' . $session['currency'] . ' #' . $session['order_id']
    ]);
    
    // Обновление статуса платежа
    $paymentStatus = $paymentResult['status'] ?? 'error';
    $exchange->updatePaymentStatus($sessionId, $paymentStatus);
    
    // Если платеж успешен, запрашиваем статус обмена криптовалюты
    if ($paymentStatus === 'success') {
        $exchangeResult = $cryptoApi->completeExchange($session['order_id'], $session['crypto_amount']);
        $exchangeStatus = $exchangeResult['status'] ?? 'processing';
        $exchange->updateExchangeStatus($sessionId, $exchangeStatus);
    }
}

// Получение актуальных статусов
if (!$paymentStatus) {
    $paymentStatus = $session['payment_status'] ?? 'pending';
}
if (!$exchangeStatus) {
    $exchangeStatus = $session['exchange_status'] ?? 'pending';
}

// Получение информации о валюте
$currencies = $exchange->getSupportedCurrencies();
$currencyInfo = $currencies[$session['currency']] ?? ['name' => $session['currency'], 'full_name' => $session['currency']];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение платежа | P2P Обмен</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Подтверждение платежа</h1>
        </header>
        
        <main>
            <div class="payment-details">
                <h2>Детали платежа</h2>
                <div class="details-box">
                    <p><strong>Валюта:</strong> <?= $currencyInfo['name'] ?> (<?= $currencyInfo['full_name'] ?>)</p>
                    <p><strong>Сумма:</strong> <?= number_format($session['amount'], 2) ?> ₽</p>
                    <p><strong>Курс обмена:</strong> <?= number_format($orderStatus['rate'], 2) ?> ₽/<?= $session['currency'] ?></p>
                    <p><strong>Количество <?= $session['currency'] ?>:</strong> <?= number_format($session['crypto_amount'], 6) ?></p>
                    <p><strong>Ваша карта:</strong> <?= maskCardNumber($session['buyer_card']) ?></p>
                    <p><strong>Карта продавца:</strong> <?= maskCardNumber($session['seller_card']) ?></p>
                </div>
                
                <div class="bank-form">
                    <h3>Оплата</h3>
                    <?php if ($paymentStatus === 'pending'): ?>
                    <form id="paymentForm" method="POST">
                        <div class="form-group">
                            <p>Для завершения платежа, нажмите кнопку ниже. Система автоматически выполнит перевод с вашей карты на карту продавца.</p>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="confirm_payment" class="btn-submit">Подтвердить оплату</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="status-box <?= $paymentStatus ?>">
                        <h4>Статус платежа: <?= getStatusText($paymentStatus) ?></h4>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($paymentStatus === 'success'): ?>
                <div class="exchange-status">
                    <h3>Статус обмена <?= $session['currency'] ?></h3>
                    <div class="status-box <?= $exchangeStatus ?>">
                        <h4>Статус обмена: <?= getStatusText($exchangeStatus) ?></h4>
                        <?php if ($exchangeStatus === 'success'): ?>
                        <p><?= $session['currency'] ?> успешно отправлен на ваш кошелек. Транзакция завершена.</p>
                        <?php elseif ($exchangeStatus === 'processing'): ?>
                        <p>Обмен находится в процессе обработки. Обновите страницу через несколько минут.</p>
                        <script>
                            setTimeout(function() { location.reload(); }, 30000); // Обновление через 30 секунд
                        </script>
                        <?php else: ?>
                        <p>Возникла проблема с обменом. Пожалуйста, свяжитесь с поддержкой.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="back-to-home">
                    <a href="index.php" class="btn-link">Вернуться на главную</a>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?= date('Y') ?> P2P Обмен Криптовалют. Все права защищены.</p>
        </footer>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Функция для маскирования номера карты
function maskCardNumber($cardNumber) {
    $cardNumber = str_replace(' ', '', $cardNumber);
    return substr($cardNumber, 0, 4) . ' **** **** ' . substr($cardNumber, -4);
}

// Функция для получения текстового описания статуса
function getStatusText($status) {
    $statuses = [
        'pending' => 'Ожидается',
        'processing' => 'Обрабатывается',
        'success' => 'Успешно',
        'error' => 'Ошибка',
        'failed' => 'Не удалось'
    ];
    
    return $statuses[$status] ?? 'Неизвестно';
}
?> 