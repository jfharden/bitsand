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

class UserSession extends Model {
	private $session_hash;

	/**
	 * Registers that a user has successfully logged on and records various
	 * bits of information within a table for each login success.
	 * @param integer $user_id
	 */
	public function register($user_id) {
		$this->session_hash = sha1(microtime() . $user_id . '-' . rand(1000,9999));

		// See if there is already an entry for the user
		$session_query = $this->db->query("SELECT ssPlayerID FROM " . DB_PREFIX . "sessions WHERE ssPlayerID = '" . (int)$user_id . "'");

		if ($session_query->num_rows == 0) {
			$sql = "
INSERT INTO " . DB_PREFIX . "sessions
  (ssPlayerId, ssLoginTime, ssIP, ssLastAccess)
  VALUES
  (
    '" . (int)$user_id . "',
    '" . $this->db->escape($this->session_hash) . "',
    '" . $this->db->escape($ip) . "',
    '" . time() . "'
  )
";
		} else {
			$sql = "
UPDATE " . DB_PREFIX . "sessions SET
  ssLoginTime = '" . $this->db->escape($this->session_hash) . "',
  ssIP = '" . $this->db->escape($ip) . "',
  ssLastAccess = '" . time() . "'
WHERE ssPlayerID = '" . (int)$user_id . "'
";
		}

		$this->db->query($sql);
	}

	/**
	 * Retrieves the unique hash for the current session
	 * @return string
	 */
	public function getHash() {
		if (isset($this->session->data['session_hash'])) {
			return $this->session->data['session_hash'];
		}
		return $this->session_hash;
	}
}