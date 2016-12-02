<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/model/event.php
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2016 The Bitsand Project
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

namespace Admin\Model;

use Bitsand\Controllers\Model;

class EventsEvent extends Model {
	const CURRENT = 'event_current';
	const EXPIRED = 'event_expired';
	const QUEUED = 'event_queued';
	const ALL = 'event_all';

	public function getByType($type) {
		$sql = "
			SELECT
				evEventID,
				evEventName,
				evEventDate,
				CASE
					WHEN evBookingsOpen <= CURDATE() AND evBookingsClose >= CURDATE() THEN 'current'
					WHEN evBookingsClose < CURDATE() THEN 'expired'
					WHEN evBookingsOpen > CURDATE() THEN 'queued'
				END AS evEventState

			FROM " . DB_PREFIX . "events";

		if ($type == self::CURRENT) {
			$sql .= " WHERE evBookingsOpen <= CURDATE() AND evBookingsClose >= CURDATE()";
		} elseif ($type == self::EXPIRED) {
			$sql .= " WHERE evBookingsClose < CURDATE()";
		} elseif ($type == self::QUEUED) {
			$sql .= " WHERE evBookingsOpen > CURDATE()";
		}
		$sql .= " ORDER BY evEventDate DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}
}