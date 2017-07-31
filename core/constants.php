<?php
/**
 * Global constants.
 * This file is part of Iridium Core project.
 *
 * Iridium Core is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Iridium Core is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Iridium Core. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author rayleigh <rayleigh@protonmail.com>
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */


/**
 * Iridium Core version.
 * Format: <major version>.<minor version>[.<patch>][-alpha|beta|indev]
 */
define('IRIDIUM_CORE_VERSION', '0.1-indev');

//===============================- PATHS -===============================

/**
 * Корневая директория сайта.
 * @deprecated
 */
define('ROOT_PATH', realpath(dirname(__FILE__)) . '/../');

/**
 * Путь к директории кеша сайта.
 * @deprecated
 */
define('CACHE_PATH', 'storage/cache/');

/**
 * Путь к хранилищу созданных или загруженных файлов.
 * @deprecated
 */
define('STORAGE_PATH', 'storage/');

/**
 * Путь к файлам логов.
 * @deprecated
 */
define('LOG_FILES_PATH', STORAGE_PATH . 'logs/');

/**
 * Путь к директории временных файлов, созданных в процессе работы сайта.
 * @deprecated
 */
define('TEMP_PATH', STORAGE_PATH . 'tmp/');

/**
 * Path to the modules directory.
 * @deprecated
 */
define('MODULES_PATH', 'modules/');

//=======================================================================

//================================- TIME -===============================

/**
 * Timestamp.
 */
define('TIMESTAMP', time());

/**
 * Main time format.
 * @deprecated
 */
define('TIME_FORMAT', 'd.m.Y H:i:s');

/**
 * @deprecated
 */
define('HTTPS', false);

/**
 * Session lifetime.
 * 12*60*60=43200, etc 12 hrs.
 */
define('SESSION_LIFETIME', 43200);

/**
 * Lifetime of the session id.
 * 60*60=3600, 1 hr.
 */
define('SESSION_ID_LIFETIME', 3600);

//=======================================================================

//==============================- LOGGING -==============================

/**
 * Enable logging.
 */
define('LOGGING_ENABLED', true);

/**
 * Current logging level.
 */
define('LOG_LEVEL', 5);

//=======================================================================

//==============================- DAEMONS -==============================

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