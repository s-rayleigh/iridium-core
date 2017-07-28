<?php


namespace core;

/**
 * Письмо электронной почты.
 * @package core
 * @author rayleigh <rayleigh@protonmail.ch>
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