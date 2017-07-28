<?php


namespace core\exceptions;

/**
 * Исключение диспетчера запросов.
 * @package core\exceptions
 */
class DispatcherException extends \Exception
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}