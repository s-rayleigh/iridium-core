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
 * @copyright 2018 Vladislav Pashaiev
 * @license LGPL-3.0+
 */

namespace Iridium\Core\Module;

use Iridium\Core\Route\RouteBuilder;

/**
 * Modules manager.
 * @package Iridium\Core\Module
 */
final class ModulesManager
{
	/**
	 * @var string Path to the modules configuration files.
	 */
	private $configsPath = '';

	/**
	 * @var RouteBuilder Route builder.
	 */
	private $routeBuilder;

	/**
	 * Creates modules manager.
	 * @param RouteBuilder $routeBuilder Route builder.
	 */
	public function __construct(RouteBuilder $routeBuilder)
	{
		$this->routeBuilder = $routeBuilder;
	}

	/**
	 * Sets path to the modules configuration files.
	 * @param string $path Path to the modules configuration files.
	 */
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
		if(!is_a($this->routeBuilder, RouteBuilder::class))
		{
			throw new \Exception('Route builder should be setted.');
		}

		foreach($modules as $moduleName)
		{
			$routeData = $this->routeBuilder->Build("{$moduleName}.{$moduleName}");

			// Already loaded
			if(class_exists($routeData->fullClass))
			{
				continue;
			}

			if(!file_exists($routeData->filePath))
			{
				throw new \Exception("File of the module '{$moduleName}' was not found.");
			}

			include_once $routeData->filePath;

			if(!class_exists($routeData->fullClass))
			{
				throw new \Exception("Class of the module '{$moduleName}' was not found.");
			}

			if(!is_subclass_of($routeData->fullClass, IModule::class))
			{
				throw new \Exception("Class of the module '{$moduleName}' must extends interface IModule.");
			}

			$configPath = (empty($this->configsPath) ? '' : $this->configsPath . DIRECTORY_SEPARATOR) . strtolower($moduleName) . '.php';

			// If file does not exists, 'include' displays warning. We dont need this behaviour, so there is '@'
			$config = @include($configPath);

			if(!is_array($config))
			{
				$config = [];
			}

			$required = call_user_func($routeData->fullClass . '::GetRequiredModules', $config);

			if(is_array($required))
			{
				foreach($required as $requiredModule)
				{
					$requiredModuleRoute = $this->routeBuilder->Build("$requiredModule.$requiredModule");

					if(!class_exists($requiredModuleRoute->fullClass))
					{
						throw new \Exception("Module {$moduleName} requires module {$requiredModule}.");
					}
				}
			}

			call_user_func($routeData->fullClass . '::Init', $config);
		}
	}
}