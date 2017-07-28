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
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */

namespace modules\Page;
use modules\TemplateProcessor\TemplateProcessor;

/**
 * Full page.
 * @package modules\Page
 */
abstract class FullPage extends Page
{
	private $jsonVars = [];

	private $css = [];

	private $js = [];

	protected final function AssignToJson(array $vars)
	{
		$this->jsonVars += $vars;
	}

	public final function AddCss(string $file)
	{
		$name = CSS_PATH . $file . '.css';
		if(in_array($name, $this->css))
		{
			return;
		}
		$this->css[] = $name;
	}

	public final function AddCssToStart($file)
	{
		$path = CSS_PATH . $file . '.css';

		if(in_array($path, $this->css))
		{
			return;
		}

		array_unshift($this->css, $path);
	}

	protected final function AddJs($file)
	{
		$name = JS_PATH . $file . '.js';

		if(in_array($name, $this->js))
		{
			return;
		}

		$this->js[] = $name;
	}

	protected final function AddExternalJs($url)
	{
		if(in_array($url, $this->js))
		{
			return;
		}

		$this->js[] = $url;
	}

	protected  function ProcessPage(string $template, array $vars): string
	{
		$generatedPage = TemplateProcessor::ProcessTemplate($template, $vars);
		echo TemplateProcessor::ProcessTemplate('page_structure.tpl', ['page' => $generatedPage]);
	}

	/**
	 * Генерирует js код из массива переменных js.
	 * @deprecated
	 */
	private function GenerateJsVariables()
	{
		if(empty($this->jsVars) && empty($this->jsonVars))
		{
			return '';
		}

		$result = '<script>var ';

		foreach($this->jsVars as $name => $value)
		{
			$value = $this->GetJSVarVal($value);

			//Если переменная - массив
			if(is_array($value))
			{
				$buf = '[';

				for($j = 0; $j < count($value); $j++)
				{
					$buf .= $this->GetJSVarVal($value[$j]) . ($j === (count($value) - 1) ? '' : ',');
				}

				$value = $buf . ']';
			}

			$result .= $name . '=' . str_replace("\\", "\\\\", $value) . ',';
		}

		foreach($this->jsonVars as $name => $value)
		{
			$result .= "$name=$value,";
		}

		return substr($result, 0, -1) . ';</script>';
	}

	/**
	 * Преобразовывает значение для правильного определения в js коде.
	 * @param $val
	 * @return string
	 * @deprecated
	 */
	private function GetJSVarVal($val)
	{
		if(is_bool($val))
		{
			return $val ? 'true' : 'false';
		}
		if(is_string($val))
		{
			return "'$val'";
		}
		else
		{
			return $val;
		}
	}
}