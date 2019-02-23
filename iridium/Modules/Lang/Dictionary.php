<?php
/**
 * Dictionary.
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

use Iridium\Core\Tools\ArrayTools;
use Iridium\Core\Tools\StringTools;

/**
 * Dictionary that contains language phrases.
 * @package Iridium\Modules\Lang
 */
class Dictionary implements \Serializable
{
	/**
	 * Separator for the phrase path.
	 */
	const PATH_SEPARATOR = '.';

	/**
	 * @var string Code of the language.
	 */
	protected $code;

	/**
	 * @var string Code of the fallback language.
	 */
	private $fallbackCode;

	/**
	 * @var string Language name.
	 */
	protected $name;

	/**
	 * @var Group Root group.
	 */
	protected $group;

	/**
	 * @var Dictionary Fallback language dictionary.
	 */
	protected $fallback;

	/**
	 * Creates new dictionaty.
	 * @param string $code Code of the language.
	 * @param string $name Name of the language.
	 * @param Group $group Root group.
	 */
	public function __construct(string $code, string $name, Group $group)
	{
		$this->code = $code;
		$this->group = $group;
		$this->name = $name;
	}

	/**
	 * @return string Code of the language.
	 */
	public function GetCode(): string
	{
		return $this->code;
	}

	/**
	 * @return string Name of the language.
	 */
	public function GetName(): string
	{
		return $this->name;
	}

	/**
	 * @return Group Root group.
	 */
	public function GetRootGroup(): Group
	{
		return $this->group;
	}

	/**
	 * Sets the code of the fallback language.
	 * @param string $code Code of the fallback language.
	 * @return Dictionary Self.
	 */
	public function SetFallbackCode(string $code): self
	{
		$this->fallbackCode = $code;
		return $this;
	}

	/**
	 * @return string Code of the setted fallback language or empty string if no fallback language is setted for current language.
	 */
	public function GetFallbackCode(): string
	{
		return empty($this->fallbackCode) ? '' : $this->fallbackCode;
	}

	/**
	 * Sets the fallback language.
	 * @param Dictionary $fallback Dictionary of the fallback language.
	 * @return Dictionary Self.
	 */
	public function SetFallbackLang(Dictionary $fallback): self
	{
		$this->fallback = $fallback;
		return $this;
	}

	/**
	 * Creates filtered dictionary from this one.
	 * @param string|string[] ...$keywords
	 * @return FilteredDictionary Filtered dictionary.
	 */
	public function Filter(...$keywords): FilteredDictionary
	{
		return new FilteredDictionary($this, ArrayTools::Flatten($keywords));
	}

	/**
	 * Searches for phrase with specified path.
	 * @param string $path Path to the phrase.
	 * @param bool $fallback Look for the phrase in the fallback languages.
	 * @return null|string Phrase or null if no phrase by the specified path.
	 * @throws \Exception If the path is empty.
	 */
	public function FindPhrase(string $path, bool $fallback = true)
	{
		if(empty($path)) { throw new \Exception('Path should not be empty.'); }

		$pathComponents = explode(self::PATH_SEPARATOR, $path);
		$phraseId       = array_pop($pathComponents);
		$group          = empty($pathComponents) ? $this->group : $this->FindGroup($pathComponents);

		if($group !== null)
		{
			$phrase = $group->GetPhrase($phraseId);
			if($phrase !== null) { return $phrase; }
		}

		// Search in the fallback languages
		if($fallback)
		{
			$first = true;

			/** @var Dictionary $lang */
			foreach($this->GetFallbackStack() as $lang)
			{
				// Skip the first language because it current
				if($first)
				{
					$first = false;
					continue;
				}

				$fph = $lang->FindPhrase($path, false);
				if($fph !== null) { return $fph; }
			}
		}

		return null;
	}

