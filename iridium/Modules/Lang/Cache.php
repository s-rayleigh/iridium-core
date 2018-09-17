<?php
/**
 * Cache of the languages.
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
 * Cache of the languages.
 * @package Iridium\Modules\Lang
 */
class Cache implements \Serializable
{
	/**
	 * Name of the languages cache file.
	 */
	const CACHE_FILE_NAME = 'lang';

	/**
	 * @var int Timestamp of the cache creation.
	 */
	private $timestamp;

	/**
	 * @var Dictionary[] Dictionaries of the languages.
	 */
	private $dictionaries;

	/**
	 * @var bool True, if cache is loaded.
	 */
	private $loaded;

	/**
	 * Creates new cache.
	 * @param Dictionary[] $dictionaries Dictionaries of the languages.
	 */
	public function __construct(array $dictionaries)
	{
		$this->dictionaries = $dictionaries;
		$this->loaded       = false;
	}

	public function serialize()
	{
		// Remember the timestamp
		$this->timestamp = TIMESTAMP;

		return serialize([
			$this->timestamp,
			$this->dictionaries
		]);
	}

	public function unserialize($serialized)
	{
		list(
			$this->timestamp,
			$this->dictionaries
		) = unserialize($serialized);

		$this->loaded = true;
	}

	/**
	 * @return bool True, if cache file is exists.
	 */
	public static function IsExists(): bool
	{
		return file_exists(self::GetCachePath());
	}

	/**
	 * @return bool Removes cache file.
	 */
	public static function Clear(): bool
	{
		return @unlink(self::GetCachePath());
	}

	/**
	 * Loads cache from file.
	 * @return Cache Loaded cache.
	 * @throws \Exception If error is occurred while reading the file or deserialization process.
	 */
	public static function Load(): self
	{
		$content = @file_get_contents(self::GetCachePath());
		if($content === false)
		{
			throw new \Exception("Cannot read the file that contains languages cache.");
		}

		$result = @unserialize($content, ['allowed_classes' => [self::class, Group::class, Dictionary::class]]);
		if($result === false)
		{
			throw new \Exception("Cannot deserialize the languages cache.");
		}

		return $result;
	}

	/**
	 * Saves cache to the file.
	 * @throws \Exception
	 */
	public function Save()
	{
		$serialized = serialize($this);
		$path       = self::GetCachePath();

		if(file_exists($path))
		{
			$tempPath = $path . '~';

			if(@file_put_contents($tempPath, $serialized, LOCK_EX) === false)
			{
				throw new \Exception("Cannot write languages cache to the temporary file.");
			}

			if(rename($tempPath, $path) === false)
			{
				@unlink($tempPath);
				throw new \Exception("Cannot replace old languages cache with new one.");
			}
		}
		else
		{
			if(@file_put_contents($path, $serialized, LOCK_EX) === false)
			{
				throw new \Exception("Cannot write languages cache to the new file with path '{$path}'.");
			}
		}
	}

	/**
	 * @return int Timestamp of the loaded cache creation.
	 * @throws \Exception
	 */
	public function GetTimestamp(): int
	{
		if(!$this->loaded)
		{
			throw new \Exception("Timestamp can be obtained only from the cache of loaded languages.");
		}

		return $this->timestamp;
	}

	/**
	 * @return Dictionary[] Dictionaries of the loaded cache.
	 * @throws \Exception
	 */
	public function GetDictionaries(): array
	{
		if(!$this->loaded)
		{
			throw new \Exception("Dictionaries can be obtained only from the cache of loaded languages.");
		}

		return $this->dictionaries;
	}

	/**
	 * @return string Path to the cache file.
	 */
	private static function GetCachePath(): string
	{
		static $cachePath = null;
		if(empty($cachePath))
		{
			$cachePath = ROOT_PATH . DIRECTORY_SEPARATOR . Lang::$conf->cache_path . DIRECTORY_SEPARATOR . self::CACHE_FILE_NAME;
		}

		return $cachePath;
	}
}