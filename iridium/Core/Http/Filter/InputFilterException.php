<?php
/**
 * Input filter exception.
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
 * Input filter exception.
 * @package Iridium\Core\Http\Filter
 * @todo Move to Iridium\Core\Exceptions
 */
class InputFilterException extends ValueFilterException
{
	/**
	 * @var string Name of the value.
	 */
	private $valueName;

	/**
	 * @var int Type of the filter input.
	 * @see FilterInput
	 */
	private $inputType;

	/**
	 * Creates input filter exception.
	 * @param int $inputType Type of the input.
	 * @param string $valueName Name of the value.
	 * @param int $valueType Type of the value.
	 * @param int $filterOptions Options of the filter.
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @param \Throwable|null $previous Previous exception.
	 */
	public function __construct(int $inputType, string $valueName, int $valueType, int $filterOptions, string $message = '', int $code = 0, \Throwable $previous = null)
	{
		parent::__construct($valueType, $filterOptions, $message, $code, $previous);
		$this->inputType = $inputType;
		$this->valueName = $valueName;
	}

	/**
	 * @return string Name of the value.
	 */
	public function GetValueName() : string
	{
		return $this->valueName;
	}

	/**
	 * @return int Type of the input.
	 */
	public function GetInputType() : int
	{
		return $this->inputType;
	}


}