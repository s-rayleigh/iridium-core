<?php
/**
 * Session class.
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

namespace Iridium\Core;

/**
 * Session.
 * @package Iridium\Core
 */
class Session
{
	/**
	 * @var Session Instance of the session.
	 */
	private static $instace;

	/**
	 * Creates session object.
	 * @throws \Exception If cannot start session.
	 */
	protected function __construct()
	{
		if(!session_start())
		{
			throw new \Exception('Can\'t create session.');
		}

		if(isset($_SESSION['last_access']) && $_SESSION['last_access'] + SESSION_ID_LIFETIME < TIMESTAMP)
		{
			session_regenerate_id(true);
		}

		if(!isset($_SESSION['created']))
		{
			$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$_SESSION['creation_time'] = TIMESTAMP;
			$_SESSION['created'] = true;
		}

		$_SESSION['from_last_access'] = isset($_SESSION['last_access']) ? TIMESTAMP - $_SESSION['last_access'] : 0;
		$_SESSION['last_access'] = TIMESTAMP;
	}

	/**
	 * Creates Session instance.
	 * @return Session Created Session instance.
	 * @throws \Exception If session is already created.
	 */
	public static function Create() : Session
	{
		if(isset(self::$instace))
		{
			throw new \Exception('Session is already created.');
		}

		self::$instace = new Session;
		return self::$instace;
	}

	/**
	 * Destroys session and all it's data.
	 */
	public static function Destroy()
	{
		self::$instace = null;

		// Delete cookies
		setcookie(session_name(), '', time() - 42000);

		// Unset $_SESSION
		session_unset();

		// Destroy session data
		session_destroy();
	}

	/**
	 * @return Session Session instance.
	 * @throws \Exception If session is not created.
	 */
	public static function GetInstance() : Session
	{
		if(!isset(self::$instace))
		{
			throw new \Exception('Session is not created.');
		}

		return self::$instace;
	}

	/**
	 * @return bool True, if session is created.
	 */
	public static function IsCreated() : bool
	{
		return isset(self::$instace);
	}

	/**
	 * @return string Session name.
	 */
	public static function GetSessionName() : string
	{
		return session_name();
	}

	/**
	 * Sets session name.
	 * @param string $name Session name.
	 */
	public static function SetSessionName(string $name)
	{
		session_name($name);
	}

	/**
	 * @return bool True, if user has session id.
	 */
	public static function UserHasSessionId() : bool
	{
		return isset($_COOKIE[session_name()]);
	}

	/**
	 * @return string Returns user ip that the user had while initial session creation.
	 */
	public function GetSessionIP() : string
	{
		return $_SESSION['user_ip'];
	}

	/**
	 * @return string Returns user agent that the user had while initial session creation.
	 */
	public function GetSessionUserAgent() : string
	{
		return $_SESSION['user_agent'];
	}

	/**
	 * @return int Time in seconds from previous access to the current.
	 */
	public function GetTimeFromLastAccess() : int
	{
		return $_SESSION['from_last_access'];
	}

	/**
	 * Sets session data to the $_SESSION array.
	 * @param string $key Array key.
	 * @param mixed $value Value.
	 */
	public function SetData(string $key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Returns data from the $_SESSION array by specified key.
	 * @param string $key Array key.
	 * @return mixed Data obtained by the specified key.
	 * @throws \OutOfRangeException The specified key is not present in $_SESSION array.
	 */
	public function GetData(string $key)
	{
		if(!isset($_SESSION[$key]))
		{
			throw new \OutOfRangeException("The specified key '$key' is not present in session array.");
		}

		return $_SESSION[$key];
	}

	/**
	 * @param string $key Key.
	 * @return bool True, if specified key is in $_SESSION array.
	 */
	public function HasData(string $key) : bool
	{
		return isset($_SESSION[$key]);
	}
}