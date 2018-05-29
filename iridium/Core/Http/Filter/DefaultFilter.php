<?php
/**
 * Default filter.
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

use Iridium\Core\ErrorCode;

/**
 * Default filter.
 * @package Iridium\Core\Http\Filter
 */
final class DefaultFilter implements IFilter
{
	/**
	 * @var bool Strict mode. If true, the strict mode is enabled.
	 */
	private $strict;

	/**
	 * Creates default filter.
	 * @param bool $strict Use strict mode.
	 */
	public function __construct($strict = false)
	{
		$this->strict = $strict;
	}

	/**
	 * Sets strict mode.
	 * @param bool $strict
	 */
	public function SetStrict(bool $strict = true)
	{
		$this->strict = $strict;
	}

	/**
	 * @return bool True if strict mode is enabled, otherwise returns false.
	 */
	public function IsStrict(): bool
	{
		return $this->strict;
	}

	/**
	 * Checks that the passed value is of the specified type.
	 * @param mixed $value Value.
	 * @param int $type Type.
	 * @return bool Result of the check.
	 * @see ValueType To specify the $type argument.
	 * @throws \UnexpectedValueException If the specified value type is not supported.
	 */
	private function CheckType($value, int $type) : bool
	{
		switch($type)
		{
			case ValueType::INT:
				return (string)(int)$value == $value;
			case ValueType::FLOAT:
				return (string)(float)$value == $value;
			case ValueType::UINT:
				return (string)abs((int)$value) == $value;
			case ValueType::UFLOAT:
				return (string)abs((float)$value) == $value;
			case ValueType::BOOL:
				return $value === '0' || $value === '1' || is_bool($value);
			case ValueType::ARR:
				return is_array($value);
			case ValueType::STRING:
				return is_string($value);
			default:
				throw new \UnexpectedValueException("Cannot check type of the value. Unsupported value type with number {$type}.");
		}
	}

	/**
	 * Casts passed value to the specified type.
	 * @param mixed $value Value.
	 * @param int $type Type to which cast the value.
	 * @return array|bool|float|int|number|string Value of the specified type.
	 * @see ValueType To specify the $type argument.
	 * @throws \UnexpectedValueException If the specified value type is not supported.
	 */
	private function Cast($value, int $type)
	{
		switch($type)
		{
			case ValueType::INT:
				return (int)$value;
			case ValueType::FLOAT:
				return (float)$value;
			case ValueType::UINT:
				return abs((int)$value);
			case ValueType::UFLOAT:
				return abs((float)$value);
			case ValueType::BOOL:
				return (bool)$value;
			case ValueType::ARR:
				return (array)$value;
			case ValueType::STRING:
				return (string)$value;
			default:
				throw new \UnexpectedValueException("Cannot cast the value to the specified type. Unsupported value type with number {$type}.");
		}
	}

	/**
	 * Filters passed value based on specified parameters.
	 * @param mixed $value Value.
	 * @param int $filterType Type of the value.
	 * @param mixed $default Default value.
	 * @param int $options Filter options.
	 * @return array|bool|float|int|mixed|null|number|string Filtered value.
	 * @throws ValueFilterException If value is required and no correct value is passed.
	 * @throws \UnexpectedValueException If specified type is not supported.
	 * @see ValueType To specify the $filterType argument.
	 * @see FilterOption To specify $options argument.
	 */
	public function FilterValue($value, int $filterType, $default = null, int $options = 0)
	{
		if(!isset($value))
		{
			if($options & FilterOption::REQUIRED)
			{
				throw new ValueFilterException($filterType, $options, 'Value is required by value filter.', ErrorCode::VALUE_REQUIRED);
			}
			else
			{
				return $default;
			}
		}

		if(!$this->CheckType($value, $filterType))
		{
			if($this->strict || $options & FilterOption::STRICT)
			{
				throw new ValueFilterException($filterType, $options, 'Type of the value does not match required type.', ErrorCode::WRONG_TYPE);
			}
			else
			{
				return $default;
			}
		}

		$value = $this->Cast($value, $filterType);

		//Additional string filtering
		if($filterType === ValueType::STRING)
		{
			//trim spaces, convert symbols to the html entities, replace unvalid symbols
			$value = htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE, 'UTF-8');

			if($options & FilterOption::MULTIBITE)
			{
				$value = preg_replace('/[[^:print:]]/u', '', $value); //Only printable UTF-8 (u flag) symbols
			}
			else
			{
				$value = preg_replace('/[^\x20-\x7E]/', '', $value); //Only 20-7E (32-126) symbols from ASCII table
			}
		}

		return $value;
	}

	/**
	 * Filters value from superglobal array based on specified type end options.
	 * @param int $filterInput Superglobal array. Use FilterInput for this argument.
	 * @param string $inputName Key for the superglobal array.
	 * @param int $filterType Type of the value.
	 * @param mixed $default Default value.
	 * @param int $options Filter options.
	 * @return array|bool|float|int|mixed|null|number|string Filtered value.
	 * @throws InputFilterException If value is required and no correct value is passed or exception thrown by value filter.
	 * @throws \UnexpectedValueException If specified type or input is not supported.
	 * @see ValueType To specify the $filterType argument.
	 * @see FilterOption To specify $options argument.
	 * @see FilterInput To specify $filterInput argument.
	 */
	public function FilterInput(int $filterInput, string $inputName, int $filterType, $default = null, int $options = 0)
	{
		switch($filterInput)
		{
			case FilterInput::GET:
				$arr = $_GET;
				break;
			case FilterInput::POST:
				$arr = $_POST;
				break;
			case FilterInput::COOKIE:
				$arr = $_COOKIE;
				break;
			case FilterInput::REQUEST:
				$arr = $_REQUEST;
				break;
			default:
				throw new \UnexpectedValueException("Cannot filter input. Unsupported filter input type with number {$filterInput}.");
		}

		if(!isset($arr[$inputName]))
		{
			if($options & FilterOption::REQUIRED)
			{
				throw new InputFilterException($filterType, $inputName, $filterInput, $options, "Value is required by input filter.", ErrorCode::VALUE_REQUIRED);
			}
			else
			{
				return $default;
			}
		}

		try
		{
			return $this->FilterValue($arr[$inputName], $filterType, $default, $options);
		}
		catch(ValueFilterException $e)
		{
			throw new InputFilterException($filterType, $inputName, $filterInput, $options, "Exception is thrown while filtering value.", ErrorCode::VALUE_FILTER, $e);
		}
	}
}