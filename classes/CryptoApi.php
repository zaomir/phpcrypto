<?php
/**
 * Класс для работы с API различных криптовалют
 */
class CryptoApi {
    private $apiKey;
    private $apiUrl;
    private $currency;
    
    /**
     * Конструктор класса
     * 
     * @param string $apiKey API ключ
     * @param string $currency Код валюты (TON, BTC, ETH, USDT и т.д.)
     */
    public function __construct($apiKey, $currency = 'TON') {
        $this->apiKey = $apiKey;
        $this->currency = strtoupper($currency);
        
        // Установка URL API в зависимости от валюты
        switch ($this->currency) {
            case 'TON':
                $this->apiUrl = 'https://api.ton.org/v1';
                break;
            case 'BTC':
                $this->apiUrl = 'https://api.bitcoin.example.com/v1';
                break;
            case 'ETH':
                $this->apiUrl = 'https://api.ethereum.example.com/v1';
                break;
            case 'USDT':
                $this->apiUrl = 'https://api.tether.example.com/v1';
                break;
            default:
                $this->apiUrl = 'https://api.crypto.example.com/v1';
        }
    }
    
    /**
     * Завершение обмена и отправка криптовалюты
     * 
     * @param int $orderId ID ордера
     * @param float $amount Количество криптовалюты
     * @return array Результат операции
     */
    public function completeExchange($orderId, $amount) {
        // В реальном приложении здесь должен быть код для запроса к API криптовалюты
        // Для демонстрации используем эмуляцию
        
        // Проверка обязательных полей
        if (empty($orderId) || empty($amount)) {
            return [
                'status' => 'error',
                'message' => 'Отсутствуют обязательные поля'
            ];
        }
        
        // Эмуляция запроса к API криптовалюты
        $response = $this->simulateTransaction($orderId, $amount);
        
        // Логирование транзакции
        $this->logTransaction($orderId, $amount, $response);
        
        return $response;
    }
    
    /**
     * Проверка статуса транзакции
     * 
     * @param string $transactionId ID транзакции
     * @return array Статус транзакции
     */
    public function checkTransactionStatus($transactionId) {
        // В реальном приложении здесь должен быть код для запроса к API криптовалюты
        // Для демонстрации используем эмуляцию
        
        // Проверка ID транзакции
        if (empty($transactionId)) {
            return [
                'status' => 'error',
                'message' => 'Отсутствует ID транзакции'
            ];
        }
        
        // Эмуляция запроса к API криптовалюты
        $response = [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'message' => 'Транзакция успешно выполнена',
            'currency' => $this->currency
        ];
        
        return $response;
    }
    
    /**
     * Получение курса криптовалюты к RUB
     * 
     * @return float Курс валюты к RUB
     */
    public function getExchangeRate() {
        // В реальном приложении здесь должен быть код для запроса к API криптовалюты
        // Для демонстрации используем фиксированные курсы
        
        switch ($this->currency) {
            case 'TON':
                return 350.0; // Примерный курс TON/RUB
            case 'BTC':
                return 5000000.0; // Примерный курс BTC/RUB
            case 'ETH':
                return 300000.0; // Примерный курс ETH/RUB
            case 'USDT':
                return 90.0; // Примерный курс USDT/RUB
            default:
                return 100.0; // Дефолтный курс
        }
    }
    
    /**
     * Получение минимальной суммы для обмена
     * 
     * @return float Минимальная сумма в RUB
     */
    public function getMinAmount() {
        switch ($this->currency) {
            case 'TON':
                return 1000.0; // Минимум 1000 RUB
            case 'BTC':
                return 5000.0; // Минимум 5000 RUB
            case 'ETH':
                return 3000.0; // Минимум 3000 RUB
            case 'USDT':
                return 1000.0; // Минимум 1000 RUB
            default:
                return 1000.0;
        }
    }
    
    /**
     * Эмуляция запроса к API криптовалюты
     * 
     * @param int $orderId ID ордера
     * @param float $amount Количество криптовалюты
     * @return array Результат операции
     */
    private function simulateTransaction($orderId, $amount) {
        // В реальном приложении здесь должен быть код для запроса к API криптовалюты
        // Для демонстрации используем случайный результат
        
        $transactionId = bin2hex(random_bytes(16));
        
        // Эмуляция процесса обработки
        // Возвращаем статус "processing", чтобы показать, что транзакция обрабатывается
        return [
            'status' => 'processing',
            'transaction_id' => $transactionId,
            'message' => 'Транзакция в процессе обработки',
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $this->currency,
            'estimated_completion_time' => time() + 300 // +5 минут
        ];
    }
    
    /**
     * Логирование транзакции
     * 
     * @param int $orderId ID ордера
     * @param float $amount Количество криптовалюты
     * @param array $response Ответ от API
     */
    private function logTransaction($orderId, $amount, $response) {
        $logDir = dirname(__DIR__) . '/logs';
        
        // Создание директории для логов, если она не существует
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/crypto_transactions.log';
        
        // Данные для логирования
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $this->currency,
            'status' => $response['status'],
            'transaction_id' => $response['transaction_id'] ?? '',
            'message' => $response['message'] ?? ''
        ];
        
        // Запись в лог
        file_put_contents(
            $logFile,
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
} 