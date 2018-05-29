<?php
/**
 * Email letter.
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
 * Email.
 * @package Iridium\Core
 */
class EmailLetter
{
	/**
	 * @var string Тема.
	 */
	private $subject;

	/**
	 * @var string Содержание.
	 */
	private $content;

	/**
	 * @var array Заголовки.
	 */
	private $headers;

	/**
	 * @var array Получатели письма.
	 */
	private $recipients;

	/**
	 * Создает письмо электронной почты.
	 * @param string $content Содержание.
	 * @param string $subject Тема.
	 */
	public function __construct($content, $subject = 'No subject')
	{
		$this->headers = [];
		$this->subject = $subject;
		$this->content = $content;

		if(empty($_SERVER['HTTP_HOST']))
		{
			$host = SITE_DOMAIN;
		}
		else
		{
			$host = $_SERVER['HTTP_HOST'];
		}

		$this->AddHeader('MIME-Version: 1.0');
		$this->AddHeader("Content-Type: text/html; charset=UTF-8");
		$this->AddHeader("From: noreply@$host");
	}

	/**
	 * Добавляет заголовок.
	 * @param string $header Заголовок.
	 */
	protected function AddHeader($header)
	{
		$this->headers[] = $header;
	}

	/**
	 * Добалвяет получателя.
	 * @param string $email Адрес электронной почты получателя.
	 * @param string $name Имя получателя.
	 */
	public function AddRecipient($email, $name = '')
	{
		if(empty($name))
		{
			$this->recipients[] = $email;
		}
		else
		{
			$this->recipients[] = "$name <$email>";
		}
	}

	/**
	 * Отправляет письмо.
	 */
	public function Send()
	{
		$recipients = implode(', ', $this->recipients);
		$headers    = implode("\r\n", $this->headers);

		mail($recipients, $this->subject, $this->content, $headers);
	}
}