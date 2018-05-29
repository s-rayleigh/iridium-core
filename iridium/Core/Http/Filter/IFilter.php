<?php
/**
 * Contains interface for creating filters.
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
 * Interface for creating filters.
 * @package Iridium\Core\Http\Filter
 */
interface IFilter
{
	/**
	 * Sets strict mode.
	 * @param bool $strict Strict mode.
	 */
	public function SetStrict(bool $strict = true);

	/**
	 * @return bool True if strict mode is enabled, otherwise returns false.
	 */
	public function IsStrict() : bool;

	/**
	 * Filters the passed value based on specified parameters.
	 * @param mixed $value Value.
	 * @param int $valueType Type of the value.
	 * @param mixed $default Default value. Returns if type of the value does not match specified type and if strict
	 * mode is disabled.
	 * @param int $options Filter options.
	 * @see ValueType To specify the $valueType argument.
	 * @see FilterOption To specify $options argument.
	 * @return mixed Value or default value of the specified type. Otherwise throws an exception.
	 */
	public function FilterValue($value, int $valueType, $default, int $options);

	/**
	 * Filters value from superglobal array based on specified type end options.
	 * @param int $filterInput Superglobal array. Use FilterInput for this argument.
	 * @param string $inputName Key for the superglobal array.
	 * @param int $valueType Type of the value.
	 * @param mixed $default Default value.
	 * @param int $options Filter options.
	 * @return array|bool|float|int|mixed|null|number|string Filtered value.
	 * @throws InputFilterException If value is required and no correct value is passed or exception thrown by value filter.
	 * @throws \UnexpectedValueException If specified type or input is not supported.
	 * @see ValueType To specify the $filterType argument.
	 * @see FilterOption To specify $options argument.
	 * @see FilterInput To specify $filterInput argument.
	 */
	public function FilterInput(
		int $filterInput,
		string $inputName,
		int $valueType,
		$default,
		int $options);
}