<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Route.php
 ||    Summary: The Route class handles the mapping of all requested URLs to
 ||             their appropriate controller file and methods within that file.
 ||
 ||             A route in it's simplest form cross references a URL to a
 ||             specific method within a Controller class.
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

namespace Bitsand\Controllers;

use Bitsand\Registry;
use Bitsand\Config\Config;

class Route {
	/**
	 * Location of the cache file
	 * @var string
	 */
	private $_cache_file = '/var/routes/route.cache';

	/**
	 * Initialises the Route object
	 */
	public function __construct() {
		$this->_cache_file = Config::getBasePath() . $this->_cache_file;
	}

	/**
	 * Returns the current route
	 * @param boolean $as_action [Optional] If false (default) then return as a
	 * plain string, if true then return as an ActionRoute object
	 * @return mixed
	 */
	public function getRoute($as_action = false) {
		$route = $this->match();

		if (!$as_action) {
			return $route;
		} else {
			return new \Bitsand\Controllers\ActionRoute($route);
		}
	}

	private function match() {
		$params = array();
		$match = false;

		$route = '';

		$request = Registry::get('request');

		// Calculate base path. We assume that there are no additional redirects in place
		/*$base_path = str_replace(array($request->server['DOCUMENT_ROOT'], 'index.php'), '', $request->server['SCRIPT_FILENAME']);

		// Retrieve the uri using the best available method
		if (isset($request->server['REDIRECT_URL'])) {
			$request_uri = $request->server['REDIRECT_URL'];
		} elseif (isset($request->server['REQUEST_URI'])) {
			$request_uri = rawurldecode($request->server['REQUEST_URI']);
			if (($strpos = strpos($request_uri, '?')) !== false) {
				$request_uri = substr($request_uri, 0, $strpos);
			}
		} else {
			$request_uri = '/';
		}

		$request_method = isset($request->server['REQUEST_METHOD']) ? $request->server['REQUEST_METHOD'] : 'GET';

		$route = str_replace($base_path, '', $request_uri);*/

		if (isset($request->get['_route_'])) {
			$route = trim($request->get['_route_'], '/');
		} elseif (!isset($request->get['_resource_'])) {
			$route = 'common/home';
		}

		return $route;
	}
}