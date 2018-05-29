<?php
/**
 * Foreach construction.
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