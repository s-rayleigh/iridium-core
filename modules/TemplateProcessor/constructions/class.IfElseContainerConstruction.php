<?php

namespace modules\TemplateProcessor\constructions;

use Exception;
use modules\TemplateProcessor\TemplateProcessor;
use modules\TemplateProcessor\Construction;

class IfElseContainerConstruction extends Construction
{
	private $condition;
	
	public function __construct($condition)
	{
		if(count($condition) === 1 && $condition[0] === 'else')
		{
			$this->condition = new ExpressionConstruction(true);
		}
		else if($condition[0] === 'if')
		{
			$this->condition = new ExpressionConstruction(array_slice($condition, 1));
		}
		else if($condition[0] === 'else' && isset($condition[1]) && $condition[1] === 'if')
		{
			$this->condition = new ExpressionConstruction(array_slice($condition, 2));
		}
		else
		{
			throw new Exception("Неопознанная условная внутренняя конструкция.\nДанные конструкции:\n" . print_r($condition, true));
		}

	}

	public function IsMatchCondition($vars)
	{
		return $this->condition->GetResultData($vars);
	}
	
	public function ProcessContent($content)
	{
		TemplateProcessor::BuildSyntaxTree($content, $this);
	}
	
	public function GetResultData($vars)
	{
		$html = '';

		foreach($this->content as $childCon)
		{
			$html .= $childCon->GetResultData($vars);
		}

		return $html;
	}
}