<?php
/**
 * Index file.
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

require_once './../iridium/web.inc.php';

use Iridium\Core\Dispatcher\{RequestDispatcher, RequestType};
use Iridium\Core\Module\ModulesManager;
use Iridium\Core\Route\RouteBuilder;
use Iridium\Core\Http\{HTTP, Filter\ValueType, Filter\DefaultFilter};

$modulesList = include(ROOT_PATH . DIRECTORY_SEPARATOR . 'config/modules.php');

if(!empty($modulesList))
{
	$modulesManager = new ModulesManager(
		(new RouteBuilder)
			->SetPathPrefix('iridium')
			->SetNamespacePrefix('Iridium')
			->SetRawRoutePrefix('Modules')
	);

	$modulesManager->SetConfigsPath(ROOT_PATH . DIRECTORY_SEPARATOR . 'config/modules');
	$modulesManager->LoadModules($modulesList);
}

HTTP::RegisterFilter(new DefaultFilter());

$opqt = (new RequestType('op'))
	->SetPathPrefix('app')
	->SetNamespacePrefix('App')
	->SetClassSuffix('Operation')
	->SetFilePrefix('op.')
	->SetRawRoutePrefix('Handlers.Operations');

$requestDispatcher = new RequestDispatcher;
$requestDispatcher->RegisterRequestType($opqt);
$requestDispatcher->RegisterRequestType(
	$opqt->Clone('page')
		->SetClassSuffix('Page')
		->SetFilePrefix('page.')
		->SetRawRoutePrefix('Handlers.Pages')
);

if(isset($_GET['op']))
{
	define('OP', true);
	$requestDispatcher->Dispatch('op', HTTP::GetGet('op', ValueType::STRING));
}
else
{
	define('PAGE', true);
	$requestDispatcher->Dispatch('page', HTTP::GetGet('page', ValueType::STRING, 'index'));
}