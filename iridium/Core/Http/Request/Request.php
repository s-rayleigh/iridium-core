<?php
/**
 * HTTP request.
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

namespace Iridium\Core\Http\Request;

/**
 * HTTP request.
 * @package Iridium\Core\Http\Request
 */
class Request
{
	/**
	 * @var array Request headers.
	 */
	private $headers = [];

	/**
	 * @var string URL.
	 */
	private $url;

	/**
	 * @var string Proxy URL.
	 */
	private $proxyUrl;

	/**
	 * @var string Method.
	 */
	private $method = Method::POST;

	/**
	 * @var int Content type.
	 */
	private $contentType = ContentType::TEXT;

	/**
	 * @var string Content character set.
	 */
	private $contentCharset = 'utf-8';

	/**
	 * @var bool Get the result even if the result code is not 200.
	 */
	private $ignoreErrors = false;

	/**
	 * @var float Timeout of the request in seconds.
	 */
	private $timeout;

	/**
	 * Creates new HTTP request.
	 * @param string $url URL.
	 */
	public function __construct($url = '')
	{
		$this->url = $url;
		$this->timeout = (float)ini_get('default_socket_timeout');
	}

	/**
	 * Adds request header.
	 * @param string $header Header.
	 * @return Request Request.
	 */
	public function AddHeader(string $header) : self
	{
		if(in_array($header, $this->headers))
		{
			return $this;
		}

		$this->headers[] = $header;
		return $this;
	}

	/**
	 * Adds authorization header.
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return Request Request.
	 */
	public function AddAuthorizationHeader(string $username, string $password) : self
	{
		$this->AddHeader('Authorization: Basic ' . base64_encode($username . ':' . $password));
		return $this;
	}

	/**
	 * Sets URL.
	 * @param string $url URL.
	 * @return Request Request.
	 */
	public function SetUrl(string $url) : self
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Sets proxy URL.
	 * @param string $url Proxy URL.
	 * @return Request Request.
	 */
	public function SetProxyUrl(string $url) : self
	{
		$this->proxyUrl = $url;
		return $this;
	}

	/**
	 * Sets request method.
	 * Default POST.
	 * @see Method
	 * @param string $method Request method.
	 * @return Request Request.
	 */
	public function SetMethod(string $method) : self
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Sets request content type.
	 * Default plain text.
	 * @see ContentType
	 * @param int $contentType Content type.
	 * @return Request Request.
	 */
	public function SetContentType(int $contentType) : self
	{
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * Sets content character set.
	 * Default utf-8.
	 * @param string $charset Content character set.
	 * @return Request Request.
	 */
	public function SetContentCharset(string $charset) : self
	{
		$this->contentCharset = $charset;
		return $this;
	}

	/**
	 * Sets ignore errors.
	 * Default false.
	 * @param bool $ignone Ignore errors.
	 * @return Request Request.
	 */
	public function SetIgnoreErrors(bool $ignone) : self
	{
		$this->ignoreErrors = $ignone;
		return $this;
	}

	/**
	 * Sets timeout of the request.
	 * @param float $timeout Timeout in seconds.
	 * @return Request Request.
	 */
	public function SetTimeout(float $timeout) : self
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Sends request with specified content.
	 * @param string $content Content.
	 * @return bool|string Response text or false if error has occured.
	 */
	public function Send($content = '')
	{
		if(empty($this->url))
		{
			throw new \InvalidArgumentException('URL of the request should not be empty.');
		}

		if(!empty($content))
		{
			switch($this->contentType)
			{
				case ContentType::TEXT:
					$this->AddHeader('Content-type: text/plain; charset=' . $this->contentCharset);
					break;
				case ContentType::URLENCODED:
					$content = http_build_query($content);
					$this->AddHeader('Content-type: application/x-www-form-urlencoded; charset=' . $this->contentCharset);
					break;
				case ContentType::JSON:
					$content = json_encode($content);
					$this->AddHeader('Content-type: application/json; charset=' . $this->contentCharset);
					break;
				case ContentType::XML:
					$this->AddHeader('Content-Type: application/xml; charset=' . $this->contentCharset);
					break;
				case ContentType::BINARY:
					$this->AddHeader('Content-Type: multipart/form-data; charset=' . $this->contentCharset);
					break;
				case ContentType::HTML:
					$this->AddHeader('Content-Type: text/html; charset=utf-8; charset=' . $this->contentCharset);
					break;
			}

			if(is_string($content))
			{
				$this->AddHeader('Content-Length: ' . strlen($content));
			}
		}

		$options = [
			'http' => [
				'header'  => implode("\r\n", $this->headers) . "\r\n",
				'method'  => $this->method,
				'content' => is_string($content) ? $content : '',
				'ignore_errors' => $this->ignoreErrors,
				'proxy' => $this->proxyUrl,
				'request_fulluri' => !empty($this->proxyUrl),
				'timeout' => $this->timeout
			]
		];

		return file_get_contents($this->url, false, stream_context_create($options));
	}
}