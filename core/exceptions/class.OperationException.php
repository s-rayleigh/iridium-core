<?php

namespace core\exceptions;

use core\log\LogLevel;

/**
 * Исключение выполнения операции
 */
class OperationException extends NoticeableException
{
	public function __construct(array $operationError)
	{
		$title		= 'Ошибка';
		$logLevel	= LogLevel::DEBUG;

		if(isset($operationError['log_level']))
		{
			$logLevel = $operationError['log_level'];
		}

		if(isset($operationError['title']))
		{
			$title = $operationError['title'];
		}

		parent::__construct($operationError['error'], $title, $logLevel);
	}
}