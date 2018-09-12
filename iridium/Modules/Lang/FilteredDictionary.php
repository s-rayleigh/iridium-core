<?php
/**
 * Dictionary, filtered by keywords.
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
 * Dictionary, filtered by keywords.
 * @package Iridium\Modules\Lang
 */
class FilteredDictionary extends Dictionary
{
	private $keywords;

	public function __construct(Dictionary $dictionary, array $keywords)
	{
		parent::__construct($dictionary->code, $dictionary->name, $dictionary->group);
		$this->fallback = $dictionary->fallback;
		$this->keywords = $keywords;
	}

	protected function IsGroupSuitable(Group $group): bool
	{
		return $group->HasKeywords($this->keywords);
	}
}