	/**
	 * Converts dictionary to the plain array where keys is the path to the phrases and values is phrases.
	 * @return array Plain array that contains phrases of this dictionary.
	 */
	public function ToArray(): array
	{
		$result = [];

		$convert = function(Group $group, $gp = '') use(&$convert, &$result)
		{
			if($this->IsGroupSuitable($group))
			{
				foreach($group->GetPhrases() as $id => $phrase)
				{
					$result[empty($gp) ? $id : $gp . $id] = $phrase;
				}
			}

			/** @var Group $sub */
			foreach($group->GetSubgroups() as $sub)
			{
				$groupName = $sub->GetName();
				$convert($sub, (empty($gp) ? '' : $gp) . (empty($groupName) ? '' : $groupName . self::PATH_SEPARATOR));
			}
		};

		/** @var Dictionary $lang */
		foreach(array_reverse($this->GetFallbackStack()) as $lang) { $convert($lang->group); }
		return $result;
	}

	/**
	 * Converts dictionary to the stdClass objects hierarchy that suitable for JSON conversion and sending to the client.
	 * Note that phrase can be replaced by group if they have same name and if they placed in one level in the hierarchy.
	 * @return \stdClass stdClass objects hierarchy that contains phrases and some additional info.
	 */
	public function ToClientFormat(): \stdClass
	{
		$convert = function(Group $group, \stdClass $parent) use(&$convert)
		{
			if($this->IsGroupSuitable($group))
			{
				foreach($group->GetPhrases() as $id => $phrase)
				{
					$parent->{StringTools::SnakeToCamelCase($id)} = $phrase;
				}
			}

			/** @var Group $sub */
			foreach($group->GetSubgroups() as $sub)
			{
				if($sub->IsNameless())
				{
					$convert($sub, $parent);
				}
				else
				{
					$name = StringTools::SnakeToCamelCase($sub->GetName());
					$container = isset($parent->{$name}) && is_object($parent->{$name}) ? $parent->{$name} : new \stdClass;
					$convert($sub, $container);

					// Do not include empty groups
					if(!empty((array)$container))
					{
						$parent->{$name} = $container;
					}
				}
			}
		};

		$result = new \stdClass;
		/** @var Dictionary $lang */
		foreach($this->GetFallbackStack() as $lang) { $convert($lang->group, $result); }
		return $result;
	}

	/**
	 * Searches for the group with specified path.
	 * @param array $pathComponents Path components to the group.
	 * @return Group|null Found group or null if no group with specified path.
	 * @throws \Exception
	 */
	private function FindGroup(array $pathComponents)
	{
		$li = count($pathComponents);

		if($li-- === 0)
		{
			throw new \Exception("Path must not be empty.");
		}

		$seek = function(Group $parent, $i = 0) use(&$seek, $pathComponents, $li)
		{
			/** @var Group $sub */
			foreach($parent->GetSubgroups() as $sub)
			{
				if($sub->IsNameless())
				{
					$found = $seek($sub);
					if($found !== null)
					{
						return $found;
					}
				}

				if($sub->GetName() === $pathComponents[$i])
				{
					return $i === $li ? $sub : $seek($sub, $i + 1);
				}
			}

			// Nothing found
			return null;
		};

		return $seek($this->group);
	}

	/**
	 * @return array List of the all languages starting from this and and ending the deepest fallback language.
	 */
	protected function GetFallbackStack(): array
	{
		$result = [];
		$f      = $this;

		while($f !== null)
		{
			if(in_array($f, $result, true)) { break; }
			$result[] = $f;
			$f        = $f->fallback;
		}

		return $result;
	}

	/**
	 * Determines when to look to the phrases of the group or not.
	 * @param Group $group Group.
	 * @return bool True if need to look for the phrases in specified group.
	 */
	protected function IsGroupSuitable(Group $group): bool { return true; }

	public function serialize()
	{
		return serialize([
			$this->code,
			$this->fallbackCode,
			$this->name,
			$this->group
		]);
	}

	public function unserialize($serialized)
	{
		list(
			$this->code,
			$this->fallbackCode,
			$this->name,
			$this->group
		) = unserialize($serialized);
	}
}