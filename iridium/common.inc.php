<?php
/**
 * Common includes.
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

// TODO: remove
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'constants.php';
require_once 'Helpers/GeneralFunctions.php';

// Error codes
require_once 'Core/ErrorCode.php';

// Exceptions
require_once 'Core/Exceptions/OperationException.php';
require_once 'Core/Exceptions/AccessException.php';
require_once 'Core/Exceptions/DispatcherException.php';
require_once 'Core/Exceptions/RestrictionException.php';

// Tools
require_once 'Core/Tools/Tools.php';
require_once 'Core/Tools/ArrayTools.php';
require_once 'Core/Tools/StringTools.php';

// Log
require_once 'Core/Log/LogLevel.php';
require_once 'Core/Log/Log.php';

// Filter
require_once 'Core/Http/Filter/ValueType.php';
require_once 'Core/Http/Filter/FilterInput.php';
require_once 'Core/Http/Filter/FilterOption.php';
require_once 'Core/Http/Filter/IFilter.php';
require_once 'Core/Http/Filter/FilterException.php';
require_once 'Core/Http/Filter/ValueFilterException.php';
require_once 'Core/Http/Filter/InputFilterException.php';
require_once 'Core/Http/Filter/DefaultFilter.php';

// Request
require_once 'Core/Http/Request/ContentType.php';
require_once 'Core/Http/Request/Method.php';
require_once 'Core/Http/Request/Request.php';

// HTTP
require_once 'Core/Http/HTTP.php';

// Restriction
require_once 'Core/Restriction/Restriction.php';
require_once 'Core/Restriction/RestrictionManager.php';

// Route
require_once 'Core/Route/Route.php';
require_once 'Core/Route/RouteBuilder.php';

// Dispatcher
require 'Core/Dispatcher/RequestType.php';
require 'Core/Dispatcher/RequestDispatcher.php';
require 'Core/Dispatcher/Handler.php';
require 'Core/Dispatcher/RestrictedAccessHandler.php';
require 'Core/Dispatcher/Operation.php';

// Modules
require_once 'Core/Module/ModulesManager.php';
require_once 'Core/Module/IModule.php';

// Session
require_once 'Core/Session.php';
