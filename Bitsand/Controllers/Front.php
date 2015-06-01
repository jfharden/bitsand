<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Action.php
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
use Bitsand\Registry;

final class Front {
	protected $pre_action = array();
	protected $error;

	public function __get($key) {
		return Registry::get($key);
	}

	public function __set($key, $value) {
		var_dump(__FILE__ . ':Ln' . __LINE__);
		var_dump($key);
		var_dump($value);
		die();
	}

	public function addPreAction($pre_action) {
		$this->pre_action[] = $pre_action;
	}

	public function dispatch($error) {
		// Holds the error action to call if there is a problem
		$this->error = $error;

		$action = Registry::get('route')->getRoute(true);

		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);

			// If a new action is passed back then use this instead of the proposed one
			if ($result) {
				$action = $result;
				break;
			}
		}

		/*
		 * Execute the action until it no longer returns something to do
		 */
		while ($action) {
			$action = $this->execute($action);
		}
	}

	private function execute($action_details) {
		$class = $action_details->getClass();
		$method = $action_details->getMethod();
		$args = $action_details->getArgs();
		$is_controller = $action_details->isController();

		$action = '';
		if ($is_controller === true && method_exists($class, $method)) {
			$controller = new $class();
			call_user_func_array(array($controller, $method), $args);
		} elseif ($is_controller === false && method_exists($class, $method)) {
			$action = call_user_func_array(array($class, $method), $args);
		} else {
			if (!empty($this->error)) {
				$action = $this->error;
				$this->error = '';
			} else {
				if (!empty($is_controller)) {
					throw new \Bitsand\Exceptions\ControllerNotFoundException('Controller file not found: ' . $action_details->getAction() . '->' . $method);
				} else {
					throw new \Bitsand\Exceptions\ClassNotFoundException('Class file not found: ' . $class . '->' . $method);
				}
			}
		}

		return $action;
	}
}