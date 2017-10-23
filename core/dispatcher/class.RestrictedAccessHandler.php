<?php
/**
 * Restricted access handler.
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

namespace core\dispatcher;


use core\restriction\Restriction;
use core\restriction\RestrictionManager;

abstract class RestrictedAccessHandler extends Handler
{
	private $restrictionManager;

	private $preprocessExecuted = false;

	public function __construct()
	{
		$this->restrictionManager = new RestrictionManager;
	}

	protected function Require(Restriction $restriction)
	{
		if($this->preprocessExecuted)
		{
			throw new \Exception('Require method must be called before Preprocess stage.');
		}

		$this->restrictionManager->Require($restriction);
	}

	protected function Preprocess()
	{
		$this->preprocessExecuted = true;
		$this->restrictionManager->CheckRequirements();
	}
}