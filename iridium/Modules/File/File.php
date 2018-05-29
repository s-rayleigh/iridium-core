<?php
/**
 * File module.
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
use Iridium\Core\Exceptions\UploadException;
use Iridium\Core\Log\Log;
use Iridium\Core\Log\LogLevel;
use Iridium\Core\Session;

require 'FilesGroup.php';
require 'FileType.php';

/**
 * File.
 * TODO: rewrite
 * @package Iridium\Modules\File
 */
class File implements IModule
{
	/**
	 * @var int Идентификатор файла в базе данных.
	 */
	private $id;

	/**
	 * @var int Идентификатор создателя. Может быть не задан.
	 */
	private $creatorId;

	/**
	 * @var string Имя файла без расширения.
	 */
	private $name;

	/**
	 * @var string Расширение файла.
	 */
	private $extension;

	/**
	 * @var int Размер файла (кол-во байт).
	 */
	private $size;

	/**
	 * @var int Время добавления файла.
	 */
	private $time;

	/**
	 * @var FilesGroup Группа файлов, к которой принадлежит данный файл.
	 */
	private $group;

	/**
	 * @var bool Указывает на то, что файл является временным и находится во временной директории.
	 */
	private $tempFile;

	/**
	 * @var bool Указывает на то, что файл локальный, а не загружен пользователем через $_FILES.
	 */
	private $local;

	/**
	 * @var string Путь к временному файлу.
	 */
	private $tempPath;

	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	static function Init(array $moduleConfig) { }

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules(): array
	{
		return ['MySql'];
	}

	private function __construct() { }

	/**
	 * Создает объект временного файла, который был загружен пользователем на сервер.
	 * @param string $arrayFileName Имя файла в суперглобальном массиве загруженных файлов.
	 * @param int $maxSize Максимальный размер файла в байтах.
	 * @param array $allowedExtensions Массив допустимых расширений файла.
	 * @return File Объект временного файла.
	 * @throws NoticeableException
	 * @throws UploadException
	 */
	public static function FromUser($arrayFileName, $maxSize, array $allowedExtensions)
	{
		if(!Session::IsCreated())
		{
			throw new NoticeableException('Невозможно получить файл от пользователя, так как не создана сессия.', 'Загрузка файла', LogLevel::ERROR);
		}

		if(!isset($_FILES[$arrayFileName]))
		{
			throw new NoticeableException("Данные файла не были получены. Имя файла: $arrayFileName.", 'Файл', LogLevel::WARNING);
		}

		if($_FILES[$arrayFileName]['error'] !== UPLOAD_ERR_OK)
		{
			throw new UploadException($_FILES[$arrayFileName]['error']);
		}

		//Проверяем размер файла
		if($_FILES[$arrayFileName]['size'] > $maxSize)
		{
			throw new NoticeableException("Размер файла превышает максимально допустимый!", 'Файл', LogLevel::WARNING);
		}

		$extension = self::GetExtensionFromPath($_FILES['file']['name']);

		//Проверяем расширение файла
		if(!in_array($extension, $allowedExtensions, true))
		{
			throw new NoticeableException("Задано недопустимое расширение файла!", 'Файл', LogLevel::WARNING);
		}

		$file = new File();

		$file->creatorId = $_SESSION['id'];
		$file->tempFile  = true;
		$file->local     = false;
		$file->tempPath  = $_FILES[$arrayFileName]['tmp_name'];
		$file->size      = $_FILES[$arrayFileName]['size'];
		$file->extension = $extension;

		return $file;
	}

	/**
	 * Создает объект файла по его идентификатору в БД.
	 * @param int $fileId Идентификатор файла.
	 * @return File Объект файла.
	 * @throws NoticeableException
	 */
	public static function GetById($fileId)
	{
		$fileData = Database::GetRow("SELECT `id`, `creator_id`, `name`, `extension`, `group_id`, `size`, UNIX_TIMESTAMP(`time`) as `time` FROM `" . FILES . "` WHERE `id` = '$fileId' LIMIT 1;");

		if(empty($fileData))
		{
			throw new NoticeableException("Невозможно получить данные файла из БД по id $fileId. Возможно, файла с таким идентификатором не существует.", 'Файл', LogLevel::ERROR);
		}

		$file = new File();

		$file->id        = $fileData['id'];
		$file->creatorId = $fileData['creator_id'];
		$file->name      = $fileData['name'];
		$file->extension = $fileData['extension'];
		$file->group     = FilesGroup::GetGroupById($fileData['group_id']);
		$file->size      = $fileData['size'];
		$file->time      = $fileData['time'];

		return $file;
	}

	/**
	 * Создает объект временного файла на основе локального файла.
	 * @param string $path Путь к локальному файлу.
	 * @return File Объект файла.
	 * @throws NoticeableException
	 */
	public static function Local($path)
	{
		if(!file_exists($path))
		{
			throw new NoticeableException('Невозможно создать объект файла, так как файл не найден.', 'Файл', LogLevel::ERROR);
		}

		$file = new File();

		$size = filesize($path);

		if($size === false)
		{
			throw new NoticeableException('Невозможно получить размер локального файла при создании объекта файла.', 'Файл', LogLevel::ERROR);
		}

		$file->tempFile  = true;
		$file->local     = true;
		$file->tempPath  = $path;
		$file->extension = self::GetExtensionFromPath($path);
		$file->size      = $size;

		return $file;
	}

