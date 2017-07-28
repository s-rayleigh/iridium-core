<?php

namespace core\log;

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