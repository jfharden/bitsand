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
		$user_query = $this->db->query("SELECT plPlayerID, plPassword, plOldSalt, plLoginCounter, plAccess, plFirstName, plSurname FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC");

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
				'is_admin' => $user['plAccess'] == 'admin',
				'firstname' => $user['plFirstName'],
				'lastname' => $user['plSurname'],
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
	public function getBasicDetails($email, $include_reset_token = false) {
		$email = strtolower(trim($email));

		if (!$include_reset_token) {
			$sql = "SELECT plPlayerID AS `user_id`, plFirstName AS `firstname`, plSurname AS `lastname` FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC";
		} else {
			/*
			 * The reset token is used to allow the user the option to reset their
			 * password.  Although a purely random token is preferable, we want to
			 * keep as true to the v8.x Bitsand database format so we composite it
			 * together from the password, last login and user.  This will be
			 * unique to a user and automatically expires once the password has
			 * been changed or the user remembered it.
			 */
			$sql = "SELECT plPlayerID AS `user_id`, plFirstName AS `firstname`, plSurname AS `lastname`, MD5(CONCAT(plPlayerID, plPassword, plLastLogin)) AS `reset_token` FROM " . DB_PREFIX . "players WHERE LOWER(plEmail) = '" . $this->db->escape($email) . "' ORDER BY plLastLogin DESC";
		}

		$user_query = $this->db->query($sql);

		return $user_query->row;
	}

	/**
	 * Retrieves the automated e-mail preferences for the requested user.
	 *
	 * @param integer $user_id
	 * @return array
	 */
	public function getMailingDetails($user_id) {
		$user_query = $this->db->query("SELECT plEmailICChange AS `ic`, plEmailOOCChange AS `ooc`, plEmailPaymentReceived AS `payment`, plEmailRemovedFromQueue AS `queue` FROM " . DB_PREFIX . "players WHERE plPlayerID = '" . (int)$user_id . "'");
		$details = array(
			'ooc'     => $user_query->row['ooc'] == 1,
			'ic'      => $user_query->row['ic'] == 1,
			'payment' => $user_query->row['payment'] == 1,
			'queue'   => $user_query->row['queue'] == 1
		);
		return $details;
	}

	/**
	 * Returns all of the personal details for the userl.  Bitand uses AES
	 * encryption on most of the personal details, using a generic encryption
	 * key.  Long term goal should be to improve this
	 *
	 * @param integer $user_id
	 * @return array
	 */
	public function getPersonalDetails($user_id) {
		$encryption_key = $this->config->get('encryption_key');

		$user_query = $this->db->query("
			SELECT
			  AES_DECRYPT(pleAddress1, '{$encryption_key}') AS `address_1`,
			  AES_DECRYPT(pleAddress2, '{$encryption_key}') AS `address_2`,
			  AES_DECRYPT(pleAddress3, '{$encryption_key}') AS `address_3`,
			  AES_DECRYPT(pleAddress4, '{$encryption_key}') AS `address_4`,
			  AES_DECRYPT(plePostcode, '{$encryption_key}') AS `postcode`,
			  AES_DECRYPT(pleTelephone, '{$encryption_key}') AS `telephone`,
			  AES_DECRYPT(pleMobile, '{$encryption_key}') AS `mobile`,
			  AES_DECRYPT(pleMedicalInfo, '{$encryption_key}') AS `medical`,
			  AES_DECRYPT(pleEmergencyNumber, '{$encryption_key}') AS `emergency_number`,
			  plFirstName AS `firstname`,
			  plSurname AS `lastname`,
			  plDOB AS `dob`,
			  plEmergencyName AS `emergency_contact`,
			  plEmergencyRelationship AS `emergency_relation`,
			  plCarRegistration AS `car_registration`,
			  plDietary AS `dietary`,
			  plNotes AS `notes`,
			  plMarshal AS `marshal`,
			  plRefNumber AS `marshal_number`
			FROM " . DB_PREFIX . "players WHERE plPlayerID = '" . (int)$user_id . "'");

		/*
		 * The dob field is configured to be a plain text field so convert it
		 * into a traditional SQL timestamp here.  This needs to be modified at
		 * some point.
		 * @todo Convert the date of birth field into a DATE type
		 */
		if (isset($user_query->row['dob'])) {
			$dob = $user_query->row['dob'];
			$user_query->row['dob'] = substr($dob, 0, 4) . '-' . substr($dob, 4, 2) . '-' . substr($dob, 6, 2);
		}
		return $user_query->row;
	}

	/**
	 * Retrieves the user id from the passed token
	 * @param string $token
	 * @return array
	 */
	public function getUserByToken($token) {
		$user_query = $this->db->query("SELECT plPlayerID as `user_id` FROM " . DB_PREFIX . "players WHERE MD5(CONCAT(plPlayerID, plPassword, plLastLogin)) = '" . $this->db->escape($token) . "'");

		return $user_query->row;
	}

	/**
	 * Formats the player id based on the necessary setting.
	 *
	 * @param integer $player_id
	 * @return string
	 */
	public function playerId($player_id) {
		if (!($format = $this->config->get('player_id_format'))) {
			$format = '%03s';
		}
		$format = $this->config->get('player_id_prefix') . $format;
		return sprintf($format, $player_id);
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
	 * Changes the password for the passed user to a new one
	 *
	 * @param integer $user_id
	 * @param string $new_password
	 * @return integer
	 * @todo Send an e-mail to the user to inform them a new password has been
	 * set
	 */
	public function changePassword($user_id, $new_password) {
		$encrypted_password = $this->_encryptPassword($new_password, false);

		$user_query = $this->db->query("UPDATE " . DB_PREFIX . "players SET plPassword = '" . $this->db->escape($encrypted_password) . "', plOldSalt = '0', plLoginCounter = '0' WHERE plPlayerID = '" . (int)$user_id . "'");

		$rows_changed = $this->db->countAffected();

		/*
		 * If rows_changed is zero, then either something went wrong, or the
		 * user is changing the password to the currently set password. Either
		 * way we need to validate this.
		 */
		if ($rows_changed == 0) {
			$verify_query = $this->db->query("SELECT COUNT(plPlayerID) AS `rows_affected` FROM " . DB_PREFIX . "players WHERE plPlayerID = '" . (int)$user_id . "' AND plPassword = '" . $this->db->escape($encrypted_password) . "'");
			$rows_changed = (int)$verify_query->row['rows_affected'];
		}

		return $rows_changed;
	}

	/**
	 * Updates the users e-mail preferences to the passed ones
	 *
	 * @param integer $user_id
	 * @param array $data
	 */
	public function changeMailing($user_id, $data) {
		$sql = "UPDATE " . DB_PREFIX . "players SET plEmailOOCChange = '" . (int)$data['ooc'] . "', plEmailICChange = '" . (int)$data['ic'] . "', plEmailPaymentReceived = '" . (int)$data['payment'] . "', plEmailRemovedFromQueue = '" . (int)$data['queue'] . "' WHERE plPlayerID = '" . (int)$user_id . "'";
		$this->db->query($sql);
	}

	/**
	 * Updates the users personal details to the passed one.  Returns if the
	 * user needs to receive an email or not
	 *
	 * @param integer $user_id
	 * @param array $data
	 * @return boolean Indicates that the user needs to be e-mailed
	 */
	public function changePersonalDetails($user_id, $data) {
		$encryption_key = $this->config->get('encryption_key');

		$sql = "UPDATE " . DB_PREFIX . "players SET
			  pleAddress1 = AES_ENCRYPT('" . $this->db->escape($data['address_1']) . "', '{$encryption_key}'),
			  pleAddress2 = AES_ENCRYPT('" . $this->db->escape($data['address_2']) . "', '{$encryption_key}'),
			  pleAddress3 = AES_ENCRYPT('" . $this->db->escape($data['address_3']) . "', '{$encryption_key}'),
			  pleAddress4 = AES_ENCRYPT('" . $this->db->escape($data['address_4']) . "', '{$encryption_key}'),
			  plePostcode = AES_ENCRYPT('" . $this->db->escape($data['postcode']) . "', '{$encryption_key}'),
			  pleTelephone = AES_ENCRYPT('" . $this->db->escape($data['telephone']) . "', '{$encryption_key}'),
			  pleMobile = AES_ENCRYPT('" . $this->db->escape($data['mobile']) . "', '{$encryption_key}'),
			  pleMedicalInfo = AES_ENCRYPT('" . $this->db->escape($data['medical']) . "', '{$encryption_key}'),
			  pleEmergencyNumber = AES_ENCRYPT('" . $this->db->escape($data['emergency_number']) . "', '{$encryption_key}'),
			  plFirstName = '" . $this->db->escape($data['firstname']) . "',
			  plSurname = '" . $this->db->escape($data['lastname']) . "',
			  plDOB = '" . $this->db->escape($data['dob']['y'] . $data['dob']['m'] . $data['dob']['d']) . "',
			  plEmergencyName = '" . $this->db->escape($data['emergency_contact']) . "',
			  plEmergencyRelationship = '" . $this->db->escape($data['emergency_relation']) . "',
			  plCarRegistration = '" . $this->db->escape($data['car_registration']) . "',
			  plDietary = '" . $this->db->escape($data['diet']) . "',
			  plNotes = '" . $this->db->escape($data['notes']) . "',
			  plMarshal = '" . $this->db->escape($data['marshal']) . "',
			  plRefNumber = '" . $this->db->escape($data['marshal_number']) . "'
			WHERE plPlayerID = '" . (int)$user_id . "'";
		$this->db->query($sql);

		// Now see if the user needs an e-mail
		if ($this->db->countAffected() > 0) {
			$user_query = $this->db->query("SELECT plEmail, plEmailOOCChange FROM " . DB_PREFIX . "players WHERE plPlayerID = '" . (int)$user_id . "'");
			if ((int)$user_query->row['plEmailOOCChange'] == 1) {
				return $user_query->row['plEmail'];
			}
		} else {
			// Nothing has been updated so never send an e-mail
		}

		return false;
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