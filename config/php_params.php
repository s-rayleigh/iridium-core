<?php

//Устанавливаем директорию подключения файлов
//set_include_path('includes/');

ini_set('session.auto_start', false);

//Не хранить id сессии после закрытия браузера пользователем
session_set_cookie_params(0);

//Использовать cookies и только cookies
ini_set('session.use_cookies', true);
ini_set('session.use_only_cookies', true);

//Не использовать пользовательский session_id
ini_set('session.use_strict_mode', true);

//Cookies доступны только через HTTP и недоступны для javascript
ini_set('session.cookie_httponly', true);

//Указывает нужно-ли передавать cookies только по HTTPS
ini_set('session.cookie_secure', false);

//Максимальное время жизни файлов сессии
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

//Определяет вероятность запуска сборщика мусора для файлов сессий
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '1000');

//Не использовать прозрачную поддержку SID
ini_set('session.use_trans_sid', 0);

//Не использовать кеширование сессии на стороне клиента
session_cache_limiter('nocache');

//Использовать алгоритм кеширования sha256 для создания session_id (идентификатора сессии)
ini_set('session.hash_function', 'sha256');