<?php
/**
 * Common constants.
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
 * @copyright 2018 Vladislav Pashaiev
 * @license LGPL-3.0+
 */


/**
 * Iridium Core version.
 * Format: <major version>.<minor version>[.<patch>][-alpha|beta|indev]
 */
define('IRIDIUM_VERSION', '0.1-indev');

//===============================- PATHS -===============================

/**
 * Application root directory.
 */
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

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

/**
 * Namespace separator.
 */
define('NAMESPACE_SEPARATOR', '\\');

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
 * Session lifetime.
 * 12*60*60=43200, etc 12 hrs.
 * @todo Move to the config.
 */
define('SESSION_LIFETIME', 43200);

/**
 * Lifetime of the session id.
 * 60*60=3600, 1 hr.
 * @todo Move to the config.
 */
define('SESSION_ID_LIFETIME', 3600);

//=======================================================================

//==============================- LOGGING -==============================

/**
 * Enable logging.
 * @todo Move to the config.
 */
define('LOGGING_ENABLED', true);

/**
 * Current logging level.
 * @todo Move to the config.
 */
define('LOG_LEVEL', 5);

//=======================================================================