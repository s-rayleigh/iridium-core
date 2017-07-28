<?php

namespace modules\TemplateProcessor\constructions;

use Exception;
use modules\TemplateProcessor\TemplateProcessor;
use modules\TemplateProcessor\Construction;

final class ForeachConstruction extends Construction
{
	private $varName;
	private $arrayVarName;

	public function __construct($data)
	{
		//Проверяем наличие слова in
		if($data[2] != 'in')
		{
			throw new Exception("Неопознанный синтаксис оператора foreach!");
		}

		//Проверяем правильность переменной получения данных
		if(!TemplateProcessor::CheckValidVariableName($data[1]))
		{
			throw new Exception("Ошибка в имени переменной получения данных цикла foreach.\nНазвание переменной: " . $data[1]);
		}

		$this->varName		= $data[1];
		$this->arrayVarName	= new ValueConstruction($data[3]);
		$this->arrayVarName->CanReturnArray(true);
	}
	
	public function GetResultData($vars)
	{
		$arr = $this->arrayVarName->GetResultData($vars);

		if(!is_array($arr))
		{
			throw new Exception("Переменная не является массивом.");
		}

		$html = '';

		foreach($arr as $element)
		{
			$vars[$this->varName] = $element;
			foreach($this->content as $contentPart)
			{
				$html .= $contentPart->GetResultData($vars);
			}
		}

		return $html;
	}
	
	public function ProcessContent($content)
	{
		TemplateProcessor::BuildSyntaxTree($content, $this);
	}
}