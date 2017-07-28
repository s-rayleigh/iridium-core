<?php

namespace core\log;

/**
 * Класс логирования сообщений в файл.
 *
 * Перед использованием необходимо вызвать метод Log::Init().
 */
final class Log
{
	/**
	 * @var string Буфер для хранения логов до сохранения в файл.
	 */
	private static $buffer;

	/**
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * @var string
	 */
	private static $fileNamePrefix;

	/**
	 * @param string $prefix
	 */
	public static function Init($prefix = '')
	{
		if(!LOGGING_ENABLED || self::$initialized)
		{
			return;
		}

		self::LogMessage('Инициализация логирования.', LogLevel::DEBUG, true);

		self::$fileNamePrefix = $prefix;

		//Если директория для файлов логов не создана
		if(!file_exists(ROOT_PATH . LOG_FILES_PATH))
		{
			self::LogMessage('Папка для хранения логов не создана, создаем ее.', LogLevel::DEBUG, true);

			//Создаем директорию
			self::$initialized = mkdir(ROOT_PATH . LOG_FILES_PATH, 0777, true);

			//При рекурсивном создании директроии не выставляет права. Ставим отдельно
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

	//Создать сообщение логирования с указанным уровнем
	public static function LogMessage($message, $level, $ignoreInitialization = false)
	{
		if($level > LOG_LEVEL || !($ignoreInitialization || self::$initialized))
		{
			return;
		}

		self::$buffer .= date('[d.m.Y H:i:s][') . LogLevel::GetString($level) . "]\n" . $message . "\n\n";
	}

	//Возвращает имя файла лога. Ротация по дням
	private static function GetLogFileName() { return self::$fileNamePrefix . date('Y.m.d', time()) . '.log'; }

	/**
	 * Сохраняет логи из буфера в файл.
	 * @param bool $clearBuffer Нужно-ли очистить буфер.
	 * @return bool False, если не удалось сохранить логи в файл.
	 */
	public static function Save($clearBuffer = true)
	{
		self::Debug('Сохранение логов из буфера в файл.');

		$filePath = ROOT_PATH . LOG_FILES_PATH . self::GetLogFileName();

		//Пишем данные буфера в файл
		if(file_put_contents($filePath, self::$buffer, FILE_APPEND) === false)
		{
			return false;
		}

		//Если не выставлен полный режим доступа - выставляем
		if((fileperms($filePath) & 0777) !== 0777)
		{
			chmod($filePath, 0777);
		}

		//Очищаем буфер
		if($clearBuffer)
		{
			self::ClearBuffer();
		}

		return true;
	}

	/**
	 * Выполняет очистку буфера логов.
	 */
	public static function ClearBuffer()
	{
		self::$buffer = '';
	}
}