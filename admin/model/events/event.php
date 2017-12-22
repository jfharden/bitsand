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
				evEventID                AS `event_id`,
				evEventName              AS `event_name`,
				evEventDate              AS `event_date`,
				CASE
					WHEN evBookingsOpen <= CURDATE() AND evBookingsClose >= CURDATE() THEN 'current'
					WHEN evBookingsClose < CURDATE() THEN 'expired'
					WHEN evBookingsOpen > CURDATE() THEN 'queued'
				END                      AS `event_state`,
				(SELECT COUNT(*) FROM " . DB_PREFIX . "bookings WHERE bkEventID = e.evEventID AND (bkDatePaymentConfirmed IS NULL OR bkDatePaymentConfirmed = '0000-00-00'))
				                         AS `payment_queue`,
				(SELECT COUNT(*) FROM " . DB_PREFIX . "bookings WHERE bkEventID = e.evEventID AND bkInQueue = '1' AND bkDatePaymentConfirmed IS NOT NULL AND bkDatePaymentConfirmed <> '0000-00-00')
				                         AS `booking_queue`

			FROM " . DB_PREFIX . "events e

			";

		if ($type == self::CURRENT) {
			$sql .= "WHERE evBookingsOpen <= CURDATE() AND evBookingsClose >= CURDATE()";
		} elseif ($type == self::EXPIRED) {
			$sql .= "WHERE evBookingsClose < CURDATE()";
		} elseif ($type == self::QUEUED) {
			$sql .= "WHERE evBookingsOpen > CURDATE()";
		}

		$sql .= "\n\n\t\t\tGROUP BY evEventID";

		$sql .= "\n\n\t\t\tORDER BY evEventDate DESC";
