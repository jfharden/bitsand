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

class Action {
	protected $type = 'class';
	protected $action;                // Original action requested
	protected $file;                  // File name the class lives in
	protected $class;                 // Namespaced class name to use
	protected $method;                // Method to call
	protected $args = array();        // Any passed arguments

	/**
	 * Main constructor
	 * @param string $class <p>The namespaced class name to use. If the class
	 *                      has a trailing / then it will call the default
	 *                      __construct magic method.</p>
	 * @param array $args <p>Any arguments to use.</p>
	 * @return null
	 */
	public function __construct($class, $args = array()) {
		$this->action = $class;

		$class = explode('/', $class);

		$this->method = array_pop($class);
		if (empty($this->method)) {
			$this->method = '__construct';
		}

		$this->class = implode('\\', $class);
		$this->args = $args;
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