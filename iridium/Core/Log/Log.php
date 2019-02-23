<?php
/**
 * Logging.
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
 * @copyright 2019 Vladislav Pashaiev
 * @license LGPL-3.0+
 */

namespace Iridium\Core\Log;

/**
 * Class for the logging to the file.
 *
 * Before use, you must call {@see Log::Init}.
 *
 * @todo move to the separate module
 */
final class Log
{
	/**
	 * @var string Buffer for storing logs before writing to a file.
	 */
	private static $buffer;

	/**
	 * @var bool Is initialized.
	 */
	private static $initialized = false;

	/**
	 * @var string Prefix of the logs file name.
	 */
	private static $fileNamePrefix;

	/**
	 * Initializes the logging. Call this at the beginning of the execution.
	 * @param string $prefix Prefix of the file name.
	 */
	public static function Init($prefix = '')
	{
		if(!LOGGING_ENABLED || self::$initialized)
		{
			return;
		}

		self::$fileNamePrefix = $prefix;

		// Create directory if it does not exist
		if(!file_exists(ROOT_PATH . LOG_FILES_PATH))
		{
			self::$initialized = mkdir(ROOT_PATH . LOG_FILES_PATH, 0777, true);
			chmod(ROOT_PATH . LOG_FILES_PATH, 0777);
			return;
		}

		self::$initialized = true;
	}

	public static function Database($message)
	{
		self::LogMessage($message, LogLevel::DATABASE);
	}

	public static function Debug($message)
	{
		self::LogMessage($message, LogLevel::DEBUG);
	}

	public static function Info($message)
	{
		self::LogMessage($message, LogLevel::INFO);
	}

	public static function Warning($message)
	{
		self::LogMessage($message, LogLevel::WARNING);
	}

	public static function Error($message)
	{
		self::LogMessage($message, LogLevel::ERROR);
	}

	public static function Fatal($message)
	{
		self::LogMessage($message, LogLevel::FATAL);
	}

	public static function Event($message)
	{
		self::LogMessage($message, LogLevel::EVENT);
	}

	public static function LogMessage($message, $level, $ignoreInitialization = false)
	{
		if($level > LOG_LEVEL || !($ignoreInitialization || self::$initialized))
		{
			return;
		}

		self::$buffer .= date('[d.m.Y H:i:s][') . LogLevel::GetString($level) . "]\n" . $message . "\n\n";
	}

	private static function GetLogFileName() { return self::$fileNamePrefix . date('Y.m.d', time()) . '.log'; }

	/**
	 * Saves log from buffer to the file.
	 * @param bool $clearBuffer Clear buffer after saving.
	 * @return bool True, if successfully saved.
	 */
	public static function Save($clearBuffer = true)
	{
		$filePath = ROOT_PATH . LOG_FILES_PATH . self::GetLogFileName();

		if(file_put_contents($filePath, self::$buffer, FILE_APPEND) === false)
		{
			return false;
		}

		if((fileperms($filePath) & 0777) !== 0777)
		{
			chmod($filePath, 0777);
		}

		if($clearBuffer)
		{
			self::ClearBuffer();
		}

		return true;
	}

	/**
	 * Clears the log buffer.
	 */
	public static function ClearBuffer()
	{
		self::$buffer = '';
	}
}