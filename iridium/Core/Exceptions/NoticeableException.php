<?php
/**
 * Noticeable exception.
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

use Iridium\Core\Log\Log;

/**
 * Noticeable exception.
 * @package Iridium\Core\Exceptions
 * @deprecated Will be removed in future versions.
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