<?php
/**
 * Request dispatcher.
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

use Iridium\Core\Exceptions\DispatcherException;

/**
 * Request dispatcher.
 * @package Iridium\Core\Dispatcher
 */
final class RequestDispatcher
{
	/**
	 * @var RequestType[] List of request types.
	 */
	private $requestTypes = [];

	/**
	 * Registers the request type.
	 * @param RequestType $requestType
	 */
	public function RegisterRequestType(RequestType $requestType)
	{
		$this->requestTypes[] = $requestType;
	}

	/**
	 * Dispatch request.
	 * @param string $requestTypeId Identifier of the request type.
	 * @param string $rawRoute Route of the request.
	 * @param null $data Data.
	 * @throws DispatcherException
	 */
	public function Dispatch(string $requestTypeId, string $rawRoute, $data = null)
	{
		foreach($this->requestTypes as $qt)
		{
			if($qt->GetId() === $requestTypeId)
			{
				$requestType = $qt;
			}
		}

		if(!isset($requestType))
		{
			throw new \InvalidArgumentException("Request type with specified id '{$requestTypeId}' was not found.");
		}

		$route = $requestType->Build($rawRoute);

		if(empty($route->filePath) || !file_exists($route->filePath))
		{
			throw new DispatcherException("Handler file with specified route ({$rawRoute}) was not found.");
		}

		/** @noinspection PhpIncludeInspection */
		include_once $route->filePath;

		if(!class_exists($route->fullClass))
		{
			throw new DispatcherException("Handler class was not found for specified route ($rawRoute).");
		}

		if(!is_subclass_of($route->fullClass, Handler::class))
		{
			throw new DispatcherException("Handler class must be subclass of the \core\dispacher\Handler class.\nRoute: $rawRoute");
		}

		$requestType->CallBeforeDispatch($route);
		(new $route->fullClass)->Execute($route, $data);
	}
}