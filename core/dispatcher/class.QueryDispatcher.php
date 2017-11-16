<?php

namespace core\dispatcher;

use core\exceptions\DispatcherException;

/**
 * Class QueryDispatcher
 * @package core\dispatcher
 */
final class QueryDispatcher
{
	/**
	 * @var QueryType[] List of query types.
	 */
	private $queryTypes = [];

	public function RegisterQueryType(QueryType $queryType)
	{
		$this->queryTypes[] = $queryType;
	}

	public function Dispatch(string $queryTypeId, string $rawRoute, $data = null)
	{
		foreach($this->queryTypes as $qt)
		{
			if($qt->GetId() === $queryTypeId)
			{
				$queryType = $qt;
			}
		}

		if(!isset($queryType))
		{
			throw new \InvalidArgumentException("Query type with specified id '$queryTypeId' was not found.");
		}

		$route = $queryType->GetRouteData($rawRoute);

		if(empty($route->filePath) || !file_exists($route->filePath))
		{
			throw new DispatcherException("Handler file with specified route ($rawRoute) was not found.");
		}

		/** @noinspection PhpIncludeInspection */
		include_once $route->filePath;

		if(!class_exists($route->fullClass))
		{
			throw new DispatcherException("Handler class was not found for specified route ($rawRoute).");
		}

		if(!is_subclass_of($route->fullClass, '\core\dispatcher\Handler'))
		{
			throw new DispatcherException("Handler class must be subclass of the \core\dispacher\Handler class.\nRoute: $rawRoute");
		}

		$queryType->CallBeforeDispatch($route);
		(new $route->fullClass)->Execute($route, $data);
	}
}