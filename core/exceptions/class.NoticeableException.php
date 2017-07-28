<?php

namespace core\exceptions;

use core\log\Log;

/**
 * Заметное исключение. Имеет методы для логирования и отправки пользователю в качестве ошибки
 */
class NoticeableException extends \Exception
{
	protected $logLevel;
	private $title;

	/**
	 * NoticeableException constructor.
	 * @param string $message
	 * @param int    $title
	 * @param mixed  $logLevel
	 */
	public function __construct($message, $title, $logLevel)
	{
		parent::__construct($message);

		$this->title    = $title;
		$this->logLevel = $logLevel;
	}

	/**
	 * Устанавливает уровень логирования исключкения
	 * @param mixed $logLevel Уровень логирования
	 */
	public function SetLogLevel($logLevel)
	{
		$this->logLevel = $logLevel;
	}

	/**
	 * Логирует исключение с указанным уровнем логирования
	 */
	public function LogException()
	{
		Log::LogMessage("Не удалось выполнить запрос, так как было вызвано исключение!\n" . $this->getMessage(), $this->logLevel);
	}

	/**
	 * Возвращает данные исключения, подготовленные для отправки клиенту
	 * @return array
	 */
	public function PackResponse()
	{
		return array('error' => $this->getMessage(), 'title' => $this->title);
	}
}