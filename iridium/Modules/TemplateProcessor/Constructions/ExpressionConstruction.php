<?php
/**
 * Expression construction.
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

use Iridium\Modules\TemplateProcessor\iConstruction;

class ExpressionConstruction implements iConstruction
{
	private $expression;
	private $builtedExpression;

	public function __construct($expression)
	{
		// Log::Debug("Создано новое выражение: " . print_r($expression, true));
		if(is_array($expression) || is_bool($expression))
		{
			$this->expression = $expression;
		}
		else
		{
			//Разбиваем текст выражения по пробелу
			$this->expression = explode(' ', $expression);
		}
	}

	public function GetResultData($vars)
	{
		if(is_bool($this->expression))
		{
			return $this->expression;
		}

		$this->Build($vars);

		ob_start(null, 0, PHP_OUTPUT_HANDLER_REMOVABLE);

		//eval('if((bool)(' . $this->builtedExpression . ')) { echo 1; } else { echo 0; }');

		// Log::Debug("expr: " . 'echo ' . $this->builtedExpression . ';');

		eval('echo ' . $this->builtedExpression . ';');

		return (bool)ob_get_clean();
	}

	private function Build($vars)
	{
		$this->builtedExpression = $this->expression;

		//Перебор всех элементов выражения
		for($i = 0; $i < count($this->expression); $i++)
		{
			switch($this->expression[$i])
			{
				//Все символы оставляем нетронутыми
				case '&&':
				case '||':
				case 'and':
				case 'or':
				case '>':
				case '<':
				case '>=':
				case '<=':
				case '==':
				case '!=':
					break;

				//Если элемент не входит в список допустимых сочетаний символов, значит это переменная или константа
				default:

					$exprPartLen = strlen($this->expression[$i]);

					$ld = ltrim($this->expression[$i], '(');
					$trimmedExpressionPart = rtrim($ld, ')');

					if(is_numeric($trimmedExpressionPart))
					{
						break;
					}

					$openCount = $exprPartLen - strlen($ld);
					$closeCount = $exprPartLen - strlen($trimmedExpressionPart) + $openCount;

					$valueConstr = new ValueConstruction($trimmedExpressionPart);
					$trimmedExpressionPart = $valueConstr->GetResultData($vars);

					if($trimmedExpressionPart === true)
					{
						$trimmedExpressionPart = 'true';
					}
					else if($trimmedExpressionPart === false)
					{
						$trimmedExpressionPart = 'false';
					}
					else if(gettype($trimmedExpressionPart) === 'string')
					{
						$trimmedExpressionPart = "'$trimmedExpressionPart'";
					}

					$this->builtedExpression[$i] = str_repeat('(', $openCount) . $trimmedExpressionPart . str_repeat(')', $closeCount);

					break;
			}
		}

		$this->builtedExpression = implode(' ', $this->builtedExpression);
	}
}