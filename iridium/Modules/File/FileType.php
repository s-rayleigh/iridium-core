<?php
/**
 * File type.
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
 * Тип файла
 * @package Iridium\Modules\File
 */
final class FileType
{
	private function __construct() { }

	/**
	 * Видеофайл.
	 */
	const VIDEO = 0;

	/**
	 * Изображение.
	 */
	const IMAGE = 1;

	/**
	 * Возвращает название директории, в которой хранятся файлы указанного типа
	 * @param    int $fileType Тип файла (FileType)
	 * @return    string                Название директории указанного типа файла
	 * @throws NoticeableException
	 */
	public static function GetFolder($fileType)
	{
		switch($fileType)
		{
			case FileType::VIDEO:
				return 'video';
			case FileType::IMAGE:
				return 'image';
			default:
				throw new NoticeableException("Невозможно определить директорию указанного типа файла $fileType!", 'Тип файла', LogLevel::FATAL);
		}
	}
}