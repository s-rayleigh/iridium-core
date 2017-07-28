<?php


namespace core\route;


/**
 * Route data.
 * @package core\dispatcher
 */
final class RouteData
{
	/**
	 * @var string Handler class name.
	 */
	public $class;

	/**
	 * @var string Handler full class name with namespace.
	 */
	public $fullClass;

	/**
	 * @var string Path to the file that contains handler class.
	 */
	public $filePath;

	/**
	 * @var array Components of the handler path.
	 */
	public $pathComponents;
}