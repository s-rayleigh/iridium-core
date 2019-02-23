<?php
/**
 * Log level.
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

namespace Iridium\Core\Log;

/**
 * Logging levels.
 * @package Iridium\Core\Log
 */
final class LogLevel
{
	const EVENT		= -1;
	const FATAL		= 0;
	const ERROR		= 1;
	const WARNING	= 2;
	const INFO		= 3;
	const DEBUG		= 4;
	const DATABASE	= 5;

	/**
	 * Returns name of the logging level in string representation.
	 * @param int $level Logging level.
	 * @return string Name.
	 */
	public static function GetString($level)
	{
		switch($level)
		{
			case self::EVENT:
				return 'EVENT';
			case self::FATAL:
				return 'FATAL';
			case self::ERROR:
				return 'ERROR';
			case self::WARNING:
				return 'WARNING';
			case self::INFO:
				return 'INFO';
			case self::DEBUG:
				return 'DEBUG';
			case self::DATABASE:
				return 'DATABASE';
			default:
				return '';
		}
	}
}