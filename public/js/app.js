// Основной файл приложения
// Этот файл может содержать общие функции и настройки

console.log('App.js загружен');

// Общие функции для всего приложения
window.App = {
    // Инициализация приложения
    init: function() {
        console.log('Приложение инициализировано');
    },

    // Общие утилиты
    utils: {
        // Форматирование чисел
        formatNumber: function(num, decimals = 2) {
            return parseFloat(num).toFixed(decimals);
        },

        // Проверка существования элемента
        elementExists: function(id) {
            return document.getElementById(id) !== null;
        }
    }
};

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    window.App.init();
});
