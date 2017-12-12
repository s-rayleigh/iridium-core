<?php
/**
 * HTTP.
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

namespace core\http;

use core\exceptions\NoticeableException;
use core\http\filter\FilterInput;
use core\http\filter\IFilter;
use core\http\request\ContentType;
use core\http\request\Method;
use core\http\request\Request;
use core\log\LogLevel;

/**
 * Class for work with HTTP protocol.
 * @package core\http
 */
final class HTTP
{
	/**
	 * @var IFilter
	 */
	private static $filter;

	/**
	 * Регистрирует переданный фильтр.
	 * @param IFilter $filter
	 */
	public static function RegisterFilter(IFilter $filter)
	{
		self::$filter = $filter;
	}

	/**
	 * Возвращает зарегистрированный фильтр входных данных.
	 * @return	IFilter	Зарегистрированный фильтр
	 */
	public static function GetRegisteredFilter()
	{
		return self::$filter;
	}

	/**
	 * Проверяет, зарегистрирован ли фильтр. Если нет, бросает Exception.
	 */
	private static function CheckFilterRegistered()
	{
		if(!isset(self::$filter))
		{
			throw new NoticeableException("Фильтр входных данных не был зарегистрирован!", "Ошибка HTTP", LogLevel::FATAL);
		}
	}

	/**
	 * @return bool True, if the request is made through the https protocol.
	 */
	public static function IsHTTPS()
	{
		return !(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off');
	}

	/**
	 * Sends response in JSON format.
	 * @param mixed $data Data that will be encoded in the json format.
	 * @param int $code Response code.
	 * @param string $charset Character set of the response.
	 */
	public static function SendJsonResponse($data, $code = 200, $charset = 'utf-8')
	{
		header('Content-Type: application/json; charset=' . $charset, true, $code);
		echo json_encode($data);
	}

	/**
	 * Sends response in XML format.
	 * @param string $data Data in XML format.
	 * @param int $code Response code.
	 * @param string $charset Character set of the response.
	 */
	public static function SendXmlResponse($data, $code = 200, $charset = 'utf-8')
	{
		header('Content-Type: application/xml; charset=' . $charset, true, $code);
		echo $data;
	}

	public static function Redirect($url, $local = true, $code = 302)
	{
		header('Location: ' . ($local ? (self::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . $url : $url), true, $code);
	}

	/**
	 * Возвращает отфильтрованное значение переменной из суперглобального массива $_POST.
	 * @param	string	$variableName	Имя переменной
	 * @param	int		$filterType		Тип фильтра (FilterType)
	 * @param	mixed	$default		Значение по умолчанию
	 * @param	int		$options		Дополнительные опции фильтра (FilterOption)
	 * @return	mixed					Отфильтрованное значение или значение по умолчанию
	 * @throws	NoticeableException
	 */
	public static function GetPost($variableName, $filterType, $default = null, $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::POST, $variableName, $filterType, $default, $options);
	}

	/**
	 * Возвращает отфильтрованное значение переменной из суперглобального массива $_GET.
	 * @param	string	$variableName	Имя переменной
	 * @param	int		$filterType		Тип фильтра (FilterType)
	 * @param	mixed	$default		Значение по умолчанию
	 * @param	int		$options		Дополнительные опции фильтра (FilterOption)
	 * @return	mixed					Отфильтрованное значение или значение по умолчанию
	 * @throws	NoticeableException
	 */
	public static function GetGet($variableName, $filterType, $default = null, $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::GET, $variableName, $filterType, $default, $options);
	}

	/**
	 * Возвращает отфильтрованное значение переменной из суперглобального массива $_COOKIE.
	 * @param	string	$variableName	Имя переменной
	 * @param	int		$filterType		Тип фильтра (FilterType)
	 * @param	mixed	$default		Значение по умолчанию
	 * @param	int		$options		Дополнительные опции фильтра (FilterOption)
	 * @return	mixed					Отфильтрованное значение или значение по умолчанию
	 * @throws	NoticeableException
	 */
	public static function GetCookie($variableName, $filterType, $default = null, $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::COOKIE, $variableName, $filterType, $default, $options);
	}

	/**
	 * Возвращает отфильтрованное значение переменной из суперглобального массива $_REQUEST.
	 * @param	string	$variableName	Имя переменной
	 * @param	int		$filterType		Тип фильтра (FilterType)
	 * @param	mixed	$default		Значение по умолчанию
	 * @param	int		$options		Дополнительные опции фильтра (FilterOption)
	 * @return	mixed					Отфильтрованное значение или значение по умолчанию
	 * @throws	NoticeableException
	 */
	public static function GetRequest($variableName, $filterType, $default = null, $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::REQUEST, $variableName, $filterType, $default, $options);
	}

	/**
	 * Устанавливает значение cookie с заданным именем и временем.
	 * @param	string	$name	Имя cookie
	 * @param	mixed	$value	Значение cookie
	 * @param	int		$time	Время жизни cookie
	 * @param	string	$path	Путь, по которому будет доступна cookie
	 */
	public static function SetCookie($name, $value, $time, $path = '/')
	{
		setcookie($name, $value, $time, $path);
	}

	/**
	 * Sends POST request with data in urlencoded format.
	 * @param string $url URL.
	 * @param array $data Data.
	 * @return bool|string Response text or false if error has occured.
	 */
	public static function SendPostRequest(string $url, array $data = [])
	{
		return (new Request($url))->SetContentType(ContentType::URLENCODED)->Send($data);
	}

	/**
	 * Sends GET request.
	 * @param string $url URL.
	 * @return bool|string Response text or false if error has occured.
	 */
	public static function SendGetRequest(string $url)
	{
		return (new Request($url))->SetMethod(Method::GET)->Send();
	}

	/**
	 * Sends POST request with data in JSON format.
	 * @param string $url URL.
	 * @param mixed $data Data.
	 * @return bool|string Response text or false if error has occured.
	 */
	public static function SendJsonRequest(string $url, mixed $data)
	{
		return (new Request($url))->SetContentType(ContentType::JSON)->Send($data);
	}

	/**
	 * Sends request with specified url, header, method and content.
	 * @param string $url URL.
	 * @param string $header Request header.
	 * @param string $method Request method (POST or GET).
	 * @param string $contentText Text of the request content.
	 * @return bool|string Response text or false if error has occured.
	 * @deprecated
	 */
	public static function SendRequest(string $url, string $header, string $method, string $contentText = '')
	{
		$options = [
			'http' => [
				'header'  => $header,
				'method'  => $method,
				'content' => $contentText
			]
		];

		$context = stream_context_create($options);
		$result  = file_get_contents($url, false, $context);

		return $result;
	}
}