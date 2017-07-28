<?php

include '../includes/classes/exceptions/class.NoticeableException.php';
include '../core/http/filter/interface.iFilter.php';
include '../core/http/filter/class.Filter.php';

use core\http\filter\Filter as Filter;
use core\http\filter\ValueType as FilterType;
use core\http\filter\FilterInput as FilterInput;
use core\http\filter\FilterOption as FilterOption;

$filter = new Filter();


//Проверка типов

//Проверка целого числа
assert($filter->CheckType('552', ValueType::INT));
assert($filter->CheckType(522, ValueType::INT));
assert($filter->CheckType('-55', ValueType::INT));
assert($filter->CheckType('0', ValueType::INT));
assert($filter->CheckType('3.', ValueType::INT));

assert($filter->CheckType('3.2', ValueType::INT) === false);
assert($filter->CheckType(3.2, ValueType::INT) === false);
assert($filter->CheckType('-3.2', ValueType::INT) === false);
assert($filter->CheckType(-3.2, ValueType::INT) === false);
assert($filter->CheckType('-005.3', ValueType::INT) === false);
assert($filter->CheckType('a', ValueType::INT) === false);
assert($filter->CheckType('', ValueType::INT) === false);
assert($filter->CheckType('-', ValueType::INT) === false);
assert($filter->CheckType('+', ValueType::INT) === false);
assert($filter->CheckType('0xf', ValueType::INT) === false);
assert($filter->CheckType('.66', ValueType::INT) === false);

//Проверка беззнакового целого числа
assert($filter->CheckType('552', ValueType::UINT));
assert($filter->CheckType('0', ValueType::UINT));
assert($filter->CheckType('+0', ValueType::UINT));
assert($filter->CheckType('-0', ValueType::UINT));
assert($filter->CheckType('55.', ValueType::UINT));
assert($filter->CheckType('+55', ValueType::UINT));

assert($filter->CheckType('-55', ValueType::UINT) === false);
assert($filter->CheckType('0.5', ValueType::UINT) === false);
assert($filter->CheckType('-0.5', ValueType::UINT) === false);
assert($filter->CheckType('123.23', ValueType::UINT) === false);
assert($filter->CheckType('+a', ValueType::UINT) === false);

//Проверка числа с плавающей точкой
assert($filter->CheckType('552', ValueType::FLOAT));
assert($filter->CheckType('552.2', ValueType::FLOAT));
assert($filter->CheckType('-552.2', ValueType::FLOAT));
assert($filter->CheckType('-0', ValueType::FLOAT));
assert($filter->CheckType('+0', ValueType::FLOAT));
assert($filter->CheckType('0.0', ValueType::FLOAT));
assert($filter->CheckType('0.1', ValueType::FLOAT));
assert($filter->CheckType('-0.0', ValueType::FLOAT));

assert($filter->CheckType('-a', ValueType::FLOAT) === false);
assert($filter->CheckType('abc', ValueType::FLOAT) === false);
assert($filter->CheckType('0a', ValueType::FLOAT) === false);
assert($filter->CheckType('023i', ValueType::FLOAT) === false);
assert($filter->CheckType('abc', ValueType::FLOAT) === false);
assert($filter->CheckType('+', ValueType::FLOAT) === false);
assert($filter->CheckType('-', ValueType::FLOAT) === false);
assert($filter->CheckType('/', ValueType::FLOAT) === false);

//Проверка беззнакового числа с плавающей точкой
assert($filter->CheckType('0.2123', ValueType::UFLOAT));
assert($filter->CheckType('934.23', ValueType::UFLOAT));
assert($filter->CheckType('0.0', ValueType::UFLOAT));
assert($filter->CheckType('+1', ValueType::UFLOAT));
assert($filter->CheckType('+1.0', ValueType::UFLOAT));
assert($filter->CheckType('-0.0', ValueType::UFLOAT));

assert($filter->CheckType('-2.3', ValueType::UFLOAT) === false);
assert($filter->CheckType('-2', ValueType::UFLOAT) === false);
assert($filter->CheckType('sdf', ValueType::UFLOAT) === false);
assert($filter->CheckType('-+0', ValueType::UFLOAT) === false);

//Проверка строки
assert($filter->CheckType('some string', ValueType::STRING));
assert($filter->CheckType(7, ValueType::STRING) === false);

//Проверка булевого типа
assert($filter->CheckType('1', ValueType::BOOL));
assert($filter->CheckType('0', ValueType::BOOL));
assert($filter->CheckType('false', ValueType::BOOL) === false);


//Тестирование фильтра

assert($filter->FilterValue('9', ValueType::INT) === 9);
assert($filter->FilterValue('-9', ValueType::INT) === -9);
assert($filter->FilterValue('0', ValueType::INT) === 0);
assert($filter->FilterValue('3.', ValueType::INT) === 3);
assert($filter->FilterValue(10, ValueType::INT) === 10);

assert($filter->FilterValue('a', ValueType::INT, 5) === 5);
assert($filter->FilterValue(array(), ValueType::INT, 10) === 10);
assert($filter->FilterValue(5.42, ValueType::INT, 10) === 10);

assert($filter->FilterValue('10', ValueType::UINT) === 10);
assert($filter->FilterValue('-15', ValueType::UINT, 23) === 23);


//Тестируем строгий режим

$filter->UseStrictMode();

try
{
	$filter->FilterValue('-50.23', ValueType::UFLOAT, 500);
	assert(false);
}
catch(Exception $e)
{
	assert(true);
}

//Тестируем строки

assert($filter->FilterValue('строкаstring', ValueType::STRING, 'another') === 'string');
assert($filter->FilterInput(FilterInput::GET, 'no_value', ValueType::STRING, 'another') === 'another');
assert($filter->FilterValue('строкаstring', ValueType::STRING, 'another', FilterOption::MULTIBITE) === 'строкаstring');
assert($filter->FilterValue('text текст ""\'-`', ValueType::STRING, 'another') === 'text  &quot;&quot;&apos;-`');