<?php
require_once 'config.php';
require_once 'classes/Exchange.php';

// Получение списка поддерживаемых валют
$exchange = new Exchange();
$currencies = $exchange->getSupportedCurrencies();

// Установка валюты по умолчанию (TON)
$defaultCurrency = 'TON';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P2P Обмен Криптовалют</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>P2P Обмен Криптовалют</h1>
        </header>
        
        <main>
            <div class="exchange-form">
                <h2>Введите данные для обмена</h2>
                <form id="p2pForm" action="process.php" method="POST">
                    <div class="form-group">
                        <label for="currency">Выберите криптовалюту:</label>
                        <select id="currency" name="currency" required>
                            <?php foreach ($currencies as $code => $currency): ?>
                            <option value="<?= $code ?>" <?= $code === $defaultCurrency ? 'selected' : '' ?> 
                                   data-min="<?= $currency['min_amount'] ?>">
                                <?= $currency['name'] ?> (<?= $currency['full_name'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Сумма (₽):</label>
                        <input type="number" id="amount" name="amount" required min="1000" step="100">
                        <small id="min-amount-notice">Минимальная сумма: 1000 ₽</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Номер карты:</label>
                        <input type="text" id="card_number" name="card_number" required 
                               pattern="\d{16}" placeholder="XXXX XXXX XXXX XXXX">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Найти ордер</button>
                    </div>
                </form>
            </div>
            
            <div id="result" class="result-container"></div>
            
            <div class="crypto-info">
                <h3>Поддерживаемые криптовалюты</h3>
                <div class="crypto-list">
                    <?php foreach ($currencies as $code => $currency): ?>
                    <div class="crypto-item">
                        <div class="crypto-icon">
                            <img src="<?= $currency['logo'] ?>" alt="<?= $currency['name'] ?>" onerror="this.src='assets/img/crypto-default.png'">
                        </div>
                        <div class="crypto-details">
                            <h4><?= $currency['name'] ?></h4>
                            <p><?= $currency['full_name'] ?></p>
                            <p>Минимальная сумма: <?= number_format($currency['min_amount'], 0, '.', ' ') ?> ₽</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
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