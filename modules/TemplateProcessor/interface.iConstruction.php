<?php

namespace modules\TemplateProcessor;

interface iConstruction
{
	function __construct($constructionData);
	function GetResultData($vars);
}