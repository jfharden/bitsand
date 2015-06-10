<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File index.php
 |     Author: Pete Allison
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/

// Calculate the root path
$root_path = dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR;

// Configure the autoloading routine
include($root_path . 'Bitsand' . DIRECTORY_SEPARATOR . 'autoload.php');

// Push the basepath into the config object
use Bitsand\Config\Config;
Config::setBasePath($root_path);
Config::setAppDirectory('lt-booking');
Config::setVal('namespace', 'LTBooking');
Config::setVal('theme', 'custom');
Config::setVal('pci_harden', true);
Config::setVal('compress', true);
Config::setVal('ssl', false);
Config::setVal('git_repository', 'https://github.com/PeteAUK/bitsand/');
Config::setVal('display_errors', true);
Config::setVal('error_handler', 'Tracy');

Config::setVal('db_driver',           'MySQLi');
Config::setVal('db_host',             'localhost');
Config::setVal('db_user',             'root');
Config::setVal('db_password',         'password');
Config::setVal('db_database',         'ba');
Config::setVal('db_prefix',           'ba');
Config::setVal('salt',                'salt');
Config::setVal('login_tries',         3);
Config::setVal('password_minimum',    8);
Config::setVal('site_name',           'Bitsand'); // DB
Config::setVal('site_title',          'BA TEST'); // DB
Config::setVal('mail_protocol',       'mail');    // Can be mail or smtp
Config::setVal('mail_hostname',       '');
Config::setVal('mail_username',       '');
Config::setVal('mail_password',       '');
Config::setVal('mail_port',           25);
Config::setVal('mail_timeout',        5);
Config::setVal('event_contact_email', 'webmonkeypete@googlemail.com'); // DB
Config::setVal('event_contact',       'wmp');                          // DB
Config::setVal('tech_contact_email',  'pete@pa-sy.com');               // DB
Config::setVal('tech_contact',        'Pete');                         // DB

// Any customisations can occur here
if (file_exists(Config::getAppPath() . 'custom.php')) {
	include(Config::getAppPath() . 'custom.php');
}

use Bitsand\Registry;

$router = Registry::set('router', 'Bitsand\Routing\Router');

$resource = $router->getResource();

if ($resource && $resource->exists()) {
	$resource->output();
}

$router->map('GET|POST', '/', 'common/home', 'common/home');
if (file_exists(Config::getAppPath() . 'routes.php')) {
	include(Config::getAppPath() . 'routes.php');
}

// Initialise Bitsand
include($root_path . 'Bitsand' . DIRECTORY_SEPARATOR . 'init.php');


use Bitsand\Controllers\Action;
use Bitsand\Controllers\ActionRoute;
use Bitsand\Controllers\Front;

$controller = new Front();

$controller->dispatch(new ActionRoute('error/not_found'));

Registry::get('view')->output();