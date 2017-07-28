<?php

namespace modules\TemplateProcessor\constructions;

use modules\TemplateProcessor\iConstruction;

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
					continue;

				//Если элемент не входит в список допустимых сочетаний символов, значит это переменная или константа
				default:

					$exprPartLen = strlen($this->expression[$i]);

					$ld = ltrim($this->expression[$i], '(');
					$trimmedExpressionPart = rtrim($ld, ')');

					if(is_numeric($trimmedExpressionPart))
					{
						continue;
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