<?php
/**
 * File group.
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

namespace Iridium\Modules\File;
use Iridium\Core\Exceptions\NoticeableException;
use Iridium\Core\Log\LogLevel;

/**
 * Группа файлов.
 * @package Iridium\Modules\File
 * @author rayleigh <rayleigh@protonmail.ch>
 */
class FilesGroup
{
	/**
	 * Права доступа к создаваемой директории группы файлов.
	 */
	const dirMode = 0777;

	/**
	 * @var int Идентификатор группы файлов в базе данных.
	 */
	private $id;

	/**
	 * @var int Идентификатор типа файлов в группе.
	 */
	private $filesTypeId;

	/**
	 * @var string Название группы файлов.
	 */
	private $name;

	/**
	 * @var int Количество файлов в группе.
	 */
	private $filesCount;

	/**
	 * @var int Дата и время создания группы файлов.
	 */
	private $time;

	private function __construct($id, $filesTypeId, $name, $filesCount, $time)
	{
		$this->id = $id;
		$this->filesTypeId = $filesTypeId;
		$this->name = $name;
		$this->filesCount = (int)$filesCount;
		$this->time = $time;
	}

	/**
	 * Создает объект группы по ее идентификатору в БД.
	 * @param int $groupId Идентификатор группы.
	 * @return FilesGroup Объект группы файлов.
	 * @throws NoticeableException
	 */
	public static function GetGroupById($groupId)
	{
		$groupData = Database::GetRow("SELECT `id`, `name`, `type_id`, `count`, UNIX_TIMESTAMP(`time`) as `time` FROM `" . FILE_GROUPS . "` WHERE `id` = '$groupId' LIMIT 1;");

		if(empty($groupData))
		{
			throw new NoticeableException("Невозможно получить данные группы файлов с id $groupId!", 'Группа файлов', LogLevel::ERROR);
		}

		$group = new FilesGroup($groupData['id'], $groupData['type_id'], $groupData['name'], $groupData['count'], $groupData['time']);

		return $group;
	}

	/**
	 * Выбирает доступную или создает новую группу файлов.
	 * @param int $filesType Тип файлов в группе (FileType).
	 * @return FilesGroup Группа файлов.
	 * @throws \Exception
	 */
	public static function SelectOrCreateGroup($filesType)
	{
		global $files;

		$groupData = Database::GetRow("SELECT `id`, `name`, `count`, UNIX_TIMESTAMP(`time`) as `time` FROM `" . FILE_GROUPS . "` WHERE `count` < '{$files['grop_max_files']}' AND `type_id` = '$filesType' LIMIT 1;");

		if(empty($groupData))
		{
			$name = self::GenerateName($files['group_name_salt'], $filesType);

			Database::Query("INSERT INTO `" . FILE_GROUPS . "`(`name`, `type_id`) VALUES('$name', '$filesType');");

			$group = new FilesGroup(Database::LastId(), $filesType, $name, 0, TIMESTAMP);

			mkdir($group->GetPath(), self::dirMode, true);

			return $group;
		}
		else
		{
			return new FilesGroup($groupData['id'], $filesType, $groupData['name'], $groupData['count'], $groupData['time']);
		}
	}

	/**
	 * Удаляет группу файлов, если в ней не находятся файлы.
	 * @throws NoticeableException
	 * @throws \Exception
	 */
	public function Delete()
	{
		$filesExists = (bool)Database::GetCell("SELECT COUNT(*) FROM `" . FILES . "` WHERE `group_id` = '{$this->id}' LIMIT 1;");

		if($filesExists)
		{
			throw new NoticeableException("Невозможно удалить группу, так как в ней есть файлы!", 'Группа файлов', LogLevel::ERROR);
		}

		//Удаляем директорию группы файлов
		if(!rmdir($this->GetPath()))
		{
			throw new NoticeableException("Невозможно удалить группу, так как в ней физически присутствуют файлы, однако они не зарегистрированы в БД!", 'Группа файлов', LogLevel::FATAL);
		}

		//Удаляем запись группы файлов в БД
		Database::Query("DELETE FROM `" . FILE_GROUPS . "` WHERE `id` = '{$this->id}' LIMIT 1;");
	}

	/**
	 * Возвращает имя группы файлов.
	 * @return string Имя группы.
	 */
	public function GetName()
	{
		return $this->name;
	}

	/**
	 * Возвращает идентификатор группы файлов.
	 * @return int Идентификатор группы файлов.
	 */
	public function GetId()
	{
		return $this->id;
	}

	/**
	 * Возвращает количество файлов в группе.
	 * @return int Количество файлов в группе.
	 */
	public function GetFilesCount()
	{
		return $this->filesCount;
	}

	/**
	 * Возвращает название типа файлов данной группы.
	 * @return string Название типа файлов.
	 * @throws NoticeableException
	 */
	public function GetFilesTypeName()
	{
		return FileType::GetFolder($this->filesTypeId);
	}

	/**
	 * Возвращает путь к директории группы файлов.
	 * @return string Путь к директории.
	 */
	public function GetPath()
	{
		return STORAGE_PATH . $this->GetFilesTypeName() . '/' . $this->GetName();
	}

	/**
	 * Генерирует уникальное имя группы длиной 9 символов.
	 * @param string $salt Соль.
	 * @param int $type Тип файла (FileType).
	 * @return string Уникальное имя группы.
	 */
	private static function GenerateName($salt, $type)
	{
		return hash('crc32b', $salt . (time() - 2000) . $type) . ($type % 9);
	}
}