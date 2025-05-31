<?php
/**
 * Класс для работы с API банка
 */
class BankApi {
    private $apiKey;
    private $apiUrl;
    
    /**
     * Конструктор класса
     * 
     * @param string $apiKey API ключ
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->apiUrl = 'https://api.bank.example.com/v1';
    }
    
    /**
     * Выполнение перевода с карты на карту
     * 
     * @param array $data Данные перевода
     * @return array Результат операции
     */
    public function processCardToCardPayment($data) {
        // В реальном приложении здесь должен быть код для запроса к API банка
        // Для демонстрации используем эмуляцию
        
        // Проверка обязательных полей
        if (empty($data['from_card']) || empty($data['to_card']) || empty($data['amount'])) {
            return [
                'status' => 'error',
                'message' => 'Отсутствуют обязательные поля'
            ];
        }
        
        // Эмуляция проверки карты и баланса
        if (!$this->validateCardNumber($data['from_card'])) {
            return [
                'status' => 'error',
                'message' => 'Неверный номер карты отправителя'
            ];
        }
        
        if (!$this->validateCardNumber($data['to_card'])) {
            return [
                'status' => 'error',
                'message' => 'Неверный номер карты получателя'
            ];
        }
        
        // Эмуляция запроса к банку
        $response = $this->simulatePayment($data);
        
        // Логирование транзакции
        $this->logTransaction($data, $response);
        
        return $response;
    }
    
    /**
     * Проверка статуса платежа
     * 
     * @param string $transactionId ID транзакции
     * @return array Статус платежа
     */
    public function checkPaymentStatus($transactionId) {
        // В реальном приложении здесь должен быть код для запроса к API банка
        // Для демонстрации используем эмуляцию
        
        // Проверка ID транзакции
        if (empty($transactionId)) {
            return [
                'status' => 'error',
                'message' => 'Отсутствует ID транзакции'
            ];
        }
        
        // Эмуляция запроса к банку
        $response = [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'message' => 'Платеж успешно выполнен'
        ];
        
        return $response;
    }
    
    /**
     * Эмуляция запроса к банку
     * 
     * @param array $data Данные перевода
     * @return array Результат операции
     */
    private function simulatePayment($data) {
        // В реальном приложении здесь должен быть код для запроса к API банка
        // Для демонстрации используем случайный результат
        
        $transactionId = bin2hex(random_bytes(16));
        
        // Вероятность успешной операции 90%
        $success = (mt_rand(1, 100) <= 90);
        
        if ($success) {
            return [
                'status' => 'success',
                'transaction_id' => $transactionId,
                'message' => 'Платеж успешно выполнен'
            ];
        } else {
            return [
                'status' => 'error',
                'transaction_id' => $transactionId,
                'message' => 'Ошибка при выполнении платежа'
            ];
        }
    }
    
    /**
     * Проверка номера карты (алгоритм Луна)
     * 
     * @param string $cardNumber Номер карты
     * @return bool Результат проверки
     */
    private function validateCardNumber($cardNumber) {
        // Удаление пробелов и нечисловых символов
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Проверка длины
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }
        
        // Алгоритм Луна
        $sum = 0;
        $length = strlen($cardNumber);
        $parity = $length % 2;
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$cardNumber[$i];
            
            if ($i % 2 == $parity) {
                $digit *= 2;
                
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return ($sum % 10 == 0);
    }
    
    /**
     * Логирование транзакции
     * 
     * @param array $data Данные перевода
     * @param array $response Ответ от банка
     */
    private function logTransaction($data, $response) {
        $logDir = dirname(__DIR__) . '/logs';
        
        // Создание директории для логов, если она не существует
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/transactions.log';
        
        // Маскирование номеров карт
        $maskedFromCard = substr(str_replace(' ', '', $data['from_card']), 0, 4) . ' **** **** ' . substr(str_replace(' ', '', $data['from_card']), -4);
        $maskedToCard = substr(str_replace(' ', '', $data['to_card']), 0, 4) . ' **** **** ' . substr(str_replace(' ', '', $data['to_card']), -4);
        
        // Данные для логирования
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'from_card' => $maskedFromCard,
            'to_card' => $maskedToCard,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? '',
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