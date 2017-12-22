<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/character/guild.php
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

class CharacterGuild extends Model {
	/**
	 * Returns all of the registered guilds
	 *
	 * @return array
	 */
	public function getAll() {
		// Note, we exclude the "none" group as this is added within the view
		$query = $this->db->query("
			SELECT
			  guID AS `guild_id`,
			  guName AS `guild_name`
			FROM " . DB_PREFIX . "guilds
			WHERE guName <> 'None' && guName <> ' None'
			ORDER BY guild_name");

		return $query->rows;
	}

	/**
	 * Retrieves the passed users guilds
	 * @param  integer $user_id [description]
	 * @return array
	 */
	public function getUserGuilds($user_id, $exclude_old = false) {
		$query = $this->db->query("
			SELECT gmID AS `guild_id`
			FROM " . DB_PREFIX . "guildmembers
			WHERE gmPlayerID = '" . (int)$user_id . "'" . ($exclude_old ? ' AND TRUE = FALSE' : '') . "

			UNION

			SELECT cgGuildId AS `guild_id`
			FROM " . DB_PREFIX . "character_guilds
			WHERE cgPlayerID = '" . (int)$user_id . "'");

		return array_column($query->rows, 'guild_id');
	}

	/**
	 * Performs a comparative update of a users OSPs and returns the number of changes made
	 * @param  int $user_id
	 * @param  array $data
	 * @return int
	 */
	public function updateUser($user_id, $data) {
		$changed = 0;

		$current_guilds = $this->getUserGuilds($user_id, true);

		foreach ($data as $guild_id) {
			if (!in_array($guild_id, $current_guilds)) {
				$this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "character_guilds (cgPlayerID, cgGuildId) VALUES ('" . (int)$user_id . "', '" . (int)$guild_id . "')");
				$changed += $this->db->countAffected();
			} else {
				$idx = array_search($guild_id, $current_guilds);
				unset($current_guilds[$idx]);
			}
		}

		if (!empty($current_guilds)) {
			foreach ($current_guilds as $guild_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "character_guilds WHERE cgPlayerID = '" . (int)$user_id . "' AND cgGuildId = '" . (int)$guild_id . "'");
				$changed += $this->db->countAffected();
			}
		}

		// Tidy up the old table
		$this->db->query("DELETE FROM " . DB_PREFIX . "guildmembers WHERE gmPlayerID = '" . (int)$user_id . "'"); // Get rid of old guilds

		return $changed;
	}

}