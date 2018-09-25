<?php
/**
 * Cryptocurrency module.
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

namespace Iridium\Modules\Cryptocurrency;

use Iridium\Core\Http\Request\ContentType;
use Iridium\Core\Http\Request\Request;
use Iridium\Core\Module\IModule;

require_once 'CommandException.php';

class Cryptocurrency implements IModule
{
	private static $defaultHost;

	private static $defaultPort;

	private static $defaultUsername;

	private static $defaultPassword;

	private $host;

	private $port;

	private $username;

	private $password;

	/**
	 * Initializes module.
	 * @param array $moduleConfig Module config array.
	 */
	public static function Init(array $moduleConfig)
	{
		self::$defaultHost = $moduleConfig['host'];
		self::$defaultPort = $moduleConfig['port'];
		self::$defaultUsername = $moduleConfig['user'];
		self::$defaultPassword = $moduleConfig['pass'];
	}

	/**
	 * Returns array of required modules.
	 * @return array Required modules.
	 */
	static function GetRequiredModules(): array
	{
		return [];
	}

	public function __construct()
	{
		$this->host = self::$defaultHost;
		$this->port = self::$defaultPort;
		$this->username = self::$defaultUsername;
		$this->password = self::$defaultPassword;
	}

	public function SetHost(string $host) : self
	{
		$this->host = $host;
		return $this;
	}

	public function SetPort(string $port) : self
	{
		$this->port = $port;
		return $this;
	}

	public function SetUsername(string $username) : self
	{
		$this->username = $username;
		return $this;
	}

	public function SetPassword(string $password) : self
	{
		$this->password = $password;
		return $this;
	}

	public function SetConnectionParameters(string $host, string $port, string $username, string $password) : self
	{
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		return $this;
	}

	/**
	 * Returns information about cryptocurrency.
	 * @return \stdClass Information about cryptocurrency.
	 */
	public function GetInfo() : \stdClass
	{
		return $this->SendRequest('getinfo');
	}

	/**
	 * Returns total balance of the wallet.
	 * @return float Balance of the wallet.
	 */
	public function GetBalance() : float
	{
		return $this->SendRequest('getbalance');
	}

	/**
	 * Returns label of the specified address.
	 * @param string $address Address.
	 * @return string Label of the specified address.
	 */
	public function GetAddressLabel(string $address) : string
	{
		return $this->SendRequest('getaccount', $address);
	}

	/**
	 * Return all addresses that have specified label.
	 * @param string $label Label.
	 * @return array All addresses that have specified label.
	 */
	public function GetAddressesByLabel(string $label) : array
	{
		return $this->SendRequest('getaddressesbyaccount', $label);
	}

	/**
	 * @return int Block count.
	 */
	public function GetBlockCount() : int
	{
		return $this->SendRequest('getblockcount');
	}

	/**
	 * @return int Connections count.
	 */
	public function GetConnectionCount() : int
	{
		return $this->SendRequest('getconnectioncount');
	}

	/**
	 * @return \stdClass Mining information.
	 */
	public function GetMiningInfo() : \stdClass
	{
		return $this->SendRequest('getmininginfo');
	}

	/**
	 * @return \stdClass Difficulty information.
	 */
	public function GetDifficulty() : \stdClass
	{
		return $this->SendRequest('getdifficulty');
	}

	/**
	 * Creates new address. If label was not specified, creates address without label.
	 * @param string $label Label for the address.
	 * @return string Created address.
	 */
	public function CreateNewAddress($label = '') : string
	{
		return $this->SendRequest('getnewaddress', $label);
	}

	/**
	 * Sends request to the currency node.
	 * @param string $method Request method.
	 * @param array $params Request parameters.
	 * @return mixed Response.
	 * @throws CommandException Thrown if currency node returns error.
	 */
	public function SendRequest(string $method, ...$params)
	{
		$result = (new Request("http://{$this->host}:{$this->port}"))
			->AddAuthorizationHeader($this->username, $this->password)
			->SetContentType(ContentType::JSON)
			->SetIgnoreErrors(true)
			->Send(['method' => $method, 'params' => $params, 'id' => 1]);

		$result = json_decode($result);

		if(!empty($result->error))
		{
			throw new CommandException($result->error->message, $result->error->code);
		}

		return $result->result;
	}
}