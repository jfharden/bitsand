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

// Any customisations can occur here
if (file_exists(Config::getAppPath() . 'custom.php')) {
	include(Config::getAppPath() . 'custom.php');
}

use Bitsand\Controllers\Resource;

// Look to see if we are serving a resource - only time we ever use $_GET directly
if (isset($_GET['_resource_'])) {
	// Resources are unique because we just pipe the correct item
	include($root_path . 'Bitsand' . DIRECTORY_SEPARATOR . 'init_resource.php');

	$resource = new Resource();
	if ($resource->exists()) {
		$resource->output();
	}
}

// Initialise Bitsand
include($root_path . 'Bitsand' . DIRECTORY_SEPARATOR . 'init.php');

use Bitsand\Controllers\Action;
use Bitsand\Controllers\ActionRoute;
use Bitsand\Controllers\Front;

$controller = new Front();

$controller->dispatch(new ActionRoute('error/not_found'));

use Bitsand\Registry;

Registry::get('view')->output();