<?php
/**
 * Group of phrases.
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
 * Group of phrases.
 * @package Iridium\Modules\Lang
 */
class Group
{
	/**
	 * @var string Name.
	 */
	private $name;

	private $dir;

	/**
	 * @var string[] Keywords.
	 */
	private $keywords;

	/**
	 * @var Group[] List of subgroups.
	 */
	private $sub;

	/**
	 * @var string[] List of phrases.
	 */
	private $phrases;

	/**
	 * Creates new group of phrases.
	 * @param string $name Name of the group.
	 */
	public function __construct(string $name = '')
	{
		$this->name     = $name;
		$this->keywords = [];
		$this->sub      = [];
		$this->phrases  = [];
	}

	/**
	 * @return string Name of the group.
	 */
	public function GetName(): string
	{
		return $this->name;
	}

	/**
	 * Sets the name of this group.
	 * @param string $name New name of the group.
	 */
	public function SetName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return string Path to directory of the file of this group or empty string if this group is not file.
	 */
	public function GetDir(): string
	{
		return empty($this->dir) ? '' : $this->dir;
	}

	/**
	 * Sets path to directory of the file of this group.
	 * @param string $path Path to the directory.
	 */
	public function SetDir(string $path)
	{
		$this->dir = $path;
	}

	/**
	 * @return bool True if group is nameless.
	 */
	public function IsNameless(): bool
	{
		return empty($this->name);
	}

	/**
	 * Adds multiple keywords to this group.
	 * @param string[] $keywords Keywords to be added.
	 */
	public function AddKeywords(array $keywords)
	{
		$this->keywords = array_merge($this->keywords, $keywords);
	}

	/**
	 * Adds keyword to this group.
	 * @param string $keyword Keyword to be added.
	 */
	public function AddKeyword(string $keyword)
	{
		array_push($this->keywords, $keyword);
	}

	/**
	 * @return string[] Keywords of this group.
	 */
	public function GetKeywords(): array
	{
		return $this->keywords;
	}

	/**
	 * Determines if group has the specified keyword.
	 * @param string $keyword Keyword.
	 * @return bool True if group has specified keyword.
	 */
	public function HasKeyword(string $keyword): bool
	{
		return in_array($keyword, $this->keywords, true);
	}

	/**
	 * Determines if group has the specified keywords.
	 * @param string[] $keywords Keywords.
	 * @return bool True if group has all of the specified keywords.
	 */
	public function HasKeywords(array $keywords): bool
	{
		return !array_diff($keywords, $this->keywords);
	}

	/**
	 * Adds new phrase to the group. If phrase with specified id is already in group, it will be replaced.
	 * @param string $id Identifier of the phrase.
	 * @param string $phrase Text of the phrase.
	 */
	public function AddPhrase(string $id, string $phrase)
	{
		$this->phrases[$id] = $phrase;
	}

	/**
	 * @param string $id Identifier of the phrase.
	 * @return null|string Phrase or null if no phrase with specified id.
	 */
	public function GetPhrase(string $id)
	{
		if(array_key_exists($id, $this->phrases)) { return $this->phrases[$id]; }

		foreach($this->sub as $sub)
		{
			// Search for the phrase in the child nameless groups
			if($sub->IsNameless())
			{
				$subphr = $sub->GetPhrase($id);
				if($subphr !== null) { return $subphr; }
			}
		}

		return null;
	}

	/**
	 * @return string[] Phrases.
	 */
	public function GetPhrases(): array
	{
		return $this->phrases;
	}

	/**
	 * Adds subgroup to this group.
	 * @param Group $subgroup Subgroup to be added.
	 */
	public function AddSubgroup(Group $subgroup)
	{
		$found = $this->FindSubgroup($subgroup->name);

		if(empty($found))
		{
			$this->sub[] = $subgroup;
			return;
		}

		$found->Merge($subgroup);
	}

	/**
	 * @return Group[] Subgroups of this group.
	 */
	public function GetSubgroups(): array
	{
		return $this->sub;
	}

	/**
	 * Searches for the subgroup with the specified name.
	 * @param string $name Name of the group to be searched.
	 * @return Group|null Group or null if no subgroup with specified name.
	 */
	public function FindSubgroup(string $name)
	{
		foreach($this->sub as $gr)
		{
			if($gr->name === $name)
			{
				return $gr;
			}
		}

		return null;
	}

	/**
	 * Merges this group with the specified end returs this group.
	 * @param Group $group Group for merge.
	 * @return Group Self.
	 */
	public function Merge(Group $group): self
	{
		$this->phrases += $group->phrases;

		foreach($group->sub as $subgroup)
		{
			$found = $this->FindSubgroup($subgroup->name);

			if(!empty($found))
			{
				$found->Merge($subgroup);
			}
		}

		return $this;
	}
}