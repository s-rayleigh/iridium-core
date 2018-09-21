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
 * @copyright 2018 Vladislav Pashaiev
 * @license LGPL-3.0+
 */

namespace Iridium\Core\Http;

use Iridium\Core\Http\Filter\DefaultFilter;
use Iridium\Core\Http\Filter\FilterInput;
use Iridium\Core\Http\Filter\IFilter;
use Iridium\Core\Http\Request\ContentType;
use Iridium\Core\Http\Request\Method;
use Iridium\Core\Http\Request\Request;

/**
 * Class for work with HTTP protocol.
 * @package Iridium\Core\Http
 */
final class HTTP
{
	/**
	 * @var IFilter Filter for the values from user.
	 */
	private static $filter;

	/**
	 * Registers passed filter.
	 * @param IFilter $filter Filter to register.
	 */
	public static function RegisterFilter(IFilter $filter)
	{
		self::$filter = $filter;
	}

	/**
	 * @return IFilter Registered filter.
	 */
	public static function GetRegisteredFilter(): IFilter
	{
		return self::$filter;
	}

	/**
	 * Checks is the filter is registered. If not, registers the default filter.
	 */
	private static function CheckFilterRegistered()
	{
		if(!isset(self::$filter)) { self::RegisterFilter(new DefaultFilter()); }
	}

	/**
	 * @return bool True, if the request is made through the https protocol.
	 */
	public static function IsHTTPS(): bool
	{
		return !(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off');
	}

	/**
	 * @return string Url of the current host.
	 */
	public static function GetHost(): string
	{
		return (self::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
	}

	/**
	 * Sends response in JSON format.
	 * @param mixed $data Data that will be encoded in the json format.
	 * @param int $code Response code.
	 * @param string $charset Character set of the response.
	 */
	public static function SendJsonResponse($data, int $code = 200, string $charset = 'utf-8')
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
	public static function SendXmlResponse(string $data, int $code = 200, string $charset = 'utf-8')
	{
		header('Content-Type: application/xml; charset=' . $charset, true, $code);
		echo $data;
	}

	/**
	 * Redirect to the passed url by using the 'Location' HTTP header.
	 * @param string $url
	 * @param bool $local
	 * @param int $code
	 */
	public static function Redirect(string $url, bool $local = true, int $code = 302)
	{
		header('Location: ' . ($local ? (self::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . $url : $url), true, $code);
	}

	/**
	 * Returns filtered value from the '$_POST' superglobal array.
	 * @param string $key Key of the value in '$_POST' superblobal array.
	 * @param int $type Type of the value (@see ValueType).
	 * @param null $default Default value. Used if no value with passed key in '$_POST'.
	 * @param int $options Filter options (@see FilterOption).
	 * @return mixed Value from '$_POST' or default value.
	 * @throws Filter\InputFilterException
	 * @throws \Exception
	 */
	public static function GetPost(string $key, int $type, $default = null, int $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::POST, $key, $type, $default, $options);
	}

	/**
	 * Returns filtered value from the '$_GET' superglobal array.
	 * @param string $key Key of the value in '$_GET' superblobal array.
	 * @param int $type Type of the value (@see ValueType).
	 * @param null $default Default value. Used if no value with passed key in '$_GET'.
	 * @param int $options Filter options (@see FilterOption).
	 * @return mixed Value from '$_GET' or default value.
	 * @throws Filter\InputFilterException
	 * @throws \Exception
	 */
	public static function GetGet(string $key, int $type, $default = null, int $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::GET, $key, $type, $default, $options);
	}

	/**
	 * Returns filtered value from the '$_COOKIE' superglobal array.
	 * @param string $key Key of the value in '$_COOKIE' superblobal array.
	 * @param int $type Type of the value (@see ValueType).
	 * @param null $default Default value. Used if no value with passed key in '$_COOKIE'.
	 * @param int $options Filter options (@see FilterOption).
	 * @return mixed Value from '$_COOKIE' or default value.
	 * @throws Filter\InputFilterException
	 * @throws \Exception
	 */
	public static function GetCookie(string $key, int $type, $default = null, int $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::COOKIE, $key, $type, $default, $options);
	}

	/**
	 * Returns filtered value from the '$_REQUEST' superglobal array.
	 * @param string $key Key of the value in '$_REQUEST' superblobal array.
	 * @param int $type Type of the value (@see ValueType).
	 * @param null $default Default value. Used if no value with passed key in '$_REQUEST'.
	 * @param int $options Filter options (@see FilterOption).
	 * @return mixed Value from '$_REQUEST' or default value.
	 * @throws Filter\InputFilterException
	 * @throws \Exception
	 */
	public static function GetRequest(string $key, int $type, $default = null, int $options = 0)
	{
		self::CheckFilterRegistered();
		return self::$filter->FilterInput(FilterInput::REQUEST, $key, $type, $default, $options);
	}

	/**
	 * Sets the cookie value.
	 * @param string $name Name of the cookie.
	 * @param string $value Value of the cookie.
	 * @param int $expire The time the cookie expires. If 0 or ommitted, the cookie expires at the end of the session (when the browser closes).
	 * @param string $path The path on the server in which the cookie will available on.
	 */
	public static function SetCookie(string $name, string $value = '', int $expire = 0, string $path = '/')
	{
		setcookie($name, $value, $expire, $path);
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
	public static function SendJsonRequest(string $url, $data)
	{
		return (new Request($url))->SetContentType(ContentType::JSON)->Send($data);
	}

	/**
	 * @return string Language code of the user preferred language or empty string.
	 */
	public static function GetUserLangCode(): string
	{
		if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) || $_SERVER['HTTP_ACCEPT_LANGUAGE'] === '*') { return ''; }
		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
}