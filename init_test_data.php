<?php
require_once 'config.php';
require_once 'classes/Exchange.php';

// Сообщение об успешной инициализации
$successMessage = 'Тестовые данные успешно инициализированы!';

try {
    // Инициализация класса обмена
    $exchange = new Exchange();
    
    // Подключение к базе данных
    $db = new PDO(
        'sqlite:' . dirname(__FILE__) . '/database/exchange.db',
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Очистка существующих данных
    $db->exec("DELETE FROM users");
    $db->exec("DELETE FROM orders");
    $db->exec("DELETE FROM exchange_sessions");
    $db->exec("DELETE FROM sqlite_sequence WHERE name IN ('users', 'orders', 'exchange_sessions')");
    
    // Добавление тестовых пользователей
    $users = [
        [
            'name' => 'Иван Иванов', 
            'card_number' => '4276123456789012', 
            'ton_address' => 'EQBsjE-XmmkQ3MiJiHa7Kz-glCv_VRNYr3l51wEN7_yPC0ae',
            'btc_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
            'eth_address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
            'usdt_address' => 'TKrw8vMpVLEGGGJG1sywbPGGNCF2afjktL'
        ],
        [
            'name' => 'Петр Петров', 
            'card_number' => '4276234567890123', 
            'ton_address' => 'EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N',
            'btc_address' => '3J98t1WpEZ73CNmQviecrnyiWrnqRhWNLy',
            'eth_address' => '0x8Ba1f109551bD432803012645Ac136ddd64DBA72',
            'usdt_address' => 'TUrMmF9Gd4rzrXsQ34ui3Wou94E7HFqbNv'
        ],
        [
            'name' => 'Анна Сидорова', 
            'card_number' => '4276345678901234', 
            'ton_address' => 'EQDd8BgSS9xBRwsUYpU9QeQCBj8zqwJsrPnYEEG66jyLvXQi',
            'btc_address' => 'bc1qar0srrr7xfkvy5l643lydnw9re59gtzzwf5mdq',
            'eth_address' => '0xBE0eB53F46cd790Cd13851d5EFf43D12404d33E8',
            'usdt_address' => 'TGjYzgCyPNoFjHd73aYmL8XSJy6tky5zJy'
        ]
    ];
    
    $insertUserStmt = $db->prepare("
        INSERT INTO users (name, card_number, ton_address, btc_address, eth_address, usdt_address) 
        VALUES (:name, :card_number, :ton_address, :btc_address, :eth_address, :usdt_address)
    ");
    
    foreach ($users as $user) {
        $insertUserStmt->bindParam(':name', $user['name']);
        $insertUserStmt->bindParam(':card_number', $user['card_number']);
        $insertUserStmt->bindParam(':ton_address', $user['ton_address']);
        $insertUserStmt->bindParam(':btc_address', $user['btc_address']);
        $insertUserStmt->bindParam(':eth_address', $user['eth_address']);
        $insertUserStmt->bindParam(':usdt_address', $user['usdt_address']);
        $insertUserStmt->execute();
    }
    
    // Добавление тестовых ордеров
    $orders = [
        // TON ордера
        ['amount' => 5000, 'crypto_amount' => 14.285, 'rate' => 350, 'currency' => 'TON', 'seller_id' => 1],
        ['amount' => 10000, 'crypto_amount' => 28.571, 'rate' => 350, 'currency' => 'TON', 'seller_id' => 2],
        ['amount' => 15000, 'crypto_amount' => 42.857, 'rate' => 350, 'currency' => 'TON', 'seller_id' => 3],
        ['amount' => 20000, 'crypto_amount' => 58.823, 'rate' => 340, 'currency' => 'TON', 'seller_id' => 1],
        ['amount' => 25000, 'crypto_amount' => 75.757, 'rate' => 330, 'currency' => 'TON', 'seller_id' => 2],
        
        // BTC ордера
        ['amount' => 50000, 'crypto_amount' => 0.01, 'rate' => 5000000, 'currency' => 'BTC', 'seller_id' => 1],
        ['amount' => 100000, 'crypto_amount' => 0.02, 'rate' => 5000000, 'currency' => 'BTC', 'seller_id' => 2],
        ['amount' => 150000, 'crypto_amount' => 0.03, 'rate' => 5000000, 'currency' => 'BTC', 'seller_id' => 3],
        
        // ETH ордера
        ['amount' => 30000, 'crypto_amount' => 0.1, 'rate' => 300000, 'currency' => 'ETH', 'seller_id' => 1],
        ['amount' => 60000, 'crypto_amount' => 0.2, 'rate' => 300000, 'currency' => 'ETH', 'seller_id' => 2],
        ['amount' => 90000, 'crypto_amount' => 0.3, 'rate' => 300000, 'currency' => 'ETH', 'seller_id' => 3],
        
        // USDT ордера
        ['amount' => 9000, 'crypto_amount' => 100, 'rate' => 90, 'currency' => 'USDT', 'seller_id' => 1],
        ['amount' => 18000, 'crypto_amount' => 200, 'rate' => 90, 'currency' => 'USDT', 'seller_id' => 2],
        ['amount' => 27000, 'crypto_amount' => 300, 'rate' => 90, 'currency' => 'USDT', 'seller_id' => 3]
    ];
    
    $insertOrderStmt = $db->prepare("
        INSERT INTO orders (amount, crypto_amount, rate, currency, seller_id) 
        VALUES (:amount, :crypto_amount, :rate, :currency, :seller_id)
    ");
    
    foreach ($orders as $order) {
        $insertOrderStmt->bindParam(':amount', $order['amount']);
        $insertOrderStmt->bindParam(':crypto_amount', $order['crypto_amount']);
        $insertOrderStmt->bindParam(':rate', $order['rate']);
        $insertOrderStmt->bindParam(':currency', $order['currency']);
        $insertOrderStmt->bindParam(':seller_id', $order['seller_id']);
        $insertOrderStmt->execute();
    }
    
    // Успешная инициализация
    echo '<h1>' . $successMessage . '</h1>';
    echo '<p>Добавлено ' . count($users) . ' пользователей и ' . count($orders) . ' ордеров.</p>';
    echo '<p><a href="index.php">Вернуться на главную страницу</a></p>';
    
} catch (Exception $e) {
    // Ошибка инициализации
    echo '<h1>Ошибка инициализации тестовых данных</h1>';
    echo '<p>Сообщение об ошибке: ' . $e->getMessage() . '</p>';
} 