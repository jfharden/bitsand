<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/character/occupational_skills.php
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2017 The Bitsand Project
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

class CharacterOSPs extends Model {
	/**
	 * Retrieves all OSPs available
	 * @return array
	 */
	public function getOSPs() {
		$osp_query = $this->db->query("
			SELECT
			  ospID AS `osp_id`,
			  ospName AS `name`,
			  TRIM(LOWER(REPLACE(ospShortName, ' ', '_'))) AS `short_name`,
			  ospAllowAdditionalText AS `has_other`
			FROM " . DB_PREFIX . "osps");

		return $osp_query->rows;
	}

	/**
	 * Retrieves the OSPs for a specific user
	 * @param  integer $user_id
	 * @return array
	 */
	public function getUserOSPs($user_id) {
		$osp_query = $this->db->query("
			SELECT
			  otOspID AS `osp_id`,
			  ospName AS `name`,
			  TRIM(LOWER(REPLACE(ospShortName, ' ', '_'))) AS `short_name`,
			  ospAllowAdditionalText AS `has_other`,
			  TRIM(otAdditionalText) AS `osp_other`
			FROM " . DB_PREFIX . "ospstaken ot LEFT JOIN " . DB_PREFIX . "osps o ON otOspID = ospID
			WHERE otPlayerID = '" . (int)$user_id . "'");

		return $osp_query->rows;
	}

	/**
	 * Performs a comparative update of a users OSPs and returns the number of changes made
	 * @param  int $user_id
	 * @param  array $data
	 * @return int
	 */
	public function updateUser($user_id, $data) {
		$changed = 0;

		$current_osps = array();
		foreach ($this->getUserOSPs($user_id) as $osp) {
			if (!$osp['osp_other']) {
				$current_osps[$osp['osp_id']] = $osp['short_name'];
			} else {
				if (!isset($current_osps[$osp['osp_id']])) {
					$current_osps[$osp['osp_id']] = array();
				}
				$current_osps[$osp['osp_id']][] = strtolower($osp['osp_other']);
			}
		}

		foreach ($data as $osp_id => $osp) {
			$sql = '';
			if (!is_array($osp)) {
				if (!isset($current_osps[$osp_id])) {
					// Insert
					$sql = "INSERT INTO " . DB_PREFIX . "ospstaken (otPlayerID, otOspID, otAdditionalText) VALUES ('" . (int)$user_id . "', '" . (int)$osp_id . "', NULL)";
				} else {
					// No need to do anything
					unset($current_osps[$osp_id]);
				}
			} else {
				foreach ($osp as $osp_other) {
					// An OSP that requires editable text
					if (!isset($current_osps[$osp_id]) || !in_array(strtolower($osp_other), $current_osps[$osp_id])) {
						// Insert
						$sql = "INSERT INTO " . DB_PREFIX . "ospstaken (otPlayerID, otOspID, otAdditionalText) VALUES ('" . (int)$user_id . "', '" . (int)$osp_id . "', '" . $this->db->escape($osp_other) . "')";
					} else {
						// No need to do anything
						$idx = array_search(strtolower($osp_other), $current_osps[$osp_id]);
						unset($current_osps[$osp_id][$idx]);
						if (empty($current_osps[$osp_id])) {
							unset($current_osps[$osp_id]);
						}
					}
				}
			}
			if ($sql) {
				$this->db->query($sql);
				$changed += $this->db->countAffected();
			}
		}

		if (!empty($current_osps)) {

			foreach ($current_osps as $osp_id => $osp) {
				$sql = "DELETE FROM " . DB_PREFIX . "ospstaken WHERE otPlayerID = '" . (int)$user_id . "' AND otOspID = '" . (int)$osp_id . "'";
				if (is_array($osp)) {
					foreach ($osp as &$os) {
						$os = "'" . $this->db->escape($os) . "'";
					}
					$sql .= " AND (TRIM(LOWER(otAdditionalText)) IN (" . implode(',', $osp) . "))";
				}

				$this->db->query($sql);
				$changed += $this->db->countAffected();
			}
		}

		return $changed;
	}
}