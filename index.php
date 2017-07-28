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
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */

//TODO: to parameters
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'core/constants.php';

require 'config/config.php';
require 'config/php_params.php';

require 'helpers/GeneralFunctions.php';

//Defined in helpers/GeneralFunction.php
set_exception_handler('exception_handler');

//Log
require 'core/log/class.LogLevel.php';
require 'core/log/class.Log.php';
use core\log\Log;
Log::Init();

require 'core/class.ErrorCode.php';

require 'core/exceptions/class.NoticeableException.php';
require 'core/exceptions/class.OperationException.php';
require 'core/exceptions/class.UploadException.php';
require 'core/exceptions/class.AccessException.php';
require 'core/exceptions/class.DispatcherException.php';
require 'core/exceptions/class.SidePanelException.php';

require 'core/route/class.RouteData.php';
require 'core/route/class.RouteBuilder.php';

require 'core/restriction/class.Restriction.php';
require 'core/restriction/class.RestrictionManager.php';

require 'core/dispatcher/class.QueryType.php';
require 'core/dispatcher/class.QueryDispatcher.php';
require 'core/dispatcher/class.Handler.php';
require 'core/dispatcher/class.RestrictedAccessHandler.php';

require 'core/module/class.ModulesManager.php';
require 'core/module/intefrace.IModule.php';

//Request
require 'core/http/request/class.ContentType.php';
require 'core/http/request/class.Method.php';
require 'core/http/request/class.Request.php';

//Filter
require 'core/http/filter/class.FilterException.php';
require 'core/http/filter/class.ValueFilterException.php';
require 'core/http/filter/class.InputFilterException.php';
require 'core/http/filter/class.ValueType.php';
require 'core/http/filter/class.FilterInput.php';
require 'core/http/filter/class.FilterOption.php';
require 'core/http/filter/interface.IFilter.php';
require 'core/http/filter/class.DefaultFilter.php';

require 'core/http/class.HTTP.php';
require 'core/class.Operation.php';


//TODO: перенести вверх
use core\http\HTTP;
use core\http\filter\ValueType;

HTTP::RegisterFilter(new core\http\filter\DefaultFilter);

//if(Session::IsUserHasSessionId())
//{
//	new Session;
//}

$modulesList = include('config/modules.php');

if(!empty($modulesList))
{
	$modulesManager = new \core\module\ModulesManager();
	$modulesManager->SetModulesRoute('modules');
	$modulesManager->SetConfigsPath('config/modules');
	$modulesManager->LoadModules($modulesList);
}

$queryDispatcher = new \core\dispatcher\QueryDispatcher();
$queryDispatcher->RegisterQueryType((new \core\dispatcher\QueryType('op'))->SetClassSuffix('Operation')->SetFilePrefix('op.')->SetRawRoutePrefix('site.operations'));
$queryDispatcher->RegisterQueryType((new \core\dispatcher\QueryType('page'))->SetClassSuffix('Page')->SetFilePrefix('page.')->SetRawRoutePrefix('site.pages'));


if(isset($_GET['op']))
{
	define('OP', true);
	$queryDispatcher->Dispatch('op', HTTP::GetGet('op', ValueType::STRING));
}
else
{
	define('PAGE', true);
	$queryDispatcher->Dispatch('page', HTTP::GetGet('page', ValueType::STRING, 'index'));
}

Log::Save();