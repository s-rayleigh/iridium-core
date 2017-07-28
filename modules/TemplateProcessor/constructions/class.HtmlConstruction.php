<?php

namespace modules\TemplateProcessor\constructions;

use modules\TemplateProcessor\Construction;

class HtmlConstruction extends Construction
{
	public function __construct($content)
	{
		$this->content = $content;
	}
	
	public function GetResultData($vars)
	{
		return $this->content;
	}

	public function ProcessContent($content)
	{
		$this->content = $content;
	}
}