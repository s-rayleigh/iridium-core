<?php
/**
 * String tools.
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

namespace Iridium\Core\Tools;

/**
 * String tools.
 * @package Iridium\Core\Tools
 */
final class StringTools extends Tools
{
	/**
	 * Separator symbol for the snake_case.
	 */
	const SNAKE_CASE_SEPARATOR = '_';

	/**
	 * Converts string in the snake_case to the camelCase.
	 * @param string $str String in snake_case.
	 * @return string String in camelCase.
	 */
	public static function SnakeToCamelCase(string $str): string
	{
		return str_replace(self::SNAKE_CASE_SEPARATOR, '', lcfirst(ucwords($str, self::SNAKE_CASE_SEPARATOR)));
	}

	/**
	 * Detects is $haystack starts with $needle.
	 * @param string $haystack The string to search in.
	 * @param string $needle The string to search at beginning of the $haystack.
	 * @return bool True, if $haystack starts with $needle.
	 */
	public static function StartsWith(string $haystack, string $needle): bool
	{
		return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	/**
	 * Detects is $haystack ends with $needle.
	 * @param string $haystack The string to search in.
	 * @param string $needle The string to search at ending of the $haystack.
	 * @return bool True, if $haystack ends with $needle.
	 */
	public static function EndsWith(string $haystack, string $needle): bool
	{
		return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}