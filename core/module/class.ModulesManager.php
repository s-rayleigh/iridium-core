<?php
/**
 * Modules manager.
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
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */

namespace core\module;


use core\route\RouteBuilder;

/**
 * Modules manager.
 * @package core\module
 */
final class ModulesManager
{
	/**
	 * @var string Modules path.
	 */
	private $modulesRoute = '';

	private $configsPath = '';

	public function __construct() { }

	public function SetModulesRoute(string $route)
	{
		$this->modulesRoute = $route;
	}

	public function SetConfigsPath(string $path)
	{
		$this->configsPath = $path;
	}

	/**
	 * Loads specified modules.
	 * @param array $modules Modules to load.
	 * @throws \Exception
	 */
	public function LoadModules(array $modules)
	{
		$moduleRouteBuilder = (new RouteBuilder)->SetRawRoutePrefix($this->modulesRoute)->SetFilePrefix('class.');

		foreach($modules as $moduleName)
		{
			$routeData = $moduleRouteBuilder->GetRouteData("$moduleName.$moduleName");

			//Already loaded
			if(class_exists($routeData->fullClass))
			{
				continue;
			}

			if(!file_exists($routeData->filePath))
			{
				throw new \Exception("File of the module '$moduleName' was not found.");
			}

			/** @noinspection PhpIncludeInspection */
			include_once $routeData->filePath;

			if(!class_exists($routeData->fullClass))
			{
				throw new \Exception("Class of the module '$moduleName' was not found.");
			}

			if(!is_subclass_of($routeData->fullClass, '\core\module\IModule'))
			{
				throw new \Exception("Class of the module '$moduleName' must extends interface IModule.");
			}

			$configPath = (empty($this->configsPath) ? '' : $this->configsPath . '/') . strtolower($moduleName) . '.php';

			//If file does not exists, 'include' displays warning. We dont need this behaviour, so there is '@'
			/** @noinspection PhpIncludeInspection */
			$config = @include($configPath);

			if(!is_array($config))
			{
				$config = [];
			}

			$required = call_user_func($routeData->fullClass . '::GetRequiredModules', $config);

			if(is_array($required))
			{
				foreach($required as $requredModule)
				{
					$requiredModuleRoute = $moduleRouteBuilder->GetRouteData("$requredModule.$requredModule");
					if(!class_exists($requiredModuleRoute->fullClass))
					{
						throw new \Exception("Module $moduleName requires module $requredModule.");
					}
				}
			}

			call_user_func($routeData->fullClass . '::Init', $config);
		}
	}
}