//echo '<pre><code>',$sql;die;
		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Retrieves an event from it's Id
	 * @param  integer $event_id
	 * @return array
	 */
	public function getById($event_id) {
		$query = $this->db->query("
			SELECT
				evEventID              AS `event_id`,
				evEventName            AS `event_name`,
				evSlug                 AS `slug`,
				evEventDescription     AS `description`,
				evEventDetails         AS `details`,
				evEventDate            AS `event_date`,
				evBookingsOpen         AS `booking_open`,
				evBookingsClose        AS `booking_close`,
				evPlayerSpaces         AS `spaces_player`,
				evMonsterSpaces        AS `spaces_monster`,
				evStaffSpaces          AS `spaces_staff`,
				evTotalSpaces          AS `spaces_total`,
				evPlayerBunks          AS `bunks_player`,
				evMonsterBunks         AS `bunks_monster`,
				evStaffBunks           AS `bunks_staff`,
				evTotalBunks           AS `bunks_total`,
				evAllowMonsterBookings AS `has_monster`,
				evUseQueue             AS `has_queue`

			FROM " . DB_PREFIX . "events

			WHERE evEventID = '" . (int)$event_id . "'");

		if ($query->num_rows) {
			$query_items = $this->db->query("
				SELECT
					itItemID           AS `item_id`,
					itDescription      AS `description`,
					itTicket           AS `ticket`,
					itMeal             AS `meal`,
					itBunk             AS `bunk`,
					itAvailableFrom    AS `from`,
					itAvailableTo      AS `to`,
					itAvailability     AS `availability`,
					itItemCost         AS `cost`,
					itAllowMultiple    AS `multiple`,
					itMandatory        AS `mandatory`

				FROM " . DB_PREFIX . "items

				WHERE itEventID = '" . (int)$event_id . "'");

			$query->row['items'] = $query_items->rows;

			return $query->row;
		} else {
			return false;
		}
	}

	/**
	 * Updates or inserts an event with it's corresponding items.  Items are
	 * incrementally updated
	 *
	 * @param  int $event_id If null then performs an insert
	 * @param  array $data
	 * @return int The event_id
	 */
	public function editEvent($event_id, $data) {
		if (is_null($event_id)) {
			$sql = "INSERT INTO ";
		} else {
			$sql = "UPDATE ";
		}

		$changes = 0;

		$sql .= DB_PREFIX . "events SET
			evEventName = '" . $this->db->escape($data['event_name']) . "',
			evEventDescription = '" . $this->db->escape($data['description']) . "',
			evEventDetails = '" . $this->db->escape($data['details']) . "',
			evEventDate = '" . $this->db->escape($data['event_date']) . "',
			evBookingsOpen = '" . $this->db->escape($data['booking_open']) . "',
			evBookingsClose = '" . $this->db->escape($data['booking_close']) . "',
			evPlayerSpaces = '" . (int)$data['spaces_player'] . "',
			evMonsterSpaces = '" . (int)$data['spaces_monster'] . "',
			evStaffSpaces = '" . (int)$data['spaces_staff'] . "',
			evTotalSpaces = '" . (int)$data['spaces_total'] . "',
			evPlayerBunks = '" . (int)$data['bunks_player'] . "',
			evMonsterBunks = '" . (int)$data['bunks_monster'] . "',
			evStaffBunks = '" . (int)$data['bunks_staff'] . "',
			evTotalBunks = '" . (int)$data['bunks_total'] . "',
			evAllowMonsterBookings = '" . (empty($data['has_monster']) ? '0' : '1') . "',
			evUseQueue = '" . (empty($data['has_queue']) ? '0' : '1') . "'
			";

		if (!is_null($event_id)) {
			$sql .= "WHERE evEventID = '" . (int)$event_id . "'";
		}

		$this->db->query($sql);
		$changes += $this->db->countAffected();

		if (is_null($event_id)) {
			$event_id = $this->db->lastInsertId();
			$current_items = array();
		} else {
			$items_query = $this->db->query("SELECT itItemID AS `item_id` FROM " . DB_PREFIX . "items WHERE itEventID = '" . (int)$event_id . "'");

			$current_items = array_flip(array_column($items_query->rows, 'item_id'));
		}

		if (isset($data['item'])) {
			foreach ($data['item'] as $item_idx => $item) {
				if (strpos($item_idx, 'new') !== false) {
					$item_idx = null;
					$sql = "INSERT INTO ";
				} else {
					$sql = "UPDATE ";
				}

				$sql .= DB_PREFIX . "items SET
				itDescription = '" . $item['description'] . "',
				itTicket = '" . (empty($item['ticket']) ? '0' : '1') . "',
				itMeal = '" . (empty($item['meal']) ? '0' : '1') . "',
				itBunk = '" . (empty($item['bunk']) ? '0' : '1') . "',
				itAvailableFrom = '" . $this->db->escape($item['from']) . "',
				itAvailableTo = '" . $this->db->escape($item['to']) . "',
				itAvailability = '" . $this->db->escape($item['availability']) . "',
				itItemCost = '" . (float)$item['cost'] . "',
				itAllowMultiple = '" . (empty($item['multiple']) ? '0' : '1') . "',
				itEventID = '" . (int)$event_id . "'
				";

				if ($item_idx) {
					$sql .= "WHERE itItemID = '" . (int)$item_idx . "'";
					unset($current_items[$item_idx]);
				}

				$this->db->query($sql);
				$changes += $this->db->countAffected();
			}
		}

		if ($current_items) {
			foreach (array_flip($current_items) as $item_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "items WHERE itItemID = '" . (int)$item_id . "'");
				$changes += $this->db->countAffected();
			}
		}

		if ($changes) {
			return $event_id;
		} else {
			return false;
		}
	}

	/**
	 * Returns the number of bookings awaiting confirmation of being paid
	 *
	 * @param  int $event_id [description]
	 * @return int
	 */
	public function getPaymentQueueCount($event_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `queue_count` FROM " . DB_PREFIX . "bookings WHERE bkEventID = '" . (int)$event_id . "' AND (bkDatePaymentConfirmed IS NULL OR bkDatePaymentConfirmed = '0000-00-00')");

		return $query->row['queue_count'];
	}

	public function getBookingQueueCount($event_id) {
		$sql = "
		SELECT COUNT(*) AS `queue_count`

		FROM " . DB_PREFIX . "bookings

		WHERE
			bkEventID = '" . (int)$event_id . "',
			bkInQueue = '1'";

		$query = $this->db->query($sql);

		return $query->row['queue_count'];
	}
}