<?php
/**
 * Page module.
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

namespace modules\Page;

use core\dispatcher\RestrictedAccessHandler;
use core\module\IModule;

/**
 * Page.
 * @package modules\Page
 */
abstract class Page extends RestrictedAccessHandler implements IModule
{
	/**
	 * @var array Template variables.
	 */
	private $vars = [];

	public static function Init(array $moduleConfig)
	{

	}

	public static function GetRequiredModules() : array
	{
		return ['TemplateProcessor'];
	}
	
	protected abstract function GetTemplateName() : string;

	/**
	 * Assigns template variables.
	 * @param array $vars
	 */
	protected final function Assign(array $vars)
	{
		$this->vars += $vars;
	}

	/**
	 * Clears template variables.
	 */
	protected final function ClearVars()
	{
		$this->vars = [];
	}


	protected final function Prepare()
	{

		//Google analytic
		if(GA_ENABLE)
		{
			$this->AddExternalJs('//www.google-analytics.com/analytics.js');
			$this->AddJs('google_analytics');
			$this->AssignToJs(['gaId' => GA_ID]); //Идентификатор отслеживания
		}
	}

	protected final function Process()
	{
		echo $this->ProcessPage($this->GetTemplateName(), $this->vars);
	}

	protected abstract function ProcessPage(string $template, array $vars) : string;

}

require 'class.FullPage.php';
require 'class.PartPage.php';