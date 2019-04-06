<?php
/**
 * Full page.
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

namespace Iridium\Modules\Page;

use Iridium\Modules\TemplateProcessor\TemplateProcessor;

/**
 * Full page.
 * @package Iridium\Modules\Page
 */
abstract class FullPage extends Page
{
	/**
	 * @var string Title of the page.
	 */
	private $title = '';

	/**
	 * @var array JSON data that will be placed in 'pageData' javascript variable on the page.
	 */
	private $jsonVars = [];

	/**
	 * @var array Variables for the page structure template.
	 */
	private $structVars = [];

	/**
	 * @var array Included CSS files.
	 */
	private $css = [];

	/**
	 * @var array Included JS files.
	 */
	private $js = [];

	/**
	 * Sets page title.
	 * @param string $title Page title.
	 */
	public function SetTitle(string $title)
	{
		$this->title = $title;
	}

	/**
	 * Assigns variables to the 'pageData' javascript variable.
	 * @param array $vars Variables.
	 */
	protected final function AssignJsData(array $vars)
	{
		$this->jsonVars += $vars;
	}

	/**
	 * Assigns page structure variables.
	 * @param array $vars Variables.
	 */
	protected final function AssignToStruct(array $vars)
	{
		$this->structVars += $vars;
	}

	/**
	 * Includes CSS file to the page.
	 * Extension must be omitted.
	 * @param string $file CSS file name.
	 * @param string $version Version of the file. Change to update the browser cache.
	 */
	public final function IncludeCss(string $file, string $version = '')
	{
		$name = self::$moduleConfig['css_path'] . $file . '.css';

		if(!empty($version))
		{
			$name .= '?v=' . $version;
		}

		if(in_array($name, $this->css))
		{
			return;
		}

		$this->css[] = $name;
	}

	/**
	 * Includes CSS file at the beginning of the list to the page.
	 * Extension must be omitted.
	 * @param string $file CSS file name.
	 * @param string $version Version of the file. Change to update the browser cache.
	 */
	public final function IncludeFirstCss(string $file, string $version = '')
	{
		$path = self::$moduleConfig['css_path'] . $file . '.css';

		if(!empty($version))
		{
			$path .= '?v=' . $version;
		}

		if(in_array($path, $this->css))
		{
			return;
		}

		array_unshift($this->css, $path);
	}

	/**
	 * Includes JS file to the page.
	 * Extension must be omitted.
	 * @param string $file JS file name.
	 * @param string $version Version of the file. Change to update the browser cache.
	 */
	protected final function IncludeJs(string $file, string $version = '')
	{
		$name = self::$moduleConfig['js_path'] . $file . '.js';

		if(!empty($version))
		{
			$name .= '?v=' . $version;
		}

		if(in_array($name, $this->js))
		{
			return;
		}

		$this->js[] = $name;
	}

	/**
	 * Includes external JS file to the page by URL.
	 * @param string $url URL of the JS file.
	 */
	protected final function IncludeExternalJs(string $url)
	{
		if(in_array($url, $this->js))
		{
			return;
		}

		$this->js[] = $url;
	}

	protected function Prepare()
	{
		parent::Prepare();

		// Google Analytics
		if(self::$moduleConfig['google_analytics']['enabled'])
		{
			$this->IncludeExternalJs('//www.google-analytics.com/analytics.js');
			$this->IncludeJs('google_analytics'); // TODO: place js file
			$this->AssignJsData(['gaId' => self::$moduleConfig['google_analytics']['id']]);
		}
	}

	protected final function ProcessPage(string $template, array $vars): string
	{
		$generatedPage = TemplateProcessor::ProcessTemplate($template, $vars);
		$pageData = '<script>pageData=' . json_encode($this->jsonVars) . '</script>';

		return TemplateProcessor::ProcessTemplate(
			'page_structure.tpl',
			[
				'title'        => $this->title,
				'page'         => $generatedPage,
				'js_page_data' => $pageData,
				'css'          => $this->css,
				'js'           => $this->js,
			] + $this->structVars
		);
	}
}