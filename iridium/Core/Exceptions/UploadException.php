<?php
/**
 * Upload exception.
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

namespace Iridium\Core\Exceptions;

use Iridium\Core\Log\LogLevel;

/**
 * Upload exception.
 * @package Iridium\Core\Exceptions
 * @deprecated Need to be rewritten.
 */
class UploadException extends NoticeableException
{
	public function __construct($errorCode)
	{
		parent::__construct($this::ErrorCodeToMessage($errorCode), 'Ошибка загрузки', LogLevel::WARNING);
	}

	private static function ErrorCodeToMessage($errorCode)
	{
		switch($errorCode)
		{
			case UPLOAD_ERR_INI_SIZE:
				return 'Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.';
			case UPLOAD_ERR_FORM_SIZE:
				return 'Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.';
			case UPLOAD_ERR_PARTIAL:
				return 'Загружаемый файл был получен только частично.';
			case UPLOAD_ERR_NO_FILE:
				return 'Файл не был загружен.';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Отсутствует временная папка.';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Не удалось записать файл на диск.';
			case UPLOAD_ERR_EXTENSION:
				return 'PHP-расширение остановило загрузку файла.';
			default:
				return 'Неизвестная ошибка загрузки файла.';
		}
	}
}