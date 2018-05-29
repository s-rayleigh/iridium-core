<?php
/**
 * If-else container construction.
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
use Iridium\Modules\TemplateProcessor\Construction;
use Iridium\Modules\TemplateProcessor\TemplateProcessor;

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