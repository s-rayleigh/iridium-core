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

//Уровни логирования
final class LogLevel
{
	const EVENT		= -1;	//Событие
	const FATAL		= 0;	//Фатальная ошибка
	const ERROR		= 1;	//Ошибка
	const WARNING	= 2;	//Предупреждение
	const INFO		= 3;	//Информационное сообщение
	const DEBUG		= 4;	//Сообщение отладки
	const DATABASE	= 5;	//Запрос к базе данных

	//TODO: дописать описание и doc comments

	/**
	 * @param $level
	 * @return string
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
				return ''; //TODO: заменить на Exception
		}
	}
}