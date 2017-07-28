<?php

namespace modules\TemplateProcessor\constructions;

use modules\TemplateProcessor\Construction;

final class MainConstruction extends Construction
{
	public function __construct($data) { }
	
	public function GetResultData($vars)
	{
		$html = '';

		foreach($this->content as $contentPart)
		{
			$html .= $contentPart->GetResultData($vars);
		}

		return $html;
	}
}