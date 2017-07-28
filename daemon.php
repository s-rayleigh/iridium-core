#!/usr/bin/php
<?php

/**
 * @deprecated
 */

require 'core/constants.php';
require 'site/constants.php';
require 'config/config.php';
require 'core/class.Database.php';
require 'core/class.Daemon.php';
require 'core/file/class.File.php';
require 'core/file/class.FilesGroup.php';
require 'core/file/class.FileType.php';
require 'core/exceptions/class.NoticeableException.php';
require 'core/class.EmailLetter.php';

use core\log\Log;
use core\Database;

if(DAEMON_DEBUG_MODE)
{
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

//Проверяем на наличие имени демона
if(empty($argv[1]))
{
	echo DAEMON_NO_NAME;
	exit;
}

//Получаем имя демона
$daemonName = $argv[1];

//Подключаем и инициализируем логирование
require 'core/log/class.LogLevel.php';
require 'core/log/class.Log.php';
Log::Init("{$daemonName}_daemon_");

switch(isset($argv[2]) ? $argv[2] : 'nc')
{
	case 'start':	//Запуск
		start($daemonName);
		break;
	case 'stop':	//Остановка
		stop($daemonName);
		ReturnResult();
		break;
	case 'kill':	//Принудительная остановка (без ожидания)
		kill($daemonName);
		ReturnResult();
		break;
	case 'restart':	//Перезапуск
		kill($daemonName);
		start($daemonName, false);
		ReturnResult();
		break;
	case 'status':	//Статус
		ReturnResult(DAEMON_OP_SUCCESS, Enabled($daemonName) ? 1 : 0);
		break;
	case 'nc':		//Команда не задана
	default:
		ReturnResult(DAEMON_UNDEFINED_COMMAND);
		break;
}

//=======================- Функции -=======================

//Включен-ли демон
function Enabled($name)
{
	$pid = Database::GetCell("SELECT `pid` FROM `" . DAEMONS . "` WHERE `name` = '$name' LIMIT 1;");

	if(empty($pid))
	{
		ReturnResult(DAEMON_NO_IN_DB);
		exit;
	}

	if($pid == -1) { return false; }

	exec("ps -p $pid", $out);
	return count($out) === 2;
}

/**
 * Выполняет запуск демона
 * @param string	$name			Название демона
 * @param bool		$tellResult		Вывести результат запуска демона
 */
function start($name, $tellResult = true)
{
	$daemonUName		= ucwords($name);
	$daemonClassName	= "site\\daemons\\{$daemonUName}Daemon";

	//Извлекаем данные демона из базы данных
	$daemonData = Database::GetRow("SELECT `id`, `pid`, `sleep_time` FROM `" . DAEMONS . "` WHERE `name` = '$name' LIMIT 1;");

	if(Enabled($name))
	{
		ReturnResult(DAEMON_ALREADY_LAUNCHED);
		exit;
	}

	//Формируем путь к файлу класса демона
	$daemonPath = 'site/daemons/daemon.' . $daemonUName . '.php';

	//Проверяем наличие файла класса
	if(!file_exists($daemonPath))
	{
		ReturnResult(DAEMON_NO_FILE);
		exit;
	}

	//Подключаем файл с классом демона
	require $daemonPath;

	//Проверяем задан-ли класс демона
	if(!class_exists($daemonClassName))
	{
		ReturnResult(DAEMON_NO_CLASS);
		exit;
	}

	//Создаем дочерний процесс
	$pid = pcntl_fork();

	//Переподключаемся к БД
	Database::Reconnect();

	if($pid < 0)		//Ошибка
	{
		ReturnResult(DAEMON_START_PROCESS_ERROR);
		exit;
	}
	else if($pid > 0)	//Родительский процесс
	{
		//Запоминаем pid
		Database::Query("UPDATE `" . DAEMONS . "` SET `pid` = '$pid', `last_start` = NOW() WHERE `id` = '{$daemonData['id']}' LIMIT 1;");

		if($tellResult)
		{
			//Сообщаем, что демон запущен
			ReturnResult();
		}

		exit;
	}

	Log::Save();

	//Делаем дочерний процесс основным, т. е. отвязываем его от родительского
	posix_setsid();

	//Закрываем стандартные потоки
	fclose(STDIN);
	fclose(STDOUT);
	fclose(STDERR);

	//Поток ввода из /dev/null
	$STDIN	= fopen('/dev/null', 'r');

	if(DAEMON_DEBUG_MODE)
	{
		//Перенаправление потоков вывода в файлы для отладки
		$STDERR = fopen(ROOT_PATH . LOG_FILES_PATH . "/daemon_{$name}_err.log", 'ab');
		$STDOUT	= fopen(ROOT_PATH . LOG_FILES_PATH . "/daemon_{$name}_out.log", 'ab');
	}
	else
	{
		//Перенаправление потоков вывода в /dev/null
		$STDERR	= fopen('/dev/null', 'ab');
		$STDOUT	= fopen('/dev/null', 'ab');
	}

	//Снимаем ограничение на время выполнения скрипта, так как это может быть не указано в php.ini
	set_time_limit(0);

	Log::Info("Запуск демона \"$name\".");
	Log::Save();

	//Запускаем демон
	(new $daemonClassName($daemonData['id'], $name, $daemonData['sleep_time']))->Run();
}

function stop($name)
{
	if(!Enabled($name))
	{
		ReturnResult(DAEMON_NOT_RUNNING);
		exit;
	}

	//Ставим флаг, что нужно завершить работу. Работа будет завершена после того как демон закончит выполнять итерацию,
	//а также подождет время, указанное в параметрах
	Database::Query("UPDATE `" . DAEMONS . "` SET `need_stop` = '1' WHERE `name` = '$name' LIMIT 1;");
}

function kill($name)
{
	$curPid = Database::GetCell("SELECT `pid` FROM `" . DAEMONS . "` WHERE `name` = '$name' LIMIT 1;");

	if(!Enabled($name))
	{
		ReturnResult(DAEMON_NOT_RUNNING);
		exit;
	}

	exec("kill $curPid");
}

/**
 * Возвращает результат в поток стандартного вывода
 * @param	int		$resultCode Код результата
 * @param	string	$resultData Данные результата
 */
function ReturnResult($resultCode = DAEMON_OP_SUCCESS, $resultData = 'empty')
{
	echo "$resultCode\n$resultData";
}