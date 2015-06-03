<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/init.php
 ||    Summary: Initialises Bitsand for use, hooking in error handling,
 ||             shutdown functions and the main registry components.
 ||
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2015 The Bitsand Project
 ||             (http://github.com/PeteAUK/bitsand)
 ||
 || Bitsand is free software; you can redistribute it and/or modify it under the
 || terms of the GNU General Public License as published by the Free Software
 || Foundation, either version 3 of the License, or (at your option) any later
 || version.
 ||
 || Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 || WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 || FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 || details.
 ||
 || You should have received a copy of the GNU General Public License along with
 || Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 ++--------------------------------------------------------------------------*/

namespace Bitsand;

use Bitsand\Registry;
use Bitsand\Controllers\Route;
use Bitsand\Config\Config;
use Bitsand\Utilities\ShutdownHandler;
use Bitsand\Utilities\Tracy;
use Bitsand\Utilities\ErrorHandler;

// Bitsand version being run
define('BITSAND_VERSION', '9.0');

// The request object is core to routing, so we need to set this up first
$request = Registry::set('request', 'Bitsand\Controllers\Request');

// We need to establish what App space we are running within
//$route = Registry::set('route', 'Bitsand\Controllers\Route');
$router = Registry::get('router');

// Load up the App config file
//Config::loadConfigFile(Config::getAppPath() . 'Application.config');

// Logging mechanism
Registry::set('log', 'Bitsand\Logging\Log');

/*
 * We have two options for debugging, either the standard method or the more
 * advanced debugger "Tracy".  For production it's adviced to use the standard
 * method which has a lower overhead cost.
 */
if (Config::getVal('error_handler') == 'Tracy') {
	Tracy::init();
} else {
	ErrorHandler::registerHandlers();
}

ShutdownHandler::registerShutdown();

// We now need to store the common "site" bits in the registry
Registry::set('view', 'Bitsand\Controllers\View');
Registry::set('session', 'Bitsand\Controllers\Session');
Registry::set('document', 'Bitsand\Controllers\Document');
//Registry::set('device', 'Bitsand\Utilities\Device');
Registry::set('config', 'Bitsand\Config\Config');
Registry::set('load', 'Bitsand\Controllers\Loader');

//Registry::set('db', '\Bitsand\Database\DB');

// Define urls here
define('HTTP_BOOKING', 'http://' . $router->getBaseUrl());
if (Config::getVal('ssl') === true) {
	define('HTTPS_BOOKING', 'https://' . $router->getBaseUrl());
	define('HTTP_SCHEMA', isset($request->server['HTTPS']) && $request->server['HTTPS'] != 'off' ? 'https://' : 'http://');
} else {
	define('HTTPS_BOOKING', HTTP_BOOKING);
	define('HTTP_SCHEMA', 'http://');
}