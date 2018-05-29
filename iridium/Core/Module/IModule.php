<?php
/**
 * IModule interface.
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

/**
 * Interface for the module main file.
 * @package Iridium\Core\Module
 */
interface IModule
{
	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	static function Init(array $moduleConfig);

	/**
	 * Should return an array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules() : array;
}