<?php


namespace core;

use core\log\Log;

/**
 * Группа пользователей.
 * @package core
 * @author rayleigh <rayleigh@protonmail.com>
 */
class UsersGroup
{
	/**
	 * @var int Идентификатор группы.
	 */
	private $id;

	/**
	 * @var string Название группы.
	 */
	private $name;

	/**
	 * @var bool Полный доступ.
	 */
	private $fullAccess;

	/**
	 * @var array Права доступа.
	 */
	private $rights;

	public function __construct($dbId)
	{
		$groupData = Database::GetRow("SELECT `name`, `full` FROM `" . USERS_GROUPS . "` WHERE `id` = '$dbId' LIMIT 1;");

		if(empty($groupData))
		{
			//TODO: создать класс для исключения
			Log::Fatal("Группа с идентификатором $dbId отсутствует, хотя задана у пользователя.");
			throw new \Exception("Группа пользователя не существует, создать объект группы невозможно.");
		}


		$this->id         = (int)$dbId;
		$this->name       = $groupData['name'];
		$this->fullAccess = (bool)$groupData['full'];

		$accessData = Database::GetRows("SELECT `type` FROM `" . USERS_GROUPS_ACCESS . "` WHERE `users_group_id` = '$dbId';");

		if(!empty($accessData) && !$this->fullAccess)
		{
			Log::Warning("Группе {$groupData['name']} не заданы права доступа и не задан полный доступ, что делает группу бесполезной.");
		}

		$this->rights = array_column($accessData, 'type');
	}

	/**
	 * Возвращает идентификатор группы пользователей.
	 * @return int Идентификатор группы пользователей.
	 */
	public function GetId()
	{
		return $this->id;
	}

	/**
	 * Есть-ли у группы все права доступа.
	 * @return bool
	 */
	public function IsFullAccess()
	{
		return $this->fullAccess;
	}

	/**
	 * Определяет есть-ли у группы пользователей указанные права доступа.
	 * @param int $accessType Тип доступа.
	 * @return bool True, если у группы есть указанные права.
	 */
	public function HasRights($accessType)
	{
		if($this->fullAccess)
		{
			return true;
		}

		if(empty($this->rights))
		{
			return false;
		}

		return in_array($accessType, $this->rights);
	}
}