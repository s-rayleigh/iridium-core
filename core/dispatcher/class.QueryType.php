<?php

namespace core\dispatcher;

use core\route\RouteBuilder;
use core\route\RouteData;

/**
 * Represents type of the query that routes by query dispatcher.
 * @package core\dispatcher
 */
final class QueryType extends RouteBuilder
{
	/**
	 * @var string Query type identifier.
	 */
	private $id = '';

	/**
	 * @var callable Callback thal called before query dispatch.
	 */
	private $beforeDispatch;

	/**
	 * Creates new query type.
	 * @param string $id Query type identifier.
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * Returns query type identifier.
	 * @return string Query type identifier.
	 */
	public function GetId() : string
	{
		return $this->id;
	}

	/**
	 * Sets callback function that called before query dispatch.
	 * @param callable $callback Callback.
	 * @return QueryType Query type.
	 */
	public function SetBeforeDispatch($callback) : self
	{
		if(!is_callable($callback))
		{
			throw new \InvalidArgumentException('Argument "callback" should be callable.');
		}

		$this->beforeDispatch = $callback;

		return $this;
	}

	/**
	 * Calls callback before query dispatch if it assigned.
	 * @param RouteData $routeData Route data of the query.
	 */
	public function CallBeforeDispatch(RouteData $routeData)
	{
		if(isset($this->beforeDispatch))
		{
			($this->beforeDispatch)($routeData);
		}
	}
}