<?php

namespace core;

use core\http\filter\ValueType;
use Exception;
use core\http\HTTP;

/**
 * Сессия.
 * @package core
 * @author rayleigh <rayleigh@protonmail.com>
 */
final class Session
{
	/**
	 * @var bool Был-ли создан объект сессии?
	 */
	private static $created = false;

	/**
	 * Конструктор сессии.
	 * @param int $userID Идентификатор пользователя.
	 * @param int $groupId Идентификатор группы пользователей пользователя.
	 * @throws Exception
	 */
	public function __construct($userID = -1, $groupId = -1)
	{
		//Не создаем, если сессия уже создана
		if(self::$created)
		{
			return;
		}

		//Запускаем сессию
		if(!session_start())
		{
			throw new Exception("Не удалось создать сессию!");
		}

		//Если пора генерировать идентификатор сессии заново
		//Время задается константой SESSION_ID_LIFETIME в файле constants.php
		if(isset($_SESSION['last_access']) && $_SESSION['last_access'] + SESSION_ID_LIFETIME < TIMESTAMP)
		{
			session_regenerate_id(true);
		}

		if(!isset($_SESSION['id'])) //Данные сессии не заполнены, т.е. сессия только-что создана. Пытаемся заполнить
		{
			if($userID === -1)
			{
				//Скорее всего у пользователя были куки, но его сессия не инициализирована.
				self::Destroy();
				HTTP::Redirect('index.php');
			}

			$_SESSION['id']    = (int)$userID;					//Идентификатор пользователя
			$_SESSION['group'] = new UsersGroup($groupId);		//Группа пользователей
			$_SESSION['ip']    = $_SERVER['REMOTE_ADDR'];		//IP пользователя
			$_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];	//Информация о браузере пользователя
		}
		else if($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['agent'] !== $_SERVER['HTTP_USER_AGENT'])
		{
			//Удаляем сессию
			self::Destroy();
			throw new Exception("Изменился браузер или ip. Потребуется выполнить вход заново.");
		}

		//Если пользователя не существует
		if(!(bool)Database::GetCell("SELECT COUNT(*) FROM `" . USERS . "` WHERE `id` = '{$_SESSION['id']}' LIMIT 1;"))
		{
			self::Destroy();
			throw new Exception("Ваш аккаунт был удален, мы завершаем вашу последнюю сессию. Приносим свои глубочайшие извинения :D");
		}

		$_SESSION['last_access'] = TIMESTAMP; //Время последнего доступа

		self::$created = true;
	}

	/**
	 * Уничтожает сессию, ее данные, и куки пользователя с идентификатором сессии.
	 */
	public static function Destroy()
	{
		setcookie(session_name(), '', time() - 42000);    //Удаляем cookies
		session_unset();                                //Удаляем данные $_SESSION
		session_destroy();                                //Удаляем все данные сессии
	}

	/**
	 * Определяет есть-ли в куках пользователя идентификатор сессии.
	 * @return bool true, если да.
	 */
	public static function IsUserHasSessionId()
	{
		return isset($_COOKIE[session_name()]);
	}

	/**
	 * Определяет был-ли создан объект сессии.
	 * @return bool true, если да.
	 */
	public static function IsCreated() { return self::$created; }

	/**
	 * Определяет получен-ли доступ администратора используя секретный пароль администратора.
	 * @return bool true, если да.
	 */
	public static function IsAdminAccess()
	{
		return isset($_SESSION['admin_cake']) ? HTTP::GetCookie('cake', ValueType::STRING) === $_SESSION['admin_cake'] : false;
	}

	/**
	 * Возвращает группу, к которой принадленит пользователь.
	 * @return UsersGroup Группа пользователей.
	 */
	public static function GetUserGroup()
	{
		return $_SESSION['group'];
	}

	/**
	 * Определяет есть-ли у группы пользователей текущего пользователя указанные права доступа.
	 * @param int $accessType Тип доступа.
	 * @return bool True, если у группы есть указанные права.
	 */
	public static function HasRights($accessType)
	{
		if(!isset($_SESSION['group'])) { return false; }
		return $_SESSION['group']->HasRights($accessType);
	}
}