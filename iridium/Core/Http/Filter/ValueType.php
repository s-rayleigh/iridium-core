<?php
/**
 * Value types for the filtration.
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

namespace Iridium\Core\Http\Filter;

/**
 * Value type for the filtration.
 * @package Iridium\Core\Http\Filter
 */
final class ValueType
{
	/**
	 * Signed integer number.
	 */
	const INT = 0;

	/**
	 * Unsigned integer number.
	 */
	const UINT = 1;

	/**
	 * Signed float number.
	 */
	const FLOAT = 2;

	/**
	 * Unsigned float number.
	 * Yeah, I know it's weird...
	 */
	const UFLOAT = 3;

	/**
	 * String.
	 */
	const STRING = 4;

	/**
	 * Array.
	 */
	const ARR = 5;

	/**
	 * Boolean.
	 */
	const BOOL = 6;
}