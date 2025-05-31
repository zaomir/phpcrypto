/**
 * Скрипт для обработки форм и AJAX запросов
 */
document.addEventListener('DOMContentLoaded', function() {
    // Форма обмена на главной странице
    const exchangeForm = document.getElementById('p2pForm');
    
    if (exchangeForm) {
        // Обработка выбора криптовалюты
        const currencySelect = document.getElementById('currency');
        const amountInput = document.getElementById('amount');
        const minAmountNotice = document.getElementById('min-amount-notice');
        
        // Обновление минимальной суммы при выборе валюты
        if (currencySelect && amountInput && minAmountNotice) {
            currencySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const minAmount = selectedOption.getAttribute('data-min');
                
                if (minAmount) {
                    amountInput.min = minAmount;
                    minAmountNotice.textContent = `Минимальная сумма: ${parseInt(minAmount).toLocaleString('ru-RU')} ₽`;
                    
                    // Если текущее значение меньше минимального, обновляем его
                    if (parseInt(amountInput.value) < parseInt(minAmount)) {
                        amountInput.value = minAmount;
                    }
                }
            });
        }
        
        // Отправка формы
        exchangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Получение данных формы
            const formData = new FormData(exchangeForm);
            
            // Отображение загрузки
            const resultContainer = document.getElementById('result');
            resultContainer.innerHTML = '<div class="loading"></div><p>Поиск подходящего ордера...</p>';
            
            // Отправка AJAX запроса
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // Отображение ошибки
                    resultContainer.innerHTML = `
                        <div class="status-box error">
                            <h4>Ошибка</h4>
                            <p>${data.error}</p>
                        </div>
                    `;
                } else {
                    // Отображение результата и перенаправление
                    const currencyName = currencySelect ? currencySelect.options[currencySelect.selectedIndex].value : 'TON';
                    
                    resultContainer.innerHTML = `
                        <div class="status-box success">
                            <h4>Ордер найден!</h4>
                            <p>Сумма: ${parseFloat(data.order.amount).toLocaleString('ru-RU')} ₽</p>
                            <p>Количество ${currencyName}: ${parseFloat(data.order.crypto_amount).toFixed(6)}</p>
                            <p>Курс: ${parseFloat(data.order.rate).toLocaleString('ru-RU')} ₽/${currencyName}</p>
                            <p>Переадресация на страницу оплаты...</p>
                        </div>
                    `;
                    
                    // Перенаправление на страницу подтверждения
                    setTimeout(function() {
                        window.location.href = data.next_step_url;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultContainer.innerHTML = `
                    <div class="status-box error">
                        <h4>Ошибка</h4>
                        <p>Произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.</p>
                    </div>
                `;
            });
        });
    }
    
    // Форматирование номера карты
    const cardNumberInput = document.getElementById('card_number');
    
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            // Удаление всех нечисловых символов
            let value = this.value.replace(/\D/g, '');
            
            // Ограничение длины до 16 символов
            if (value.length > 16) {
                value = value.slice(0, 16);
            }
            
            // Форматирование в группы по 4 цифры
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
            }
            
            // Обновление значения поля
            this.value = value;
        });
    }
    
    // Автообновление статуса обмена
    const exchangeStatus = document.querySelector('.exchange-status .status-box.processing');
    
    if (exchangeStatus) {
        // Автоматическое обновление страницы каждые 30 секунд
        setInterval(function() {
            location.reload();
        }, 30000);
    }
}); 