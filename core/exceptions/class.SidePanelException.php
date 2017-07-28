<?php


namespace core\exceptions;

/**
 * Исключение боковой панели.
 * @package core\exceptions
 */
class SidePanelException extends \Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}