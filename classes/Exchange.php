<?php
/**
 * Класс для работы с обменными операциями
 */
class Exchange {
    private $db;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация соединения с базой данных
        $this->db = new PDO(
            'sqlite:' . dirname(__DIR__) . '/database/exchange.db',
            null,
            null,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Создание таблиц, если они не существуют
        $this->initDatabase();
    }
    
    /**
     * Инициализация базы данных
     */
    private function initDatabase() {
        // Создание таблицы заказов
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                amount REAL NOT NULL,
                crypto_amount REAL NOT NULL,
                rate REAL NOT NULL,
                currency TEXT NOT NULL DEFAULT 'TON',
                seller_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Создание таблицы пользователей
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                card_number TEXT,
                ton_address TEXT,
                btc_address TEXT,
                eth_address TEXT,
                usdt_address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Создание таблицы сессий обмена
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS exchange_sessions (
                id TEXT PRIMARY KEY,
                order_id INTEGER NOT NULL,
                buyer_card TEXT NOT NULL,
                seller_card TEXT NOT NULL,
                amount REAL NOT NULL,
                crypto_amount REAL NOT NULL,
                currency TEXT NOT NULL DEFAULT 'TON',
                payment_status TEXT DEFAULT 'pending',
                exchange_status TEXT DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    /**
     * Поиск подходящего ордера
     * 
     * @param float $amount Сумма в рублях
     * @param string $currency Код валюты (по умолчанию TON)
     * @return array|null Найденный ордер или null
     */
    public function findOrder($amount, $currency = 'TON') {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE status = 'active' 
            AND currency = :currency
            AND amount >= :min_amount 
            AND amount <= :max_amount
            ORDER BY rate ASC
            LIMIT 1
        ");
        
        // Допустимое отклонение ±5%
        $minAmount = $amount * 0.95;
        $maxAmount = $amount * 1.05;
        
        $stmt->bindParam(':min_amount', $minAmount);
        $stmt->bindParam(':max_amount', $maxAmount);
        $stmt->bindParam(':currency', $currency);
        $stmt->execute();
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            return [
                'id' => $order['id'],
                'amount' => $amount, // Используем запрошенную сумму
                'crypto_amount' => $amount / $order['rate'],
                'rate' => $order['rate'],
                'currency' => $order['currency'],
                'seller_id' => $order['seller_id']
            ];
        }
        
        return null;
    }
    
    /**
     * Запрос данных карты продавца
     * 
     * @param int $orderId ID ордера
     * @return array|null Данные продавца или null
     */
    public function requestSellerCardDetails($orderId) {
        $stmt = $this->db->prepare("
            SELECT u.card_number FROM orders o
            JOIN users u ON o.seller_id = u.id
            WHERE o.id = :order_id
        ");
        
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Создание сессии обмена
     * 
     * @param array $data Данные сессии
     * @return string ID созданной сессии
     */
    public function createExchangeSession($data) {
        $sessionId = bin2hex(random_bytes(16));
        
        $stmt = $this->db->prepare("
            INSERT INTO exchange_sessions 
            (id, order_id, buyer_card, seller_card, amount, crypto_amount, currency) 
            VALUES (:id, :order_id, :buyer_card, :seller_card, :amount, :crypto_amount, :currency)
        ");
        
        $stmt->bindParam(':id', $sessionId);
        $stmt->bindParam(':order_id', $data['order_id']);
        $stmt->bindParam(':buyer_card', $data['buyer_card']);
        $stmt->bindParam(':seller_card', $data['seller_card']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':crypto_amount', $data['crypto_amount']);
        $stmt->bindParam(':currency', $data['currency'] ?? 'TON');
        
        $stmt->execute();
        
        return $sessionId;
    }
    
    /**
     * Получение данных сессии
     * 
     * @param string $sessionId ID сессии
     * @return array|null Данные сессии или null
     */
    public function getSession($sessionId) {
        $stmt = $this->db->prepare("
            SELECT * FROM exchange_sessions
            WHERE id = :session_id
        ");
        
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $session ?: null;
    }
    
    /**
     * Получение статуса ордера
     * 
     * @param int $orderId ID ордера
     * @return array|null Статус ордера или null
     */
    public function getOrderStatus($orderId) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders
            WHERE id = :order_id
        ");
        
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $order ?: null;
    }
    
    /**
     * Обновление статуса платежа
     * 
     * @param string $sessionId ID сессии
     * @param string $status Новый статус
     * @return bool Результат операции
     */
    public function updatePaymentStatus($sessionId, $status) {
        $stmt = $this->db->prepare("
            UPDATE exchange_sessions
            SET payment_status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :session_id
        ");
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':session_id', $sessionId);
        
        return $stmt->execute();
    }
    
    /**
     * Обновление статуса обмена
     * 
     * @param string $sessionId ID сессии
     * @param string $status Новый статус
     * @return bool Результат операции
     */
    public function updateExchangeStatus($sessionId, $status) {
        $stmt = $this->db->prepare("
            UPDATE exchange_sessions
            SET exchange_status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :session_id
        ");
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':session_id', $sessionId);
        
        return $stmt->execute();
    }
    
    /**
     * Получение списка поддерживаемых валют
     * 
     * @return array Список валют
     */
    public function getSupportedCurrencies() {
        return [
            'TON' => [
                'name' => 'TON',
                'full_name' => 'Toncoin',
                'min_amount' => 1000,
                'logo' => 'assets/img/ton.png'
            ],
            'BTC' => [
                'name' => 'BTC',
                'full_name' => 'Bitcoin',
                'min_amount' => 5000,
                'logo' => 'assets/img/btc.png'
            ],
            'ETH' => [
                'name' => 'ETH',
                'full_name' => 'Ethereum',
                'min_amount' => 3000,
                'logo' => 'assets/img/eth.png'
            ],
            'USDT' => [
                'name' => 'USDT',
                'full_name' => 'Tether',
                'min_amount' => 1000,
                'logo' => 'assets/img/usdt.png'
            ]
        ];
    }
    
    /**
     * Получение адреса кошелька пользователя
     * 
     * @param int $userId ID пользователя
     * @param string $currency Код валюты
     * @return string|null Адрес кошелька или null
     */
    public function getUserWalletAddress($userId, $currency) {
        $column = strtolower($currency) . '_address';
        
        $stmt = $this->db->prepare("
            SELECT $column FROM users
            WHERE id = :user_id
        ");
        
        $stmt->bindParam(':user_id', $userId);
        
        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result[$column] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
} 