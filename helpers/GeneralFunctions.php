<?php

use core\log\Log;
use core\http\HTTP;


// TODO: move to classes

//Возвращает все позиции вхождения подстроки в строку
//Возвращает false, если ни одного вхождения не найдено
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
 * Определяет, начинается ли строка $haystack с подстроки $needle
 * @param	string	$haystack
 * @param	string	$needle
 * @return	bool
 */
function StartsWith($haystack, $needle)
{
	//strrpos вместо strpos для выполнения меньшего числа операций
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

/**
 * Определяет, заканчивается ли строка $haystack подстрокой $needle
 * @param	string	$haystack
 * @param	string	$needle
 * @return	bool
 */
function EndsWith($haystack, $needle)
{
	return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
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
 * Проверяет содержит-ли строка беззнаковое целое число
 * @param	string	$str	Строка, которую нужно проверить
 * @returns	bool			true, если строка содержит беззнаковое целое
 * @deprecated
 */
function StringContainsUint($str)
{
	return (string)abs((int)$str) === $str;
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

/**
 * Извлекает из имени файла его расширение в нижнем регистре
 * @param	string	$fileName	Имя файла
 * @return	string				Расширение файла
 */
function GetFileExtension($fileName)
{
	return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
}

/**
 * Проверяет соответствует-ли изображение заданному соотношению сторон
 * @param	string	$imagePath		Путь к изображению
 * @param	int		$aspRatWidth	Соотношение по X
 * @param	int		$aspRatHeight	Соотношение по Y
 * @return	bool
 * @deprecated
 */
function MatchAspectRatio($imagePath, $aspRatWidth, $aspRatHeight)
{
	//Получаем размер изображения
	$size = getimagesize($imagePath);

	//Если файл не изображение
	if($size === false)
	{
		return false;
	}

	//Получаем высоту и ширину картинки, проверяем соотношение сторон и возвращаем результат
	list($width, $height) = $size;

//	return ($width % $aspRatWidth === 0) && ($height % $aspRatHeight === 0);
	return $width / $height === $aspRatWidth / $aspRatHeight;
}

function MachineTimeFormat(int $timestamp)
{
	return date('c', $timestamp);
}

function HumanTimeFormat($timestamp)
{
	return date(TIME_FORMAT, $timestamp);
}