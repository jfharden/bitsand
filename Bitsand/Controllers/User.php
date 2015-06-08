<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/User.php
 ||    Summary: A base user object, this will
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

class User {
	private $user_id;

	private $is_admin = false;

	private $name;

	protected $session;

	public function __construct() {
		$this->session = Registry::get('session');

		if (isset($this->session->data['user_id'])) {
			$this->user_id = (int)$this->session->data['user_id'];
		}
	}

	/**
	 * Checks to see if the user is logged in
	 * @return boolean
	 */
	public function isLogged() {
		return $this->user_id !== null;
	}

	/**
	 * Checks to see if the current user has admin rights
	 * @return boolean
	 */
	public function isAdmin() {
		return $this->is_admin;
	}

	/**
	 * Logs a user in, setting the passed data in the session.
	 * $data must contain a user_id field
	 * @param array $data
	 */
	public function logIn($data) {
		if (!isset($data['user_id'])) {
			throw new \Bitsand\Exceptions\NoUserIDSetException('No user_id key set in user logIn');
		}
		foreach ($data as $key => $value) {
			$this->session->data[$key] = $value;
		}
		$this->session->data['keys'] = array_keys($data);
	}

	/**
	 * Logs the user out and unsets the various keys within the session
	 */
	public function logOut() {
		if (isset($this->session->data['keys'])) {
			foreach ($this->session->data['keys'] as $key) {
				unset($this->session->data[$key]);
			}
			unset($this->session->data['keys']);
		}
	}

	/**
	 * Returns the logged in user's name
	 * @return string
	 */
	public function getName() {
		if (!is_null($this->name)) {
			$name = (isset($this->session->data['firstname']) ? $this->session->data['name'] : '') . ' ' . (isset($this->session->data['lastname']) ? $this->session->data['name'] : '');

			$this->name = trim($name);
		}

		return $this->name;
	}
}