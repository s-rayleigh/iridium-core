<?php
/**
 * Value filter exception.
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
 * Value filter exception.
 * @package Iridium\Core\Http\Filter
 */
class ValueFilterException extends FilterException
{
	/**
	 * @var int Type of the value.
	 * @see ValueType
	 */
	private $valueType;

	/**
	 * @var int Filter options.
	 * @see FilterOption
	 */
	private $filterOptions;

	/**
	 * Creates value filter exception.
	 * @param int $valueType Type of the value.
	 * @param int $filterOptions Options of the filter.
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @param \Throwable|null $previous Previous exception.
	 */
	public function __construct(int $valueType, int $filterOptions, $message = '', $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->valueType = $valueType;
		$this->filterOptions = $filterOptions;
	}

	/**
	 * @return int Type of the value.
	 */
	public function GetValueType() : int
	{
		return $this->valueType;
	}

	/**
	 * @return int Options of the filter.
	 */
	public function GetFilterOptions() : int
	{
		return $this->filterOptions;
	}
}