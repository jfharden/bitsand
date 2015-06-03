<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/ActionRoute.php
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

use Bitsand\Config\Config;
use Bitsand\Utilities\Functions;

class ActionRoute extends Action {
	protected $type = 'controller';

	/**
	 * Main constructor
	 * @param string $route <p>Route to the controller file within the main
	 *                      application folder.</p>
	 * @param array $args <p>Any arguments to use.</p>
	 * @return null
	 */
	public function __construct($route, $args = array()) {
		$this->action = $route;

		$path = '';
		$class = '';
		$parts = explode('/', str_replace(array('../', '..\\', '..'), '', (string)$route));

		foreach ($parts as $part) {
			$path .= $part;
			$class .= ucwords(preg_replace_callback('/[_|-]([a-zA-Z0-9])/', function($c) {
				return strtoupper($c[1]);
			}, $part));

			// All routes point to a controller file
			if (is_dir(Config::getAppPath() . 'controller' . DIRECTORY_SEPARATOR . $path)) {
				$path .= DIRECTORY_SEPARATOR;
				$class .= ' ';
				array_shift($parts);
				continue;
			}

			if (is_file(Config::getAppPath() . 'controller' . DIRECTORY_SEPARATOR . str_replace('../', '', $path) . '.php')) {
				$this->file = Config::getAppPath() . 'controller' . DIRECTORY_SEPARATOR . str_replace('../', '', $path) . '.php';
				$this->class = Config::getVal('namespace') . '/Controller/' . preg_replace('/[^a-zA-Z0-9]/', '', $class);
				array_shift($parts);
				break;
			}
		}

		// If no controller has been found, look in the built in folder
		if (is_null($this->class) && is_null($this->file)) {
			$class = preg_replace('/[^a-zA-Z0-9]/', '', ucwords(str_replace(array('_', '/'), ' ', $route)));
			if (file_exists(str_replace('/', DIRECTORY_SEPARATOR, Config::getBasePath() . 'Bitsand/Builtin/Controller/' . $class . '.php'))) {
				$this->class = 'Bitsand/Builtin/Controller/' . $class;
				$this->file = str_replace('/', DIRECTORY_SEPARATOR, Config::getBasePath() . 'Bitsand/Builtin/Controller/' . $class . '.php');
				$parts = array();
			}
		}

		// Flick the slashes round so they become namespaces
		$this->class = str_replace('/', '\\', $this->class);

		if (!empty($args)) {
			$this->args = $args;
		}

		$method = array_shift($parts);

		if ($method) {
			$this->method = $method;
		} else {
			// Controllers have an index file
			$this->method = 'index';
		}
	}

	/**
	 * Retrieves the class reference
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * Retrieves the method to call
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Retrieves any arguments
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * Returns if the action is a controller file or not.  If not then it will
	 * be a normal namespaced class
	 * @return boolean
	 */
	public function isController() {
		return $this->type == 'controller';
	}

	/**
	 * Returns the original action call string
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}
}