<?php
/**
 * General helpers functions.
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

use Iridium\Core\Log\Log;
use Iridium\Core\Http\HTTP;


// TODO: move to classes

//Возвращает все позиции вхождения подстроки в строку
//Возвращает false, если ни одного вхождения не найдено
/**
 * @param $text
 * @param $sub
 * @return array|bool
 * @deprecated
 */
function FoundAllSubstrPos($text, $sub)
{
	$arr     = array();
	$lastPos = strpos($text, $sub);

	if($lastPos === false)
	{
		return false;
	}

	do
	{
		$arr[]   = $lastPos;
		$lastPos = strpos($text, $sub, $lastPos + 1);
	} while($lastPos !== false);

	return $arr;
}

/**
 * Обработчик неотловленных исключений
 * @param Exception $exception Исключение
 * @todo Move to the class.
 * @deprecated
 */
function exception_handler($exception)
{
	if(defined('OP'))
	{
		if(is_a($exception, '\core\exceptions\NoticeableException'))
		{
			$exception->LogException();
			HTTP::SendJSON($exception->PackResponse());
		}
		else if(is_a($exception, 'Throwable'))
		{
			HTTP::SendJSON(array('title' => (is_a($exception, 'ParseError') ? 'Синтаксическая ошибка' : 'Ошибка PHP'), 'error' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine()));
		}
		else
		{
			HTTP::SendJSON(array('error' => $exception->getMessage(), 'title' => 'Ошибка выполнения', 'class' => get_class($exception)));
		}
	}
	else if(defined('PAGE'))
	{
		$errorText = "Ошибка обработки страницы!\n<pre>{$exception->getMessage()}</pre>\nВ файле {$exception->getFile()}:{$exception->getLine()}";

		Log::Fatal($errorText);
		echo str_replace("\n", '<br>', $errorText);
		Log::Save();
	}
	else
	{
		echo "<pre>$exception</pre>";
	}
}

/**
 * Считает кол-во страниц на основе общего кол-ва записей и кол-ва записей, показываемых на странице
 * @param    int $count   Общее количество записей
 * @param    int $perPage Количество записей на странице
 * @return    int                Количество страниц
 * @deprecated
 */
function CalculatePagesCount($count, $perPage)
{
	return (int)ceil($count / $perPage);
}

/**
 * Смещает номер страницы в пределах [0; $pages]
 * @param    int $curPage Текущая страница
 * @param    int $pages   Всего страниц
 * @param    int $move    Сторона, в которую нужно сместить (-1 или +1)
 * @return    int                Итоговый номер страницы
 * @deprecated
 */
function MovePage($curPage, $pages, $move)
{
	//Переходим по страницам
	switch($move)
	{
		case -1:
			$curPage--;
			break;
		case 1:
			$curPage++;
			break;
	}

	//Ограничиваем промежутком [0; $pages]
	$curPage = ClampNumber($curPage, 0, $pages - 1);

	return $curPage;
}

/**
 * Добавляет символ / по краям регулярного выражение, а также указанные флаги
 * @param	string	$expr	Регулярное выражение
 * @param	string	$flags	Флаги регулярного выражения
 * @return	string			Итоговое регулярное выражение
 * @deprecated
 */
function GetValidRegexpr($expr, $flags = '')
{
	return "/$expr/$flags";
}

/**
 * Ограничивает число заданным промежутком
 * @param	int	$number	Число
 * @param	int	$min	Минимум промежутка
 * @param	int	$max	Максимум промежутка
 * @return	int
 */
function ClampNumber($number, $min, $max)
{
	return max(min($number, $max), $min);
}

/**
 * Возвращает размер данных в байтах
 * @param	string	$strSize	Строковое представление размера данных вида 2М, 5G и т. п.
 * @return	int
 */
function GetSizeBytes($strSize)
{
	switch(substr($strSize, -1))
	{
		case 'K':
		case 'k':
			return (int)$strSize * 1024;
		case 'M':
		case 'm':
			return (int)$strSize * 1048576;
		case 'G':
		case 'g':
			return (int)$strSize * 1073741824;
		default:
			return (int)$strSize;
	}
}