<?php


namespace core\exceptions;


use Exception;

/**
 * Ошибка доступа.
 * @package core\exceptions
 */
class AccessException extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}