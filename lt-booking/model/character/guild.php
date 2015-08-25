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

}