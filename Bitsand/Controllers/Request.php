<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Request.php
 ||    Summary: Provides access to the POST and GET variables, but handled so
 ||             as to prevent any XSS injection issues.
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

class Request {
	public $get = array();
	public $post = array();
	public $cookie = array();
	public $files = array();
	public $server = array();

	public function __construct() {
		$_GET = $this->clean($_GET);
		$_POST = $this->clean($_POST);
		$_REQUEST = $this->clean($_REQUEST);
		$_COOKIE = $this->clean($_COOKIE);
		$_FILES = $this->clean($_FILES);
		$_SERVER = $this->clean($_SERVER, array('SERVER_SIGNATURE', 'SCRIPT_FILENAME', 'REDIRECT_QUERY_STRING', 'REDIRECT_URL', 'QUERY_STRING', 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF'));

		$this->get = $_GET;
		$this->post = $_POST;
		$this->request = $_REQUEST;
		$this->cookie = $_COOKIE;
		$this->files = $_FILES;
		$this->server = $_SERVER;

		// Remove any trailing slashes from the redirected route
		if (isset($this->get['_route_'])) {
			$this->get['_route_'] = rtrim($this->get['_route_'], '/');
		}
	}

	/**
	 * Performs XSS protection (cleaning) on the passed data by html encoding
	 * it.  If passed any exclude keys then don't perform it on these, this is
	 * useful for ensuring certain server variables aren't messed with.
	 *
	 * @param string|array $data
	 * @param array $exclude_keys [Optional]
	 * @return string|array
	 */
	public function clean($data, $exclude_keys = false) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);
				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			if ($exclude_keys !== false && in_array($key, $exclude_keys)) {
				$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
			}
		}

		return $data;
	}

	/**
	 * Returns the current method being used
	 * @return string
	 */
	public function method() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	/**
	 * Checks to see if the request has been made via an AJAX call
	 * @return boolean
	 */
	public function isAJAX() {
		if (isset($this->server['HTTP_X_REQUESTED_WITH']) && $this->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			return true;
		} else {
			return false;
		}
	}
}