<?php
/**
 * Route builder.
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

namespace Iridium\Core\Route;

/**
 * Route builder.
 * @package Iridium\Core\Route
 */
class RouteBuilder
{
	/**
	 * Separator for the components of raw route.
	 */
	const RAW_ROUTE_SEPARATOR = '.';

	/**
	 * @var string Route raw prefix.
	 * Components must be separated by RAW_ROUTE_SEPARATOR.
	 */
	private $routePrefix = '';

	/**
	 * @var string File path prefix.
	 */
	private $pathPrefix = '';

	/**
	 * @var string Class namespace prefix.
	 */
	private $namespacePrefix = '';

	/**
	 * @var string File name prefix.
	 */
	private $filePrefix = '';

	/**
	 * @var string File name suffix.
	 */
	private $fileSuffix = '';

	/**
	 * @var string File extension.
	 */
	private $fileExtension = 'php';

	/**
	 * @var string Class name prefix.
	 */
	private $classPrefix = '';

	/**
	 * @var string Class name suffix.
	 */
	private $classSuffix = '';

	/**
	 * Sets raw route preffix.
	 * Components must be separated by RAW_ROUTE_SEPARATOR.
	 * @param string $prefix Preffix for the route.
	 * @return self Route builder.
	 */
	public function SetRawRoutePrefix(string $prefix) : self
	{
		$this->routePrefix = $prefix;
		return $this;
	}

	public function SetPathPrefix(string $prefix) : self
	{
		$this->pathPrefix = $prefix;
		return $this;
	}

	public function SetNamespacePrefix(string $prefix) : self
	{
		$this->namespacePrefix = $prefix;
		return $this;
	}

	/**
	 * Sets file extension.
	 * @param string $extension File extension. Default: php.
	 * @return self Route builder.
	 */
	public function SetFileExtension(string $extension) : self
	{
		$this->fileExtension = $extension;
		return $this;
	}

	/**
	 * Sets file name preffix.
	 * @param string $prefix File name preffix.
	 * @return self Route builder.
	 */
	public function SetFilePrefix(string $prefix) : self
	{
		$this->filePrefix = $prefix;
		return $this;
	}

	/**
	 * Sets file name suffix.
	 * @param string $suffix File name suffix.
	 * @return self Route builder.
	 */
	public function SetFileSuffix(string $suffix) : self
	{
		$this->fileSuffix = $suffix;
		return $this;
	}

	/**
	 * Sets class name prefix.
	 * @param string $prefix Class name prefix.
	 * @return self Route builder.
	 */
	public function SetClassPrefix(string $prefix) : self
	{
		$this->classPrefix = $prefix;
		return $this;
	}

	/**
	 * Sets class name suffix.
	 * @param string $suffix Class name suffix.
	 * @return self Route builder.
	 */
	public function SetClassSuffix(string $suffix) : self
	{
		$this->classSuffix = $suffix;
		return $this;
	}

	/**
	 * Generates and returns route data.
	 * @param string $rawRoute Raw route.
	 * Components must be separated by RAW_ROUTE_SEPARATOR.
	 * @return Route Route data for specified raw route.
	 */
	public function Build(string $rawRoute) : Route
	{
		$route = new Route();

		$route->pathComponents = explode(self::RAW_ROUTE_SEPARATOR, trim($this->routePrefix, self::RAW_ROUTE_SEPARATOR));
		$route->pathComponents = array_merge($route->pathComponents, explode(self::RAW_ROUTE_SEPARATOR, trim($rawRoute, self::RAW_ROUTE_SEPARATOR)));

		$handlerName = ucwords(end($route->pathComponents));
		$route->pathComponents = array_splice($route->pathComponents, 0, -1);

		$route->class = $this->classPrefix . $handlerName . $this->classSuffix;
		$route->fullClass = (empty($this->namespacePrefix) ? '' : trim($this->namespacePrefix, NAMESPACE_SEPARATOR) . NAMESPACE_SEPARATOR)
			. implode(NAMESPACE_SEPARATOR, $route->pathComponents)
			. NAMESPACE_SEPARATOR
			. $route->class;
		$route->filePath = ROOT_PATH
			. DIRECTORY_SEPARATOR
			. (empty($this->pathPrefix) ? '' : trim($this->pathPrefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
			. implode(DIRECTORY_SEPARATOR, $route->pathComponents)
			. DIRECTORY_SEPARATOR
			. $this->filePrefix . $handlerName . $this->fileSuffix . '.' . $this->fileExtension;

		return $route;
	}
}