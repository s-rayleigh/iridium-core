<?php

namespace core;

use Exception;
use mysqli;
use core\log\Log;

/**
 * Класс доступа к БД.
 * @package core
 * @author rayleigh <rayleigh@protonmail.com>
 * @deprecated Moved to modules/MySql
 */
final class Database
{
	/**
	 * @var mysqli Подключение к базе данных.
	 */
	private static $connection;

	/**
	 * @var \mysqli_result|bool Результат выполнения запроса.
	 */
	private static $result;

	/**
	 * @var string Соль пароля.
	 */
	private static $passwordSalt;

	/**
	 * @var array Параметры подключения к базе данных.
	 */
	private static $parameters;

	/**
	 * Инициализирует подключение к базе данных.
	 * @param array $parameters Параметры подключения к базе данных.
	 */
	public static function Initialize($parameters)
	{
		self::$parameters   = $parameters;
		self::$passwordSalt = $parameters['salt'];
		self::Connect();
	}

	/**
	 * Открывает подключение к базе данных.
	 * @throws Exception
	 */
	private static function Connect()
	{
		//Создаем подключение
		self::$connection = new mysqli((USE_CONNECTIONS_POOL ? 'p:' : '') . self::$parameters['host'],
									   self::$parameters['user'],
									   self::$parameters['password'],
									   self::$parameters['db_name']);

		if(self::$connection->connect_errno)
		{
			throw new Exception("Не удалось подключиться к базе данных MySql.\nКод ошибки: " . self::$connection->connect_errno . "\nОшибка: " . self::$connection->connect_error);
		}

		//Устанавливаем кодировку
		self::$connection->set_charset('utf8');
	}

	/**
	 * Выполняет переподключение к базе данных.
	 */
	public static function Reconnect()
	{
		self::Close();
		self::Connect();
	}

	/**
	 * Возвращает данные одной ячейки результата запроса.
	 * @param string $query Текст запроса к базе данных.
	 * @return mixed Данные ячейки.
	 */
	public static function GetCell($query)
	{
		self::Query($query);

		if(is_bool(self::$result))
		{
			return self::$result;
		}

		list($result) = self::$result->fetch_row();

		return $result;
	}

	/**
	 * Возвращает данные первой строки результата запроса в виде ассоциативного массива.
	 * @param string $query Текст запроса к базе данных.
	 * @return array Данные первой строки.
	 */
	public static function GetRow($query)
	{
		self::Query($query);

		return self::$result->fetch_assoc();
	}

	/**
	 * Возвращает данные всех строк результата запроса.
	 * Каждая строка представляет собой ассоциативный массив.
	 * @param string $query Текст запроса к базе данных.
	 * @return array Данные всех строк.
	 */
	public static function GetRows($query)
	{
		self::Query($query);

		return self::$result->fetch_all(MYSQLI_BOTH);
	}

	/**
	 * Выполняет запрос к базе данных.
	 * @param string $query Текст запроса к базе данных.
	 * @throws Exception
	 */
	public static function Query($query)
	{
		Log::Database("Запрос:\n$query");

		if(!(self::$result = self::$connection->query($query)))
		{
			throw new Exception("Не удалось выполнить запрос!\nОшибка: " . self::$connection->error);
		}
	}

	//TODO: реализовать метод
	public static function MultiQuery()
	{
		throw new Exception("Метод мультизапроса не реализован!");
	}

	/**
	 * Закрывает подключение к базе данных.
	 */
	public static function Close()
	{
		self::$connection->close();
	}

	//Экранирует символы в строке в соответствии с кодировкой БД
	//Рекоммендуется использовать при вставке в БД строки с многобайтовыми символами
	public static function EscapeString($string)
	{
		return self::$connection->escape_string($string);
	}

	/**
	 * Создает хеш пароля пользователя.
	 * @param string $password Пароль пользователя.
	 * @return string Хеш пароля пользователя.
	 */
	public static function PasswordHash($password)
	{
		return md5(md5($password) . self::$passwordSalt);
	}

	/**
	 * Возвращает первичный ключ (идентификатор) последней добавленной записи.
	 * @return int Первичный ключ последней добавленной записи.
	 */
	public static function LastId()
	{
		return self::$connection->insert_id;
	}

	/**
	 * Возвращает версию сервера mysql.
	 * @return string Версия сервера mysql.
	 */
	public static function GetServerVersion()
	{
		return self::$connection->server_info;
	}

	/**
	 * Возвращает версию mysqli.
	 * @return string Версия mysql.
	 */
	public static function GetClientVersion()
	{
		return self::$connection->client_version;
	}
}

//Инициализируем подключение к Базе данных
Database::Initialize($database);

//Параметры подключения больше не требуются
unset($database);