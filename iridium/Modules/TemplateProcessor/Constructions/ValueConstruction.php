<?php
/**
 * Value construction.
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

namespace Iridium\Modules\TemplateProcessor\Constructions;

use Exception;
use Iridium\Modules\TemplateProcessor\iConstruction;
use Iridium\Modules\TemplateProcessor\TemplateProcessor;

final class ValueConstruction implements iConstruction
{
	private $variableData;
	private $canReturnArray = false;

	//Указать, что в результате конструкция может вернуть массив. В противном случае массив будет преобразован в текст
	public function CanReturnArray($can)
	{
		$this->canReturnArray = $can;
	}

	//Построение конструкции
	public function __construct($data)
	{
		if(is_array($data))
		{ $varData = $data[1]; }
		else
		{ $varData = $data; }

		//Ищем квадратные скобочки
		$openSqrBracketsPos		= FoundAllSubstrPos($varData, '[');
		$closeSqrBracketsPos	= FoundAllSubstrPos($varData, ']');

		//Если есть несовпадение квадратных скобочек, бросаем исключение
		if($openSqrBracketsPos !== $closeSqrBracketsPos && count($openSqrBracketsPos) !== count($closeSqrBracketsPos))
		{
			throw new Exception("Несоответствие квадратных скобочек в названии переменной.\nЗаданное название переменной:\n" . print_r($varData, true));
		}

		//Массив будет заполнен частями получения значения
		$this->variableData = array();

		if($openSqrBracketsPos === false)
		{
			//Формируем часть и добавляем в массив
			$this->variableData[] = self::FormPart($varData, true);
		}
		else
		{
			//Имя переменной
			$this->variableData[] = self::FormPart(substr($varData, 0, $openSqrBracketsPos[0]), true);

			//Перебор частей конструкции, не включая 1-ю
			for($i = 0; $i < count($openSqrBracketsPos); $i++)
			{
				$varDataPart = substr($varData, $openSqrBracketsPos[$i] + 1, $closeSqrBracketsPos[$i] - $openSqrBracketsPos[$i] - 1);

				if(strlen($varDataPart) === 0)
				{
					throw new Exception("Не задан индекс массива!\nДанные переменной: $varData");
				}

				$this->variableData[] = self::FormPart($varDataPart);
			}
		}

	}

	//Формирует массив-часть конструкции получения значения.
	//Переменнвя $var должна содержать строковое представление части конструкции или литерал.
	//Если $single = true, то для переменной не будет создана конструкция, т. е. будем считать что там не будет индексом массива и т. п.
	//Это может понадобиться, если данная (текущая) конструкция содержит только получение значение одной переменной напрямую
	private static function FormPart($var, $single = false)
	{
		if(self::IsStringLiteral($var))			//Строка
		{
			//Проверяем строковый литерал на правильность
			if(substr_count($var, "'") - substr_count($var, "\\'") !== 2
			&& substr_count($var, '"') - substr_count($var, '\\"') !== 2)
			{
				throw new Exception("Задан ошибочный строковый литерал.\nЛитерал: $var");
			}

			//Возвращаем строковый литерал как часть конструкции
			return array(2, substr($var, 1, strlen($var) - 2));
		}
		else if(self::IsNumericLiteral($var))	//Число
		{
			//Возвращаем числовой литерал как часть конструкции
			return array(1, $var);
		}
		else if(self::IsBooleanLiteral($var))	//Булево
		{
			//Возвращаем булевый литерал как часть конструкции
			return array(3, $var === 'true');
		}
		else									//Переменная
		{
			//Проверка имени переменной
			if(!TemplateProcessor::CheckValidVariableName($var))
			{
				throw new Exception("Ошибка формата переменной.\nИмя переменной: $var");
			}

			//Возвращаем имя переменной или конструкцию получения значения по имени переменной как часть констуркции
			return array(0, $single ? $var : new ValueConstruction($var));
		}
	}

	//Проверяет, является-ли $var строковым литералом
	private static function IsStringLiteral($var)
	{
		//Проверяется содержание одинарных и двойных кавычек на краях строки
		return $var[0] === "'" && $var[strlen($var) - 1] === "'"
			|| $var[0] === '"' && $var[strlen($var) - 1] === '"';
	}

	//Првоеряет, является-ли $var числовым литералом
	private static function IsNumericLiteral($var)
	{
		return is_numeric($var);
	}

	//Проверяет, является-ли $var булевым литералом
	private static function IsBooleanLiteral($var)
	{
		return $var === 'true' || $var === 'false';
	}
	
	public function GetResultData($vars)
	{

		$result = $vars;

		for($i = 0; $i < count($this->variableData); $i++)
		{
			list($type, $data) = $this->variableData[$i];

			switch($type)
			{
				case 0:    //Переменная
					if($i !== 0)
					{
						$data = $data->GetResultData($vars);
					}

					if(!isset($result[$data]))
					{
						throw new Exception("Не найден ключ массива: ". print_r($data,true) . ".\nКлюч был получен динамически.\nИндекс части: $i");
					}

					$result = $result[$data];
					break;
				case 1: //Числовой литерал
				case 2: //Строковый литерал
				case 3: //Булево
					if($i === 0)
					{
						$result = $data;
					}
					else
					{
						if(!isset($result[$data]))
						{
							throw new Exception("Не найден ключ массива: $data\nКлюч - это литерал.");
						}

						$result = $result[$data];
					}
					break;
				default:
					throw new Exception("Обработчик шаблона не знает что делать с индексом типа $type.");
					break;
			}
		}

		if(is_array($result) && !$this->canReturnArray)
		{
			return print_r($result, true);
		}

		return $result;
	}
}