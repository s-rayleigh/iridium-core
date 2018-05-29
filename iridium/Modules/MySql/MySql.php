<?php
/**
 * MySql module.
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

namespace Iridium\Modules\MySql;

use Exception;
use Iridium\Core\Module\IModule;
use mysqli;


final class MySql implements IModule
{
	/**
	 * @var mysqli Connection to the database.
	 */
	private static $connection;

	/**
	 * @var \mysqli_result|bool Результат выполнения запроса.
	 */
	private static $result;

	/**
	 * @var array Параметры подключения к базе данных.
	 */
	private static $parameters;

	public static function Init(array $moduleConfig)
	{
		self::$parameters = $moduleConfig;

		if(self::$parameters['autoconnect'])
		{
			self::Connect();
		}
	}

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules(): array
	{
		return [];
	}

	public static function SetParameters(array $parameters)
	{
		self::$parameters = $parameters;
	}

	/**
	 * Connects to the database.
	 * @throws Exception
	 */
	public static function Connect()
	{
		if(!empty(self::$connection))
		{
			self::Close();
		}

		self::$connection = new mysqli(
			(self::$parameters['pool'] ? 'p:' : '') . self::$parameters['host'],
			self::$parameters['user'],
			self::$parameters['pass'],
			self::$parameters['name']
		);

		if(self::$connection->connect_errno)
		{
			throw new Exception("Не удалось подключиться к базе данных MySql.\nКод ошибки: " . self::$connection->connect_errno . "\nОшибка: " . self::$connection->connect_error);
		}

		self::$connection->set_charset(self::$parameters['char']);
	}

	/**
	 * Closes the connection to the database.
	 */
	public static function Close()
	{
		if(!empty(self::$connection))
		{
			self::$connection->close();
			self::$connection = null;
		}
	}

	/**
	 * Reconnects to the database.
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
		if(!(self::$result = self::$connection->query($query)))
		{
			throw new Exception("Не удалось выполнить запрос!\nОшибка: " . self::$connection->error);
		}
	}

	public static function MultiQuery()
	{
		//TODO: implement this method
		throw new Exception("Метод мультизапроса не реализован!");
	}

	//Экранирует символы в строке в соответствии с кодировкой БД
	//Рекоммендуется использовать при вставке в БД строки с многобайтовыми символами
	public static function EscapeString($string)
	{
		return self::$connection->escape_string($string);
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