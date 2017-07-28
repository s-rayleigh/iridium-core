<?php
/**
 * @author rayleigh <rayleigh@protonmail.com>
 */


//=========================- Основные константы -========================

/**
 * Core version.
 * Format: <major version>.<minor version>[.<patch>][-alpha|beta|indev]
 */
define('CORE_VERSION', '0.1-indev');

//=======================================================================

//===========================- Основные пути -===========================

/**
 * Корневая директория сайта.
 */
define('ROOT_PATH', realpath(dirname(__FILE__)) . '/../');

/**
 * Путь к директории кеша сайта.
 */
define('CACHE_PATH', 'storage/cache/');

/**
 * Путь к хранилищу созданных или загруженных файлов.
 */
define('STORAGE_PATH', 'storage/');

/**
 * Путь к файлам логов.
 */
define('LOG_FILES_PATH', STORAGE_PATH . 'logs/');

/**
 * Путь к директории временных файлов, созданных в процессе работы сайта.
 */
define('TEMP_PATH', STORAGE_PATH . 'tmp/');

/**
 * Path to the modules directory.
 */
define('MODULES_PATH', 'modules/');

//=======================================================================

//===============================- Время -===============================
/**
 * Время начала выполнения запроса
 */
define('TIMESTAMP', time());

/**
 * Основной формат времени для вывода на страницу.
 */
define('TIME_FORMAT', 'd.m.Y H:i:s');

/**
 * Использование HTTPS.
 */
define('HTTPS', false);

/**
 * Время жизни файла сессии без обновления.
 * 12*60*60=43200, то бишь 12 часов.
 */
define('SESSION_LIFETIME', 43200);

/**
 * Время, через которое идентификатор сессии будет сгенерирован заново.
 * 60*60=3600, то бишь 1 час.
 */
define('SESSION_ID_LIFETIME', 3600);

//=======================================================================

//============================- Логирование -============================

/**
 * Включить логирование.
 */
define('LOGGING_ENABLED', true);

/**
 * Текущий уровень логирования сообщений.
 */
define('LOG_LEVEL', 5);

//=======================================================================

//==============================- Daemons -==============================

/**
 * Режим отладки демонов.
 * В данном режиме stderr демона перенаправляется в файл daemon_<name>_err.log.
 */
define('DAEMON_DEBUG_MODE', true);

//Коды результатов
define('DAEMON_OP_SUCCESS',				0);
define('DAEMON_NO_FILE',				1);
define('DAEMON_START_PROCESS_ERROR',	3);
define('DAEMON_UNDEFINED_COMMAND',		4);
define('DAEMON_NO_NAME',				5);
define('DAEMON_NO_IN_DB',				6);
define('DAEMON_ALREADY_LAUNCHED',		7);
define('DAEMON_NO_CLASS',				8);
define('DAEMON_NOT_RUNNING',			9);

//Коды статуса демона
define('DAEMON_DISABLED', 0);	//Отключен
define('DAEMON_WAITING', 1);	//Ждет указанное в БД время
define('DAEMON_WORKING', 2);	//Работает

//Регулярные выражения проверки названий демона
define('DAEMON_NAME_REGEXPR', '^[A-z_]{2,30}$');
define('DAEMON_DISPL_NAME_REGEXPR', '^[A-Za-zА-Яа-я0-9_\s\-]{2,30}$');

//=======================================================================