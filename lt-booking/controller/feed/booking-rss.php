<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/feed/booking-rss.php
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

namespace LTBooking\Controller;

use Bitsand\Controllers\Controller;

class FeedBookingRss extends Controller {
	public function rss($event_reference) {
		error_reporting(E_ALL);

		// Turn off Tracy debugging
		\Bitsand\Utilities\Tracy::disable();

		$this->load->model('event/event');

		$event = $this->model_event_event->getEvent($event_reference);
		if (!$event) {
			return $this->redirect($this->router->link('error/not_found', null, \Bitsand\SSL));
		}

		if ($this->config->get('booking_list_if_logged_in') && $this->user->isLogged()) {
			return $this->redirect($this->router->link('error/denied', null, \Bitsand\SSL));
		}

		$this->data['event_name'] = $event['event_name'];
		$this->data['rss_link'] = $this->router->link('feed/booking-rss/rss', array('event'=>$event_reference), \Bitsand\SSL);
		$this->data['system_url'] = $this->router->link('', null, \Bitsand\SSL);
		$this->data['booking_count'] = array();

		$this->data['show_character_group'] = $this->config->get('list_groups_label');

		foreach ($this->model_event_event->getBookings($event['event_id']) as $booking) {
			if ($booking['character_group']) {
				$group = $booking['character_group'];
			} elseif ($booking['character_group_other'] && $booking['character_group_other'] != 'Enter name here if not in above list') {
				$group = 'Other (' . $booking['character_group_other'] . ')';
			} else {
				$group = '';
			}
			$this->data['bookings'][] = array(
				'firstname'          => $booking['firstname'],
				'lastname'           => $booking['lastname'],
				'player_number'      => $booking['player_number'],
				'character_name'     => $booking['character_name'],
				'character_nickname' => $booking['character_nickname'],
				'character_group'    => $group,
				'faction'            => $booking['faction'],
				'guid'               => $this->router->link('event/view/booking', array('event'=>$event['event_id'], 'booking'=>$booking['booking_id']), \Bitsand\SSL)
			);

			if (!isset($this->data['booking_count'][$booking['type']])) {
				$this->data['booking_count'][$booking['type']] = 0;
			}
			$this->data['booking_count'][$booking['type']]++;
		}

		$this->setView('event/rss');

		$this->view->setOutput($this->render());
	}

}