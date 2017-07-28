<?php

namespace core\route;

/**
 * Route builder.
 * @package core\route
 */
class RouteBuilder
{
	/**
	 * Separator for the components of raw route.
	 */
	public const RAW_ROUTE_SEPARATOR = '.';

	/**
	 * @var string Route raw prefix.
	 * Components must be separated by RAW_ROUTE_SEPARATOR.
	 */
	private $routePrefix = '';

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
	 * @return RouteData Route data for specified raw route.
	 */
	public function GetRouteData(string $rawRoute) : RouteData
	{
		$route = new RouteData();

		$route->pathComponents = explode('.', trim($this->routePrefix, self::RAW_ROUTE_SEPARATOR) . self::RAW_ROUTE_SEPARATOR . trim($rawRoute, self::RAW_ROUTE_SEPARATOR));
		$handlerName = ucwords(end($route->pathComponents));
		$route->pathComponents = array_splice($route->pathComponents, 0, -1);

		$route->class = $this->classPrefix . $handlerName . $this->classSuffix;
		$route->fullClass = implode('\\', $route->pathComponents) . '\\' . $route->class;
		$route->filePath = implode(DIRECTORY_SEPARATOR, $route->pathComponents) . DIRECTORY_SEPARATOR. $this->filePrefix . $handlerName . $this->fileSuffix . '.' . $this->fileExtension;

		return $route;
	}
}