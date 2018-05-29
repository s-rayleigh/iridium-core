<?php
/**
 * Request handler.
 * This file is part of Iridium Core project.
 *
 * Iridium Core is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Iridium Core is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Iridium Core. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author rayleigh <rayleigh@protonmail.com>
 * @copyright 2018 Vladislav Pashaiev
 * @license LGPL-3.0+
 */

namespace Iridium\Core\Dispatcher;

use Iridium\Core\Route\Route;

/**
 * Request handler.
 * @package Iridium\Core\Dispatcher
 */
abstract class Handler
{
	/**
	 * @var Route Route data of the current handler.
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
	 * @param Route $routeData Route data of the handler.
	 * @param mixed $queryData Data of the query.
	 */
	public final function Execute(Route $routeData, $queryData = null)
	{
		$this->routeData = $routeData;
		$this->queryData = $queryData;

		$this->Prepare();
		$this->Preprocess();
		$this->Process();
		$this->Postprocess();
	}
}