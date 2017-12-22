<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/event/list.php
 ||    Summary: The main listing for events
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

class EventList extends Controller {
	public function index() {
		$this->document->setTitle('Event List');

		$this->load->model('event/event');

		$this->data['icalendar_link'] = $this->router->link('event/list/icalendar');

		$this->data['bookings_open'] = array();
		foreach ($this->model_event_event->getEvents(true) as $event) {
			$this->data['bookings_open'][] = array(
				'date'              => date($this->config->get('date_format'), $event['event_date']),
				'booking_closes'    => date($this->config->get('date_format'), $event['booking_closes']),
				'name'              => $event['event_name'],
				'booked'            => !!$event['booked'],
				'in_queue'          => !!$event['in_queue'],
				'link'              => $this->router->link('event/view', array('event' => $event['event_id'])),
				'booking_link'      => $this->router->link('event/book', array('event' => $event['event_id'])),
				'view_booking_link' => $this->router->link('event/book/view', array('event' => $event['event_id']))
			);
		}

		$this->data['bookings_closed'] = array();
		foreach ($this->model_event_event->getEvents(false) as $event) {
			$this->data['bookings_closed'][] = array(
				'date' => date($this->config->get('date_format'), $event['event_date']),
				'name' => $event['event_name'],
				'link' => $this->router->link('event/view', array('event' => $event['slug'] ? $event['slug'] : $event['event_id']))
			);
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('event/list');

		$this->view->setOutput($this->render());
	}

	public function icalendar() {
		$this->data['github_link'] = $this->config->getVal('git_repository');
		$this->data['site_name'] = $this->config->getVal('site_name');
		$this->data['contact_name'] = $this->config->getVal('event_contact');
		$this->data['contact_email'] = $this->config->getVal('event_contact_email');

		$this->load->model('event/event');

		$this->data['events'] = array();
		foreach ($this->model_event_event->getAllEvents() as $event) {
			$this->data['events'][] = array(
				'date' => date('Ymd', $event['event_date']),
				'booking_opens' => date('Ymd', $event['booking_opens']),
				'booking_closes' => date('Ymd', $event['booking_closes']),
				'name' => $event['event_name'],
				'link' => $this->router->link('event/view', array('event' => $event['slug'] ? $event['slug'] : $event['event_id']), \Bitsand\SSL),
				'booking_opens_link' => $this->router->link('event/view/booking', array('event' => $event['slug'] ? $event['slug'] : $event['event_id'], 'booking' => 'open'), \Bitsand\SSL),
				'booking_closes_link' => $this->router->link('event/view/booking', array('event' => $event['slug'] ? $event['slug'] : $event['event_id'], 'booking' => 'close'), \Bitsand\SSL)
			);
		}

		$this->setView('event/icalendar');

		$this->view->setOutput($this->render());
	}
}