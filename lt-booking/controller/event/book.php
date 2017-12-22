<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/event/book.php
 ||    Summary: Handles booking for events
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

class EventBook extends Controller {
	public function index($event_reference) {
		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('event/book', array('event'=>$event_reference), \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->load->model('event/event');
		$this->load->model('user/user');

		$event = $this->model_event_event->getEvent($event_reference);

		if (!$event) {
			return $this->redirect($this->router->link('error/not_found', null, \Bitsand\SSL));
		}

		// Cater for people who have saved the booking link
		if (!$event['booking_open']) {
			return $this->redirect($this->router->link('event/view', array('event'=>$event_reference)));
		}

		$this->document->setTitle('Event Booking - ' . $event['event_name']);

		$this->data['booking_link'] = $this->router->link('event/book', array('event'=>$event_reference));

		// Check the user has character
		$this->data['has_ic_profile'] = $this->model_user_user->hasCharacter($this->user->getId());
		// @todo Validate character doesn't have too many points

		// Check we have enough total spaces
		$this->data['has_spaces'] = $event['booked_total'] < $event['spaces_total'];

		$this->data['booking_options'] = array();

		foreach ($this->model_event_event->getBookingOptions($event['event_id']) as $type => $options) {
			$this->data['booking_options'][$type] = array(
				'type'    => $type,
				'name'    => ucwords(strtolower($type)),
				'options' => array()
			);
			foreach ($options as $option) {
				$this->data['booking_options'][$type]['options'][] = array(
					'item_id'        => $option['item_id'],
					'name'           => $option['description'],
					'cost'           => (float)$option['cost'],
					'cost_formatted' => $this->currency->format($option['cost']),
					'is_multiple'    => !!$option['is_multiple'],
					'mandatory'      => !!$option['mandatory']
				);
			}
		}

		// Remove monster option if not allows to book (i.e. backend allocated only)
		if (!$event['allow_monster_booking']) {
			unset($this->data['booking_options']['monster']);
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('event/book');

		$this->view->setOutput($this->render());
	}
}