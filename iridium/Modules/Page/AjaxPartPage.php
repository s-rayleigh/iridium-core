<?php
/**
 * Ajax part page.
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

use Iridium\Core\Http\HTTP;

/**
 * Partof the page accessible by ajax.
 * @package Iridium\Modules\Page
 */
abstract class AjaxPartPage extends PartPage
{
	/**
	 * @var string Title of the page.
	 */
	private $title = '';

	/**
	 * @var array Data that will be returned with page content.
	 */
	private $data = [];

	protected final function Display(string $pageContent)
	{
		HTTP::SendJsonResponse(
			[
				'content' => $pageContent,
				'title' => $this->title,
				'timestamp' => TIMESTAMP,
				'data' => $this->data
			]
		);
	}

	/**
	 * Sets title that will be returned with page content.
	 * @param string $title
	 */
	protected function SetTitle(string $title)
	{
		$this->title = $title;
	}

	/**
	 * Adds data that will be returned with page content.
	 * @param array $data Data.
	 */
	protected function AddData(array $data)
	{
		$this->data += $data;
	}
}