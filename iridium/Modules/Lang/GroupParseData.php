<?php
/**
 * Data for parsing the group.
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

namespace Iridium\Modules\Lang;

/**
 * Data for parsing the group.
 * @package Iridium\Modules\Lang
 */
class GroupParseData
{
	/**
	 * @var string Content of the group to be parsed.
	 */
	private $content;

	/**
	 * @var int Beginning line if it is a subgroup.
	 */
	private $line;

	/**
	 * @var string Directory of file if group is a file.
	 */
	private $dir;

	/**
	 * @var string File of the group if group is a file.
	 */
	private $file;

	/**
	 * Creates new data object for parsing the group.
	 * @param string $content Content of the group to parse.
	 */
	public function __construct(string $content)
	{
		$this->content = $content;
	}

	/**
	 * @return string Content of the group to be parsed.
	 */
	public function GetContent(): string
	{
		return $this->content;
	}

	/**
	 * @param int $line Beginning line.
	 * @return GroupParseData
	 */
	public function SetLine(int $line): self
	{
		$this->line = $line;
		return $this;
	}

	/**
	 * @return int Beginning line.
	 */
	public function GetLine(): int
	{
		return empty($this->line) ? 1 : $this->line;
	}

	/**
	 * Sets path to the file without extension.
	 * @param string $path Path to the file.
	 * @return GroupParseData
	 * @throws \Exception If file or directory name is not valid.
	 */
	public function SetPath(string $path): self
	{
		$pathComponents = explode('/', $path);
		$this->file     = array_pop($pathComponents);

		if(!self::CheckFileName($this->file))
		{
			throw new \Exception("File name {$this->file} is not valid.");
		}

		if(count($pathComponents) > 0)
		{
			foreach($pathComponents as $comp)
			{
				if(!self::CheckFileName($comp))
				{
					throw new \Exception("Directory name {$comp} is not valid.");
				}
			}

			$this->dir = implode(DIRECTORY_SEPARATOR, $pathComponents);
		}

		return $this;
	}

	/**
	 * @return bool True, if group is a file.
	 */
	public function IsFile(): bool
	{
		return !empty($this->file);
	}

	/**
	 * @return string Directory of the file.
	 */
	public function GetDir(): string
	{
		return empty($this->dir) ? '' : $this->dir;
	}

	/**
	 * @return string Name of the file without extension.
	 */
	public function GetFile(): string
	{
		return empty($this->file) ? '' : $this->file;
	}

	/**
	 * @return string Relative path to the file without extension.
	 */
	public function GetRelativePath(): string
	{
		return (empty($this->dir) ? '' : $this->dir . DIRECTORY_SEPARATOR) . (empty($this->file) ? '' : $this->file);
	}

	/**
	 * Checks if passed file or directory name is valid.
	 * @param string $name File name.
	 * @return bool True, if file name is valid.
	 */
	private static function CheckFileName(string $name): bool
	{
		return preg_match('/^[A-Za-z0-9_]+$/', $name) === 1;
	}
}