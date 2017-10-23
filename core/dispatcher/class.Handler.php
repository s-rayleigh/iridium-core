<?php


namespace core\dispatcher;

use core\route\RouteData;

/**
 * Query handler.
 * @package core\dispatcher
 */
abstract class Handler
{
	/**
	 * @var RouteData Route data of the current handler.
	 */
	private $routeData;

	/**
	 * @var mixed Data for the query.
	 */
	private $queryData;

	/**
	 * Prepare to processing.
	 * Execution order: 1.
	 */
	protected function Prepare() { }

	/**
	 * Preprocessing.
	 * Execution order: 2.
	 */
	protected function Preprocess() { }

	/**
	 * Processing.
	 * Execution order: 3.
	 */
	protected abstract function Process();

	/**
	 * Postprocessing.
	 * Execution order: 4.
	 */
	protected function Postprocess() { }

	/**
	 * Determines is handler route contains specified component.
	 * @param string $component Component of the route.
	 * @return bool True, if handler route contains specified component.
	 */
	protected function RouteContainsComponent(string $component) : bool
	{
		return in_array($component, $this->routeData->pathComponents, true);
	}

	/**
	 * @return mixed|null Data of the query.
	 */
	protected function GetQueryData()
	{
		return $this->queryData;
	}

	/**
	 * Executes handler.
	 * @param RouteData $routeData Route data of the handler.
	 * @param mixed $queryData Data of the query.
	 */
	public final function Execute(RouteData $routeData, $queryData = null)
	{
		$this->routeData = $routeData;
		$this->queryData = $queryData;

		$this->Prepare();
		$this->Preprocess();
		$this->Process();
		$this->Postprocess();
	}
}