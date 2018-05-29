<?php
/**
 * Template processor module.
 * It is ugly and in plans to rewrite it.
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

namespace Iridium\Modules\TemplateProcessor;

use Exception;
use Iridium\Core\Log\Log;
use Iridium\Core\Module\IModule;
use Iridium\Modules\TemplateProcessor\Constructions\{
	HtmlConstruction, MainConstruction, ValueConstruction,
	ForeachConstruction, ForConstruction, WhileConstruction,
	IfElseConstruction
};

require 'iConstruction.php';
require 'Construction.php';

//Constructions
require 'Constructions/MainConstruction.php';
require 'Constructions/HtmlConstruction.php';
include 'Constructions/ValueConstruction.php';
include 'Constructions/IfElseConstruction.php';
include 'Constructions/ForeachConstruction.php';
include 'Constructions/IfElseContainerConstruction.php';
include 'Constructions/ExpressionConstruction.php';

/**
 * Template processor.
 * @package Iridium\Modules\TemplateProcessor
 */
final class TemplateProcessor implements IModule
{
	/**
	 * @var string Path to the template files.
	 */
	private static $templatesPath;

	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	public static function Init(array $moduleConfig)
	{
		self::$templatesPath = ROOT_PATH . DIRECTORY_SEPARATOR . $moduleConfig['templates_path'];
	}

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	public static function GetRequiredModules(): array
	{
		return [];
	}

	/**
	 * Sets templates path.
	 * @param string $path Path to the template files.
	 */
	public static function SetTemplatesPath(string $path)
	{
		self::$templatesPath = $path;
	}

	/**
	 * Обрабатывает шаблон с указанным именем.
	 * @param string $templateName Имя шаблона.
	 * @param array $vars Переменные шаблона.
	 * @return bool|string
	 */
	public static function ProcessTemplate($templateName, array $vars = array())
	{
		$templateData = file_get_contents(self::$templatesPath . $templateName);
		if($templateData === false) { return false; }
		$templateData = str_replace(array("\n", "\r", "\t"), '', $templateData);

		try
		{
			//Строим синтаксическое дерево
			$tree = self::BuildSyntaxTree($templateData, new MainConstruction(null));
		}
		catch(Exception $e)
		{
			Log::Error("Во время построения синтаксического дерева шаблона $templateName произошла ошибка:\n" . $e->getMessage());
			return $e->getMessage();
		}
		
		try
		{
			//Получаем html на основе синтаксического дерева
			return $tree->GetResultData($vars);
		}
		catch(Exception $e)
		{
			Log::Error("Во время обработки синтаксического дерева шаблона $templateName произошла ошибка:\n" . $e->getMessage());
			return $e->getMessage();
		}
	}
	
