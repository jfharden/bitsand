<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/event/event.php
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

class EventEvent extends Model {
	/**
	 * Retrieves events that have booking open or closed status
	 *
	 * @param boolean $booking_open
	 * @return array
	 */
	public function getEvents($booking_open) {
		return $this->_getEvents(array(
			'booking_state' => $booking_open ? 'open' : 'closed'
		));
	}

	/**
	 * Retrieves all events, active or not
	 *
	 * @return array
	 */
	public function getAllEvents() {
		return $this->_getEvents();
	}

	/**
	 * Internal event retrieval method
	 *
	 * @param array $data
	 * @return array
	 */
	private function _getEvents($data=array()) {
		$sql = "
			SELECT
			  evEventID                         AS `event_id`,
			  evEventName                       AS `event_name`,
			  evSlug                            AS `slug`,
			  UNIX_TIMESTAMP(evEventDate)       AS `event_date`,
			  UNIX_TIMESTAMP(evBookingsOpen)    AS `booking_opens`,
			  UNIX_TIMESTAMP(evBookingsClose)   AS `booking_closes`,
			  IF (bkPlayerID IS NULL, '0', '1') AS `booked`,
			  bkInQueue                         AS `in_queue`
			FROM " . DB_PREFIX . "events ev
			  LEFT JOIN " . DB_PREFIX . "bookings bk ON ev.evEventID = bk.bkEventID AND bk.bkPlayerID = '" . (int)$this->user->getId() . "'
			WHERE ";

		if (isset($data['booking_state'])) {
			if ($data['booking_state'] == 'open') {
				$sql .= "evBookingsOpen <= CURRENT_DATE() AND evBookingsClose >= CURRENT_DATE";
			} else {
				$sql .= "evBookingsClose < CURRENT_DATE()";
			}
		} else {
			// Only ever show events that have booking open - otherwise treat as "hidden"
			$sql .= "evBookingsOpen <= CURRENT_DATE()";
		}

		$sql .= "
			ORDER BY evEventDate DESC";

		$event_query = $this->db->query($sql);

		return $event_query->rows;
	}

	/**
	 * Gets a specific event from either it's slug or event_id
	 *
	 * @param string|integer $event_reference
	 * @return array
	 */
	public function getEvent($event_reference) {
		// Prevent us pulling event 5 from "5dfsfkljds"
		if (is_numeric($event_reference)) {
			$event_id = (int)$event_reference;
		} else {
			$event_id = 0;
		}
		// We check if booking is open here in case the database has a
		// different timezone setting
		$sql = "
			SELECT
			  evEventID                                  AS `event_id`,
			  evEventName                                AS `event_name`,
			  evSlug                                     AS `slug`,
			  UNIX_TIMESTAMP(evBookingsOpen)             AS `booking_opens`,
			  UNIX_TIMESTAMP(evEventDate)                AS `event_date`,
			  UNIX_TIMESTAMP(evBookingsClose)            AS `booking_closes`,
			  evEventDescription                         AS `description`,
			  evEventDetails                             AS `details`,
			  evPlayerSpaces                             AS `spaces_player`,
			  SUM(IF(LOWER(bkBookAs) = 'player', 1, 0))  AS `booked_player`,
			  evMonsterSpaces                            AS `spaces_monster`,
			  SUM(IF(LOWER(bkBookAs) = 'monster',1, 0))  AS `booked_monster`,
			  evStaffSpaces                              AS `spaces_staff`,
			  SUM(IF(LOWER(bkBookAs) = 'staff', 1, 0))   AS `booked_staff`,
			  evTotalSpaces                              AS `spaces_total`,
			  COUNT(bkEventID)                           AS `booked_total`,
			  evAllowMonsterBookings                     AS `allow_monster_booking`,
			  IF(evBookingsOpen <= CURRENT_DATE() AND evBookingsClose >= CURRENT_DATE, '1', '0')
			                                             AS `booking_open`
			FROM " . DB_PREFIX . "events ev
			  LEFT JOIN " . DB_PREFIX . "bookings bk ON ev.evEventID = bk.bkEventID

			WHERE evSlug = '" . $this->db->escape($event_reference) . "' OR evEventID = '" . $event_id . "'

			GROUP BY ev.evEventID";

		$event_query = $this->db->query($sql);

		return $event_query->row;
	}

	/**
	 * Retrieves bookings that have been confirmed as paid.
	 *
	 * @param integer $event_id
	 * @return array
	 */
	public function getBookings($event_id) {
		// Note - monster only field wasn't coded in v8
		$sql = "
			SELECT
			  bkID                                         AS `booking_id`,
			  plPlayerID                                   AS `player_id`,
			  plFirstName                                  AS `firstname`,
			  plSurname                                    AS `lastname`,
			  plPlayerNumber                               AS `player_number`,
			  LOWER(bkBookAs)                              AS `type`,
			  chName                                       AS `character_name`,
			  chPreferredName                              AS `character_nickname`,
			  chGroupSel                                   AS `character_group`,
			  chGroupText                                  AS `character_group_other`,
			  chFaction                                    AS `faction`,
			  IFNULL(chMonsterOnly, bkBookAs = 'Monster')  AS `monster_only`,
			  UNIX_TIMESTAMP(bkDatePaymentConfirmed)       AS `payment_confirmed`

			FROM " . DB_PREFIX . "bookings bk
			  JOIN " . DB_PREFIX . "players pl ON pl.plPlayerID = bk.bkPlayerID
			    LEFT JOIN " . DB_PREFIX . "characters ch ON pl.plPlayerID = ch.chPlayerID

			WHERE
			  bkEventID = '" . (int)$event_id . "'
			  AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed IS NOT NULL

			ORDER BY plSurname, plFirstName";

		$booking_query = $this->db->query($sql);

		return $booking_query->rows;
	}

	/**
	 * Retrieves the bookable options for each of the bookable types (monster,
	 * player, staff)
	 *
	 * @param integer $event_id
	 * @return array
	 */
	public function getBookingOptions($event_id) {
		/*
		 * Notes -
		 * meal and bunk marks the item so that it will appear on the
		 * allocation pages for the event
		 *
		 * allow multiple provides a drop down box, not entirely sure the
		 * circumstances where you would use this.
		 *
		 * mandatory is "automatically selected"
		 */
		$sql = "
			SELECT
			  itItemID                        AS `item_id`,
			  itDescription                   AS `description`,
			  itTicket                        AS `ticket`,
			  itMeal                          AS `has_meal`,
			  itBunk                          AS `has_bunk`,
			  UNIX_TIMESTAMP(itAvailableFrom) AS `available_from`,
			  UNIX_TIMESTAMP(itAvailableTo)   AS `available_to`,
			  LOWER(itAvailability)           AS `type`,
			  itItemCost                      AS `cost`,
			  itAllowMultiple                 AS `is_multiple`,
			  itMandatory                     AS `mandatory`

			FROM " . DB_PREFIX . "items

			WHERE
			  itEventID = '" . (int)$event_id . "'
			  AND itAvailableFrom <= CURRENT_DATE()
			  AND itAvailableTo >= CURRENT_DATE()

			ORDER BY itAvailability";

		$items_query = $this->db->query($sql);

		$items = array('player'=>array(), 'monster'=>array(), 'staff'=>array());
		foreach ($items_query->rows as $item) {
			$items[$item['type']][$item['item_id']] = $item;
		}


		return $items;
	}
}