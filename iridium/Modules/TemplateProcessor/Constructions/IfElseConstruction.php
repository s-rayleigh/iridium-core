<?php
/**
 * If-else construction.
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

use Iridium\Modules\TemplateProcessor\Construction;

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