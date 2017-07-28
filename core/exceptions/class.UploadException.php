<?php

namespace core\exceptions;

use core\log\LogLevel;

/**
 * Исключение загрузки файла(ов) на сервер
 */
class UploadException extends NoticeableException
{
	public function __construct($errorCode)
	{
		parent::__construct($this::ErrorCodeToMessage($errorCode), 'Ошибка загрузки', LogLevel::WARNING);
	}

	/**
	 * Преобразовывает код ошибки в сообщение
	 * @param int $errorCode Код ошибки
	 * @return string
	 */
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