<?php
/**
 * Request type.
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

use Iridium\Core\Route\RouteBuilder;
use Iridium\Core\Route\Route;

/**
 * Represents type of the request that routes by request dispatcher.
 * @package Iridium\Core\Dispatcher
 */
final class RequestType extends RouteBuilder
{
	/**
	 * @var string Identifier.
	 */
	private $id;

	/**
	 * @var callable Callback thal called before request dispatch.
	 */
	private $beforeDispatch;

	/**
	 * Creates new request type.
	 * @param string $id Identifier.
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * Returns identifier.
	 * @return string Identifier.
	 */
	public function GetId() : string
	{
		return $this->id;
	}

	/**
	 * Sets callback function that called before request dispatch.
	 * @param callable $callback Callback.
	 * @return RequestType Request type.
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
	 * Calls callback before request dispatch if it assigned.
	 * @param Route $route Route data of the request.
	 */
	public function CallBeforeDispatch(Route $route)
	{
		if(isset($this->beforeDispatch))
		{
			($this->beforeDispatch)($route);
		}
	}

	/**
	 * Clones request type.
	 * @param string $id Identifier of the clone.
	 * @return RequestType Clone.
	 */
	public function Clone(string $id) : self
	{
		$clone = clone $this;
		$clone->id = $id;
		return clone $clone;
	}
}