<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/user/user.php
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

namespace LTBooking\Model;

use Bitsand\Controllers\Model;

class UserUser extends Model {
	const LOCKED_USER = 'ACCOUNT DISABLED';

	const INCORRECT = 0;
	const JUST_LOCKED = 1;
	const LOCKED = 2;
	const LOGGED_IN = 3;

	public function login($email, $password, $override = false) {
		$email = strtolower(trim($email));

		// Pull the whole user from the database, sort by login in case of duplicate registrations
		$user_query = $this->db->query("SELECT plPlayerID, plPassword, plOldSalt, plLoginCounter FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC");

		if ($user_query->num_rows == 0) {
			// No e-mails match so just say "incorrect"
			return self::INCORRECT;
		} elseif ($user_query->num_rows > 1) {
			// More than one e-mail registered :(
			$this->log->write('E-mail "' . $email . '" has more than one registered user.');
		}

		// Grab the player ID
		$user_id = (int)$user_query->row['plPlayerID'];

		// Calculate the encrypted password, with either the current or previous salt
		$encrypted_password = $this->_encryptPassword($password, (int)$user_query->row['plOldSalt']);

		// If not logging on as an admin or the password doesn't match, fail
		if (!$override && $encrypted_password !== $user_query->row['plPassword']) {
			// We need to record a login attempt
			return $this->_registerIncorrect($user_id, $user_query->row['plLoginCounter'], $user_query->row['plPassword'] == self::LOCKED_USER);
		}

		// If we're here, then we're legitimately meant to be!
		if (!$override) {
			$user = $user_query->row;

			// Not an admin so record that we've logged in and sort out the password if necessary
			if ((int)$user_query->row['plOldSalt'] == 0) {
				$this->db->query("UPDATE " . DB_PREFIX . "players SET plLastLogin = NOW(), plLoginCounter = '0' WHERE plPlayerID = '" . $user_id . "'");
			} else {
				$new_password = $this->_encryptPassword($password);
				$this->db->query("UPDATE " . DB_PREFIX . "players SET plLastLogin = NOW(), plLoginCounter = '0', plPassword = '" . $this->db->escape($new_password) . "', plOldSalt = '0' WHERE plPlayerID = '" . $user_id . "'");
			}

			/*
			 * The following section handles the session elements of the site.
			 * We store our "logged in" state in within the session variable,
			 * but also record details of the user in the database.
			 */
			$this->load->model('user/session');

			$this->model_user_session->register($user_id);


			$this->user->logIn(array(
				'user_id' => $user_id,
				'is_admin' => $player['plAccess'] == 'admin',
				'firstname' => $player['plFirstName'],
				'lastname' => $player['plSurname'],
				'session_hash' => $this->model_user_session->getHash()
			));

			return self::LOGGED_IN;
		}
	}

	/**
	 * Retrieves some basic details from the passed e-mail
	 * @param string $email
	 * @return array
	 */
	public function getBasicDetails($email) {
		$email = strtolower(trim($email));

		// Pull the whole user from the database, sort by login in case of duplicate registrations
		$user_query = $this->db->query("SELECT plPlayerID AS `user_id`, plFirstName AS `firstname`, plSurname AS `lastname` FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC");

		return $user_query->row;
	}

	/**
	 * Logs the user out
	 */
	public function logout() {
		if ($this->user->isLogged()) {
			$this->user->logout();
		}
	}

	/**
	 * Checks to see if the e-mail exists within the database
	 * @param string $email
	 * @return boolean
	 */
	public function emailExists($email) {
		$email = strtolower(trim($email));

		$user_query = $this->db->query("SELECT plEmail FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC");

		return $user_query->num_rows > 0;
	}

	/**
	 * Handles an incorrect log in attempt, sending out the automated e-mail
	 * @param integer $user_id
	 * @param integer $current_incorrect
	 * @param boolean $is_locked
	 * @return integer
	 */
	private function _registerIncorrect($user_id, $current_incorrect, $is_locked = false) {
		$lock_after = (int)$this->config->get('login_tries');

		// If lock is set to zero, then never lock
		if ($lock_after == 0) {
			return;
		}
		if ($current_incorrect <= $lock_after || $is_locked) {
			// Just record one more failed attempt
			$this->db->query("UPDATE " . DB_PREFIX . "players SET plLoginCounter = plLoginCounter + 1 WHERE plPlayerID = '" . (int)$user_id . "'");
			if ($is_locked) {
				return self::LOCKED;
			} else {
				return self::INCORRECT;
			}
		} elseif (!$is_locked) {
			// Lock the user and send out an automated e-mail
			// @todo automated e-mail
			$this->db->query("UPDATE " . DB_PREFIX . "players SET plLoginCounter = plLoginCounter + 1, plPassword = '" . $this->db->escape(self::LOCKED_USER) . "' WHERE plPlayerID = '" . (int)$user_id . "'");
			return self::JUST_LOCKED;
		}
	}

	private function _encryptPassword($password, $old_salt = false) {
		if (!$old_salt) {
			return sha1($password . $this->config->get('salt'));
		} else {
			return sha1($password . $this->config->get('old_salt'));
		}
	}
}