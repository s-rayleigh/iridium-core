<?php
/**
 * HTTP request content type.
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

namespace core\http\request;

/**
 * Request content type.
 * @package core\http\request
 */
abstract class ContentType
{
	/**
	 * Plain text.
	 */
	public const TEXT = 0;

	/**
	 * Urlencoded format.
	 */
	public const URLENCODED = 1;

	/**
	 * JSON format.
	 */
	public const JSON = 2;

	/**
	 * XML format.
	 */
	public const XML = 3;

	/**
	 * Binary.
	 */
	public const BINARY = 4;

	/**
	 * HTML format.
	 */
	public const HTML = 5;
}