	public static function BuildSyntaxTree($templateData, iConstruction $parent)
	{
		//Внимание! Этот метод может взорвать мозг при попытке разобраться как он работает!
		
		static $templateRules = array(
			'html'		=> HtmlConstruction::class,
			'value'		=> ValueConstruction::class,
			'foreach'	=> ForeachConstruction::class,
			'for'		=> ForConstruction::class,
			'while'		=> WhileConstruction::class,
			'if'		=> IfElseConstruction::class
		);

		$parseConstruction				= false;	//Если истина, то в данный момент парсится конструкция
		$parseConstructionContent		= false;	//Если истина, то в данный момент парсятся данные конструкции
		$parseConstructionContentEnd	= false;	//Если истина, то в данный момент идет поиск окончания контента конструкции
		$constructionStartPosition		= 0;		//Начало текущей конструкции ({)
		$constructionEndPosition		= 0;		//Окончание текущей конструкции (})
		$constructionContentEndPosition	= -1;		//Окончание данных текущей конструкции ({/name}). Должно быть -1, так как всегда (!) показывает на символ раньше (фигурную кавычку), а вначале шаблона ее может не быть
		$sameConstructionsCount			= 0;		//Количество конструкций с именем, совпадающим с именем текущей конструкции при парсинге контента конструкции
		$currentConstruction			= null;		//Текущая конструкция (объект класса, имя которого задано в $templateRules)
		$currentConstructionName		= '';		//Имя текущей конструкции
		
		//Перебираем каждый символ текста шаблона
		for($i = 0; $i < strlen($templateData); $i++)
		{
			if($templateData[$i] === '{')
			{
				$constructionStartPosition = $i;
				
				//Окончание контента конструкции
				if($templateData[$i + 1] === '/')
				{
					//Log::Debug('Найдено окончание контента конструкции.');
					$parseConstructionContentEnd = true;
				}
				else //Начало конструкции
				{
					//Log::Debug('Найдено начало конструкции.');
					$parseConstruction = true;
				}
			}
			else if($templateData[$i] === '}' && ($parseConstruction || $parseConstructionContent)) //Окончание конструкции
			{
				//Разбиваем по пробелу строку с данными конструкции
				$constructionDataArray = explode(" ", substr($templateData, $constructionStartPosition + 1, $i - $constructionStartPosition - 1));

				//Log::Debug("Закрывающая скобка конструкции " . $constructionDataArray[0] . '.');
				
				if($parseConstructionContent)
				{
					//Parse construction content
					if($parseConstructionContentEnd)
					{
						//Parse end of construction content
						if(trim($constructionDataArray[0], "/") === $currentConstructionName)
						{
							//Matches current construction
							if($sameConstructionsCount == 0)
							{
								$content = substr($templateData, $constructionEndPosition + 1, $constructionStartPosition - $constructionEndPosition - 1);
								
								$currentConstruction->ProcessContent($content);
								
								$constructionContentEndPosition = $i;
								$parseConstructionContent = false;
							}
							else
							{
								$sameConstructionsCount--;
							}
						}
						
						$parseConstructionContentEnd = false;
					}
					else if($constructionDataArray[0] === $currentConstructionName)
					{
						//Found same cunstruction like current
						$sameConstructionsCount++;
					}
					
					continue;
				}
				
				$constructionEndPosition = $i;

				//Found ending of the construction

				$prevHtmlLen = $constructionStartPosition - $constructionContentEndPosition - 1;

				if($constructionStartPosition > 0 && $prevHtmlLen != 0)
				{
					//Create construction object for the html before current construction
					$parent->AddContent(new $templateRules['html'](substr($templateData, $constructionContentEndPosition + 1, $prevHtmlLen)));
				}

				//If data array has one element, this construction is value get construction
				if(count($constructionDataArray) === 1 && $constructionDataArray[0] !== 'html')
				{
					//Log::Debug("Это конструкция получения значения переменной.");
					$constructionDataArray[1] = $constructionDataArray[0];
					$constructionDataArray[0] = 'value';
					$constructionContentEndPosition = $i;
				}
				else
				{
					//Construction has content
					//У конструкции получения значения нету контента, значит парсим контент только если текущая конструкция не является получением значения
					$parseConstructionContent = true;
				}
				
				$currentConstructionName = $constructionDataArray[0];
				
				if(!isset($templateRules[$currentConstructionName]))
				{
					throw new Exception("Не найдено определение для заданной конструкции в шаблоне!\nКонструкция:\n" . $constructionDataArray[0]);
				}
				
				if(!class_exists($templateRules[$currentConstructionName], false))
				{
					throw new Exception('Класс ' . $templateRules[$constructionDataArray[0]] . ' не был объявлен, однако требуется его наличие для разбора конструкции шаблона.');
				}
				
				//Create construction object
				$currentConstruction = new $templateRules[$currentConstructionName]($constructionDataArray);
				
				//Add cosntruction to parent
				$parent->AddContent($currentConstruction);
				
				$parseConstruction = false;
			}
		}
		
		if($constructionContentEndPosition < strlen($templateData) - 1)
		{
			//There is some more text
			$parent->AddContent(new $templateRules['html'](substr($templateData, $constructionContentEndPosition + 1, strlen($templateData) - $constructionContentEndPosition)));
		}
		
		return $parent;
	}

	/**
	 * Осуществляет провекру правильности названия переменной
	 * @param string $varName Название переменной
	 * @return bool
	 */
	public static function CheckValidVariableName($varName)
	{
		return ctype_alnum(str_replace(array('-', '_'), '', $varName));
	}
}