<?php
/**
 * Lock file for the daemon.
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

/**
 * Lock file for the daemon.
 * @package Iridium\Modules\Daemon
 */
final class LockFile
{
	/**
	 * Access mode of the file.
	 */
	const MODE = 0664;

	/**
	 * @var string Path to the lock files directory.
	 */
	private static $dir;

	/**
	 * @var string Extension of the lock file. Also unique id.
	 */
	private static $extension;

	/**
	 * @var string Path to the lock file.
	 */
	private $path;

	/**
	 * Creates new object of the class LockFile.
	 * @param string $daemonName
	 */
	public function __construct(string $daemonName)
	{
		$this->path = self::$dir . '/' . $daemonName . '.' . self::$extension;
	}

	/**
	 * Initialization.
	 * @param array $conf Config.
	 */
	public static function Init(array $conf)
	{
		self::$dir = empty($conf['path']) ? sys_get_temp_dir() : $conf['path'];
		self::$extension = $conf['unique_id'];

		if(empty(self::$extension))
		{
			throw new \InvalidArgumentException('Parameter "unique_id" in daemon module parameters should be defined.');
		}

		if(!is_dir(self::$dir))
		{
			if(!mkdir(self::$dir, self::MODE, true))
			{
				throw new \InvalidArgumentException('Parameter "path" in daemon module parameters shoud contain correct path to the lock files directory.');
			}
		}
	}

	/**
	 * Creates new lock file of the daemon with the specified process id.
	 * @param int $pid Process id of the daemon.
	 */
	public function Create(int $pid)
	{
		if(file_put_contents($this->path, $pid, LOCK_EX) === false)
		{
			throw new \RuntimeException("Cannot create lock file ({$this->path}) for the daemon.");
		}

		if(!chmod($this->path, self::MODE))
		{
			throw new \RuntimeException("Cannot chage rights of the daemon lock file ({$this->path}).");
		}
	}

	public function Remove()
	{
		if(!unlink($this->path))
		{
			throw new \RuntimeException("Cannot delete ");
		}
	}

	/**
	 * @return bool True, if lock file exists.
	 */
	public function Exists() : bool
	{
		return file_exists($this->path);
	}

	/**
	 * @return int Process id.
	 */
	public function GetPid() : int
	{
		$result = file_get_contents($this->path);

		if($result === false)
		{
			throw new \RuntimeException("Cannot read content of the daemon lock file ({$this->path}).");
		}

		return (int)$result;
	}
}