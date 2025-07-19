# Пример .env файла с автоматическим получением куки

# ===========================================
# ОБМЕННИКИ - АВТОМАТИЧЕСКОЕ ПОЛУЧЕНИЕ КУКИ
# ===========================================

# OBAMA - Автоматическое получение куки
OBAMA_USERNAME=martyn
OBAMA_PASSWORD=your_password_here
OBAMA_AUTO_REFRESH_COOKIES=true

# OBAMA - Ручные куки (для совместимости)
OBAMA_COOKIE="PHPSESSID=09d701c64da186835fc19947710fef89; premium_session_id=YrikZAhubfQ0TvxPrDEg2PZ3yp2taQtMXYFSlsISy4l1vIcehugHXjrI5gFBn0nL; wordpress_logged_in_000f37c7c9e29bc682c1113c4ab6ebfa=martyn%7C1752648540%7CzUpfR5epG5e6iXb84bMqyqjxLQAcr6UQ6WxRhI2TDnm%7C954c77e0c15413df8c83cfa069b20fb1398d5c8b463bd1ba994bc6febccce90d; wordpress_sec_000f37c7c9e29bc682c1113c4ab6ebfa=martyn%7C1752648540%7CzUpfR5epG5e6iXb84bMqyqjxLQAcr6UQ6WxRhI2TDnm%7C55909b921eac60bab302caa0c4a1c889cb06ad8504a255ff1649e48d3acbdd13"

# URAL - Автоматическое получение куки
URAL_USERNAME=k1z1n
URAL_PASSWORD=your_password_here
URAL_AUTO_REFRESH_COOKIES=true

# URAL - Ручные куки (для совместимости)
URAL_COOKIE="PHPSESSID=153cae2c447520803c79a2801bb378b5; premium_session_id=AItVl0xJhe0glnYoNUwDeY7IXClMlNyROcWU9Gmi5NwF58asO7v5IfgaItUz5KgL; wordpress_logged_in_939aa296cba7e000661edfeeafb230c8=k1z1n%7C1752648566%7CZCS9qBsUsMP9kl4lH8fQqTShIyBGJXVldu17hYzgIIf%7C17f84506c9b2fac17e7bc79832a656784bef8ce999494ea4a3d3cf1984dcaf54; wordpress_sec_939aa296cba7e000661edfeeafb230c8=k1z1n%7C1752648566%7CZCS9qBsUsMP9kl4lH8fQqTShIyBGJXVldu17hYzgIIf%7Ce97a917ab273171164f24f9ca50e95d875809fa54972d4e18c93b6daafda11e3"

# ===========================================
# КОМАНДЫ ДЛЯ УПРАВЛЕНИЯ КУКИ
# ===========================================

# Проверить статус куки:
# php artisan cookies:manage status

# Обновить куки для всех обменников:
# php artisan cookies:manage refresh

# Обновить куки для конкретного обменника:
# php artisan cookies:manage refresh obama

# Протестировать куки:
# php artisan cookies:manage test

# ===========================================
# КАК ЭТО РАБОТАЕТ
# ===========================================

# 1. При синхронизации система сначала проверяет сохраненные куки
# 2. Если куки не работают и включено автообновление, система:
#    - Автоматически логинится на сайт
#    - Получает новые куки
#    - Кэширует их на 1 час
# 3. Использует полученные куки для синхронизации

# ===========================================
# БЕЗОПАСНОСТЬ
# ===========================================

# - Куки кэшируются только в памяти
# - Пароли не сохраняются в логах
# - Автоматическое обновление можно отключить
# - Все операции логируются 

 
 
 
 
 
 
 
 
 
 