<?php

namespace modules\TemplateProcessor\constructions;

use modules\TemplateProcessor\Construction;

final class IfElseConstruction extends Construction
{
	private $ifCondition;
	
	//Здесь $data - первое условие
	public function __construct($data)
	{
		$this->ifCondition = $data;
	}
	
	public function GetResultData($vars)
	{
		foreach($this->content as $ifElseContainer)
		{
			if($ifElseContainer->IsMatchCondition($vars))
			{
				return $ifElseContainer->GetResultData($vars);
			}
		}
	}
	
	public function ProcessContent($content)
	{
		$caretOpenPositions				= FoundAllSubstrPos($content, '{');
		$caretClosePositions			= FoundAllSubstrPos($content, '}');
		$nestedIfCount					= 0;
		$prevContainerContentStartPos	= 0;
		
		$container = new IfElseContainerConstruction($this->ifCondition);
		
		if($caretOpenPositions === false)
		{
			$container->ProcessContent($content);
			$this->AddContent($container);
			return;
		}
		
		for($i = 0; $i < count($caretOpenPositions); $i++)
		{
			$constructionDataArray	= explode(" ", substr($content, $caretOpenPositions[$i] + 1, $caretClosePositions[$i] - $caretOpenPositions[$i] - 1));
			$constructionName		= $constructionDataArray[0];
			
			if($nestedIfCount > 0 && $constructionName === '/if')
			{
				$nestedIfCount--;
			}
			
			if($nestedIfCount === 0 && $constructionName === 'else') //Для else и else if
			{
				$container->ProcessContent(substr($content, $prevContainerContentStartPos, $caretOpenPositions[$i] - $prevContainerContentStartPos));
				$this->AddContent($container);

				$container						= new IfElseContainerConstruction($constructionDataArray);
				$prevContainerContentStartPos	= $caretClosePositions[$i] + 1;
			}
			else if($constructionName === 'if')
			{
				$nestedIfCount++;
			}
		}

		$container->ProcessContent(substr($content, $prevContainerContentStartPos, strlen($content) - $prevContainerContentStartPos));
		$this->AddContent($container);
	}
}