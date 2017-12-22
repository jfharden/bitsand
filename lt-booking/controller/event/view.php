<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/event/view.php
 ||    Summary: Event view, including booking list
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

namespace LTBooking\Controller;

use Bitsand\Controllers\Controller;

class EventView extends Controller {
	public function index($event_reference) {
		$this->load->model('event/event');

		$event = $this->model_event_event->getEvent($event_reference);

		if (!$event) {
			return $this->redirect($this->router->link('error/not_found', null, \Bitsand\SSL));
		}

		$this->document->setTitle('Event Details - ' . $event['event_name']);
		$this->document->addStyle('styles/feature-block.css');

		$this->data['name'] = $event['event_name'];
		$this->data['event_date'] = date($this->config->get('date_format'), $event['event_date']);
		$this->data['booking_closes'] = date($this->config->get('date_format'), $event['booking_closes']);
		$this->data['description'] = html_entity_decode($event['description']);
		$this->data['details'] = html_entity_decode($event['details']);
		$this->data['has_monsters'] = !!$event['allow_monster_booking'];

		$this->data['login_link'] = $this->router->link('user/login/login');
		$this->data['rss_link'] = $this->router->link('feed/booking-rss/rss', array('event'=>$event_reference));
		$this->data['icalendar_link'] = $this->router->link('event/view/icalendar', array('event'=>$event_reference));

		if (!$this->config->get('booking_list_if_logged_in') || $this->user->isLogged()) {
			$this->data['show_booking_list'] = true;
		} else {
			$this->data['show_booking_list'] = false;
		}
		$this->data['show_character_group'] = $this->config->get('list_groups_label');

		$this->data['bookings'] = array();
		$this->data['booking_count'] = array(
			'staff'   => 0,
			'player'  => 0,
			'monster' => 0
		);

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
				'character_name'     => $booking['character_name'],
				'character_nickname' => $booking['character_nickname'],
				'character_group'    => $group,
				'faction'            => $booking['faction'],
				'type'               => $booking['type'] == 'staff' ? $this->config->get('staff_label') : ucwords($booking['type']),
				'monster_only'       => $booking['monster_only']
			);

			$this->data['booking_count'][$booking['type']]++;
		}

		$this->data['booking_count']['total'] = array_sum($this->data['booking_count']);

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('event/view');

		$this->view->setOutput($this->render());
	}

	public function icalendar($event_reference) {
		$this->data['github_link'] = $this->config->getVal('git_repository');
		$this->data['site_name'] = $this->config->getVal('site_name');
		$this->data['contact_name'] = $this->config->getVal('event_contact');
		$this->data['contact_email'] = $this->config->getVal('event_contact_email');

		$this->load->model('event/event');

		$event = $this->model_event_event->getEvent($event_reference);
		if (!$event) {
			return $this->redirect($this->router->link('error/not_found', null, \Bitsand\SSL));
		}

		$this->data['events'] = array();
		$this->data['events'][] = array(
			'date' => date('Ymd', $event['event_date']),
			'booking_opens' => date('Ymd', $event['booking_opens']),
			'booking_closes' => date('Ymd', $event['booking_closes']),
			'name' => $event['event_name'],
			'link' => $this->router->link('event/view', array('event' => $event['slug'] ? $event['slug'] : $event['event_id']), \Bitsand\SSL),
			'booking_opens_link' => $this->router->link('event/view/booking', array('event' => $event['slug'] ? $event['slug'] : $event['event_id'], 'booking' => 'open'), \Bitsand\SSL),
			'booking_closes_link' => $this->router->link('event/view/booking', array('event' => $event['slug'] ? $event['slug'] : $event['event_id'], 'booking' => 'close'), \Bitsand\SSL)
		);

		$this->setView('event/icalendar');

		$this->view->setOutput($this->render());
	}
}