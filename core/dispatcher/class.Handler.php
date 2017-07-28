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
	 * Подготовка сущности к обработке.
	 * Порядок выполнения: 1.
	 */
	protected function Prepare() { }

	/**
	 * Предобработка.
	 * Порядок выполнения: 2.
	 */
	protected function Preprocess() { }

	/**
	 * Обработка.
	 * Порядок выполнения: 3.
	 */
	protected abstract function Process();

	/**
	 * Постобработка.
	 * Порядок выполнения: 4.
	 */
	protected function Postprocess() { }

	protected function RouteContainsComponent(string $component)
	{
		return in_array($component, $this->routeData->pathComponents, true);
	}

	public final function Execute(RouteData $routeData)
	{
		$this->routeData = $routeData;

		$this->Prepare();
		$this->Preprocess();
		$this->Process();
		$this->Postprocess();
	}
}