	/**
	 * Перемещает временный файл в хранилище файлов.
	 * @param int $fileType Тип файла (FileType).
	 * @param int $fileMode Права доступа к файлу (в восьмеричном формате).
	 * @throws NoticeableException Если файл не является временным.
	 * @throws \Exception
	 */
	public function MoveToStorage($fileType, $fileMode = 0600)
	{
		if(!$this->tempFile)
		{
			throw new NoticeableException("Невозможно переместить файл в хранилище файлов! Файл не является временным.", 'Файл', LogLevel::ERROR);
		}

		$this->group = FilesGroup::SelectOrCreateGroup($fileType);
		$this->name  = self::GenerateName($this->group->GetId());

		Database::Query("INSERT INTO `" . FILES . "`(" . (isset($this->creatorId) ? '`creator_id`, ' : '') . "`name`, `extension`, `group_id`, `size`)
											VALUES(" . (isset($this->creatorId) ? "'{$this->creatorId}', " : '') . "'{$this->name}', '{$this->extension}', '{$this->group->GetId()}', '{$this->size}');");

		$this->id = Database::LastId();

		$path = $this->GetPath();

		//Перемещаем файл

		if($this->local)
		{
			if(!rename($this->GetTempPath(), $path))
			{
				//Удаляем ненужную запись в БД
				Database::Query("DELETE FROM `" . FILES . "` WHERE `id` = '{$this->id}' LIMIT 1;");

				throw new NoticeableException('Не удалось переместить локальный файл при создании объекта файла.', 'Файл', LogLevel::ERROR);
			}
		}
		else
		{
			if(!move_uploaded_file($this->GetTempPath(), $path))
			{
				//Удаляем ненужную запись в БД
				Database::Query("DELETE FROM `" . FILES . "` WHERE `id` = '{$this->id}' LIMIT 1;");

				//Отправляем сообщение о ошибке
				throw new NoticeableException('Попытка переместить файл, не загруженный по HTTP.', 'Файл', LogLevel::FATAL);
			}
		}

		if(!chmod($path, $fileMode))
		{
			Log::Error('Невозможно изменить права доступа при перемещении файла в хранилище!');
		}

		//Добавляем 1 к кол-ву файлов группы
		Database::Query("UPDATE `" . FILE_GROUPS . "` SET `count` = `count` + 1 WHERE `id` = '{$this->group->GetId()}' LIMIT 1;");

		$this->tempFile = false;
	}

	/**
	 * Удаляет файл.
	 * @throws NoticeableException Файл временный.
	 * @throws \Exception
	 */
	public function Delete()
	{
		if($this->tempFile)
		{
			throw new NoticeableException("Невозможно удалить временный файл. Временные файлы удаляются после завершения выполнения запроса.", 'Файл', LogLevel::ERROR);
		}

		unlink($this->GetPath());

		Database::Query("DELETE FROM `" . FILES . "` WHERE `id` = '{$this->id}' LIMIT 1;");

		if($this->group->GetFilesCount() === 1)
		{
			//Удаляем группу, если данный файл в ней последний
			$this->group->Delete();
		}
		else
		{
			//Уменьшаем кол-во файлов в группе на 1
			Database::Query("UPDATE `" . FILE_GROUPS . "` SET `count` = `count` - 1 WHERE `id` = '{$this->group->GetId()}' LIMIT 1;");
		}
	}

	/**
	 * Возвращает путь к файлу.
	 * @return string Путь к файлу
	 */
	public function GetPath()
	{
		return $this->group->GetPath() . '/' . $this->name . '.' . $this->extension;
	}

	/**
	 * Возвращает путь к временному файлу.
	 * @return string Путь к временному файлу
	 * @throws NoticeableException Если файл не является временным.
	 */
	public function GetTempPath()
	{
		if(!$this->tempFile)
		{
			throw new NoticeableException("Невозможно получить путь к временному файлу, так как файл не является временным.", 'Файл', LogLevel::ERROR);
		}

		return $this->tempPath;
	}

	/**
	 * Возвращает идентификатор файла.
	 * @return int Идентификатор файла.
	 * @throws NoticeableException Файл временный.
	 */
	public function GetId()
	{
		if($this->tempFile)
		{
			throw new NoticeableException("Невозможно получить идентификатор файла, так как файл временный.", 'Файл', LogLevel::ERROR);
		}

		return $this->id;
	}

	/**
	 * Возвращает название файла.
	 * @return string Название файла.
	 * @throws NoticeableException Временный файл.
	 */
	public function GetName()
	{
		if($this->tempFile)
		{
			throw new NoticeableException("Невозможно получить имя файла, так как файл временный.", 'Файл', LogLevel::ERROR);
		}

		return $this->name;
	}

	/**
	 * Возвращает расширение файла.
	 * @return string Расширение файла.
	 */
	public function GetExtension()
	{
		return $this->extension;
	}

	/**
	 * Возвращает идентификатор создателя файла.
	 * @return int|null Идентификатор создателя или null, если файл создан (загружен) не пользователем.
	 */
	public function GetCreatorId()
	{
		return $this->creatorId;
	}

	/**
	 * Извлекает из названия файла его расширение в нижнем регистре.
	 * @param string $path Название файла
	 * @return mixed Расширение файла
	 */
	public static function GetExtensionFromPath($path)
	{
		return strtolower(pathinfo($path, PATHINFO_EXTENSION));
	}

	/**
	 * Генерирует новое уникальное имя файла на основе заданной соли и идентификатора группы файлов.
	 * Длина полученного имени файла - 46 символов.
	 * @param int $groupId Идентификтор группы файлов.
	 * @return string Имя файла.
	 */
	public static function GenerateName($groupId)
	{
		global $files;

		return md5($files['file_name_salt'] . $groupId . time()) . date('dmYHis', time());
	}
}