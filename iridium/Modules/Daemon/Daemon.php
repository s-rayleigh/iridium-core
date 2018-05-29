<?php
/**
 * Daemon module.
 * Requires Linux or Unix (not tested).
 *
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

namespace Iridium\Modules\Daemon;

use Iridium\Core\Module\IModule;

require_once 'LockFile.php';

/**
 * Daemon.
 * @package Iridium\Modules\Daemon
 */
abstract class Daemon implements IModule
{
	/**
	 * @var string Directory for the lock files.
	 */
	private static $locksDir;

	/**
	 * @var bool Debug mode.
	 */
	private static $debugMode;

	/**
	 * @var string Daemon name.
	 */
	protected $name;

	private $stop = false;

	/**
	 * @var LockFile Lock file.
	 */
	private $lockFile;

	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	static function Init(array $moduleConfig)
	{
		self::$locksDir = empty($moduleConfig['locks_dir']) ? sys_get_temp_dir() : $moduleConfig['locks_dir'];
		self::$debugMode = $moduleConfig['debug'];

		LockFile::Init($moduleConfig['lock']);
	}

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules() : array
	{
		return [];
	}

	/**
	 * Creates new daemon.
	 */
	public function __construct()
	{
		$expl = explode('\\', get_called_class());
		$this->name = end($expl);
		$this->lockFile = new LockFile($this->name);
	}

	/**
	 * Starts the daemon.
	 * @return int PID.
	 * @throws \Exception If method fails to fork the process.
	 * @throws \RuntimeException If daemon is already running.
	 */
	public final function Start() : int
	{
		if($this->IsRunning())
		{
			throw new \RuntimeException('Daemon is already running.');
		}

		$pid = pcntl_fork();

		if($pid < 0) // Error
		{
			throw new \Exception("Cannot fork parent process.");
		}
		else if($pid > 0) // Parent process
		{
			$this->lockFile->Create($pid);
			return $pid;
		}

		// Child process

		// Unbinds child process from the parent process
		posix_setsid();

		// Close standart streams if they open.
		// Streams can be already closed if daemon restart command called.

		if(is_resource(STDIN))
		{
			fclose(STDIN);
		}

		if(is_resource(STDOUT))
		{
			fclose(STDOUT);
		}

		if(is_resource(STDERR))
		{
			fclose(STDERR);
		}

		if(self::$debugMode)
		{
			/** @noinspection PhpUnusedLocalVariableInspection */
			$STDERR = fopen(sys_get_temp_dir() . "/daemon-{$this->name}-err.txt", 'ab');
			/** @noinspection PhpUnusedLocalVariableInspection */
			$STDOUT = fopen(sys_get_temp_dir() . "/daemon-{$this->name}-out.txt", 'ab');
		}
		else
		{
			/** @noinspection PhpUnusedLocalVariableInspection */
			$STDERR	= fopen('/dev/null', 'ab');
			/** @noinspection PhpUnusedLocalVariableInspection */
			$STDOUT	= fopen('/dev/null', 'ab');
		}

		// Remove execution time limit
		set_time_limit(0);

		// Changes execution directory for possibility to start daemons from the removable storage
		chdir('/home/');

		$signals = [
			SIGTERM, SIGINT, SIGUSR1, SIGHUP, SIGCHLD,
			SIGUSR2, SIGCONT, SIGQUIT, SIGILL, SIGTRAP,
			SIGABRT, SIGIOT, SIGBUS, SIGFPE, SIGSEGV,
			SIGPIPE, SIGALRM, SIGCONT, SIGTSTP, SIGTTIN,
			SIGTTOU, SIGURG, SIGXCPU, SIGXFSZ, SIGVTALRM,
			SIGPROF, SIGWINCH, SIGIO, SIGSYS, SIGBABY
		];

		// Register signals
		foreach(array_unique($signals) as $signal)
		{
			pcntl_signal($signal, [$this, 'SignalReceived']);
		}

		$this->Run();

		return -1;
	}

	/**
	 * Stops the daemon.
	 * Sends SIGTERM signal to the daemon process.
	 * @throws \RuntimeException If daemon is not running.
	 */
	public final function Stop()
	{
		if(!$this->IsRunning())
		{
			throw new \RuntimeException('Daemon is not running');
		}

		posix_kill($this->lockFile->GetPid(), SIGTERM);
	}

	/**
	 * Kills the daemon.
	 * Sends SIGKILL signal to the daemon process.
	 * @throws \RuntimeException If daemon is not running.
	 */
	public final function Kill()
	{
		if(!$this->IsRunning())
		{
			throw new \RuntimeException('Daemon is not running');
		}

		posix_kill($this->lockFile->GetPid(), SIGKILL);
	}

	/**
	 * Restarts the daemon.
	 * Sends SIGUSR1 signal (binded to restart) to the daemon process.
	 * @throws \RuntimeException If daemon is not running.
	 */
	public final function Restart()
	{
		if(!$this->IsRunning())
		{
			throw new \RuntimeException('Daemon is not running');
		}

		posix_kill($this->lockFile->GetPid(), SIGUSR1);
	}

	/**
	 * @return bool True, if daemon is running.
	 */
	public final function IsRunning() : bool
	{
		if(!$this->lockFile->Exists())
		{
			return false;
		}

		if($this->lockFile->GetPid() <= 0)
		{
			return false;
		}

		exec('ps -p ' . $this->lockFile->GetPid(), $out);
		return count($out) === 2; // If process is running, command 'ps -p' return two lines
	}

	/**
	 * Runs infinite loop.
	 */
	private function Run()
	{
		$this->OnStart();

		while(true)
		{
			if($this->stop)
			{
				break;
			}

			$this->OnIterationBegin();
			$this->Iteration();
			$this->OnIterationEnd();

			// Can be interrupted by the signals
			sleep($this->GetSleepTime());

			// Calls the handlers for the waiting signals
			pcntl_signal_dispatch();
		}

		$this->OnStop();

		exit(0);
	}

	/**
	 * Signals receiver.
	 * @param int $signo Number of the signal.
	 * @param mixed $signinfo Info of the signal (empty if system does not supports signal info or null if the php version lower than 7.1).
	 */
	private function SignalReceived(int $signo, $signinfo = null)
	{
		$this->OnSignal($signo, $signinfo);

		switch($signo)
		{
			case SIGHUP: // Stop
			case SIGINT:
			case SIGTERM:
				if(!$this->stop)
				{
					$this->stop = true;
					$this->lockFile->Remove();
				}
				break;
			case SIGUSR1: // Restart
				if(!$this->stop)
				{
					$this->stop = true;
					$this->lockFile->Remove();
				}
				(new static())->Start();
				break;
		}
	}

	/**
	 * @return int The daemon sleep time between iterations.
	 */
	protected abstract function GetSleepTime() : int;

	/**
	 * Iteration of the infinite loop.
	 */
	protected abstract function Iteration();

	/**
	 * Called on daemon start.
	 */
	protected function OnStart() { }

	/**
	 * Called on daemon stop.
	 */
	protected function OnStop() { }

	/**
	 * Called before iteration begin.
	 */
	protected function OnIterationBegin() { }

	/**
	 * Called after iteration.
	 */
	protected function OnIterationEnd() { }

	/**
	 * Called when a signal is received.
	 * @param int $signo Number of the signal.
	 * @param mixed $signinfo Info of the signal (empty if system does not supports signal info or null if the php version lower than 7.1).
	 */
	protected function OnSignal(int $signo, $signinfo = null) { }
}