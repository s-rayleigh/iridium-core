<?php

namespace core\dispatcher;

use core\route\RouteBuilder;

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
}