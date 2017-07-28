<?php
/**
 * Daemon module.
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
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */

namespace modules\Daemon;

use core\exceptions\OperationException;
use core\module\IModule;
use site\classes\OperationError;
use core\log\Log;

/**
 * Фоновый процесс.
 *
 * Работает только в ОС Linux.
 *
 * @author rayleigh <rayleigh@protonmail.com>
 */
abstract class Daemon implements IModule
{
	private $stop = false;	//Нужно-ли остановить демон

	protected $daemonName;	//Имя демона (латиница)
	protected $id;			//Идентификатор демона в БД
	protected $sleepTime;	//Время сна между итерациями бесконнечного цикла

	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	static function Init(array $moduleConfig) { }

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules(): array
	{
		return ['MySql'];
	}

	public final function __construct($id, $name, $sleepTime)
	{
		$this->daemonName	= $name;
		$this->id			= $id;
		$this->sleepTime	= (int)$sleepTime;
	}

	/**
	 * Запускает демон на выполнение
	 * @param	string	$daemonName	Имя демона
	 * @throws	OperationException
	 */
	public static function Start($daemonName)
	{
		self::ExecDaemonCommand($daemonName, 'start');
	}

	/**
	 * Останавливает выполнения демона после окончания текущей итерации
	 * @param	string	$daemonName	Имя демона
	 * @throws	OperationException
	 */
	public static function Stop($daemonName)
	{
		self::ExecDaemonCommand($daemonName, 'stop');
	}

	/**
	 * Сообщает всем демонам выполнить остановку
	 */
	public static function StopAll()
	{
		Database::Query("UPDATE `" . DAEMONS . "` SET `need_stop` = '1' WHERE `status` != '0';");
	}

	public static function Kill($daemonName)
	{
		self::ExecDaemonCommand($daemonName, 'kill');
	}

	public static function Restart($daemonName)
	{
		self::ExecDaemonCommand($daemonName, 'restart');
	}

	public static function Status($daemonName)
	{
		return (bool)self::ExecDaemonCommand($daemonName, 'status');
	}

	/**
	 * Передает демону команду на выполнение
	 * @param string $daemonName Имя демона
	 * @param string $command Команда
	 * @return mixed
	 * @throws OperationException
	 */
	private static function ExecDaemonCommand($daemonName, $command)
	{
		exec('php -f ' . ROOT_PATH . "daemon.php $daemonName $command", $result);
		
		Log::Debug("Результат выполнения команды демона:" . print_r($result, true));

		list($code, $data) = $result;

		switch($code)
		{
			case DAEMON_OP_SUCCESS:
				break;
			case DAEMON_NO_FILE:
				throw new OperationException(OperationError::NO_DAEMON_FILE);
			case DAEMON_START_PROCESS_ERROR:
				throw new OperationException(OperationError::DAEMON_PROCESS_ERROR);
			case DAEMON_UNDEFINED_COMMAND:
				throw new OperationException(OperationError::WRONG_DAEMON_COMMAND);
			case DAEMON_NO_NAME:
				throw new OperationException(OperationError::NO_DAEMON_NAME);
			case DAEMON_NO_IN_DB:
				throw new OperationException(OperationError::NO_DAEMON);
			case DAEMON_ALREADY_LAUNCHED:
				throw new OperationException(OperationError::DAEMON_ALREADY_RUN);
			case DAEMON_NO_CLASS:
				throw new OperationException(OperationError::NO_DAEMON_CLASS);
			case DAEMON_NOT_RUNNING:
				throw new OperationException(OperationError::DAEMON_NOT_RUN);
			default:
				throw new OperationException(OperationError::UNKNOWN_DAEMON_CODE);
		}

		return $data;
	}

	/**
	 * Запускает бесконечный цикл демона.
	 */
	public final function Run()
	{
		Log::Debug("Запуск бесконечного цикла.");
		Log::Save();

		$this->Prepare();

		while(true)
		{
			//Узнаем нужно-ли завершить выполнение
			$this->stop = (bool)Database::GetCell("SELECT `need_stop` FROM `" . DAEMONS . "` WHERE `id` = '{$this->id}' LIMIT 1;");

			//Если нужно завершить выполнение
			if($this->stop)
			{
				Log::Debug("А вот и моя остановочка!");
				Log::Save();
				
				//Пишем в базу что процесс завершен и более не требуется отметка для завершения
				Database::Query("UPDATE `" . DAEMONS . "` SET `pid` = '-1', `need_stop` = '0', `status` = '" . DAEMON_DISABLED . "' WHERE `id` = '$this->id' LIMIT 1;");
				
				//Прерываем бесконнечный цикл
				break;
			}

			//Обновляем статус на "работает"
			Database::Query("UPDATE `" . DAEMONS . "` SET `status` = '" . DAEMON_WORKING . "' WHERE `id` = '{$this->id}' LIMIT 1;");

			//Выполняем работу для этой итерации цикла
			$this->Iteration();

			//Обновляем статус на "в ожидании"
			Database::Query("UPDATE `" . DAEMONS . "` SET `status` = '" . DAEMON_WAITING . "' WHERE `id` = '{$this->id}' LIMIT 1;");

			Log::Info("Жду {$this->sleepTime} секунд.");

			//Сохраняем логи, созданные во время выполнения итерации демона
			Log::Save();

			//Ждем указанное в параметрах кол-во секунд и переходим на следующую итерацию
			sleep($this->sleepTime);
		}
	}

	/**
	 * Действия, которые выполняет демон за итерацию бесконечного цикла.
	 */
	protected abstract function Iteration();

	/**
	 * Подготовка демона перед работой.
	 *
	 * Метод для переопределения.
	 */
	protected function Prepare() {}
}