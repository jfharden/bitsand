<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/controller/events/view.php
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

namespace Admin\Controller;
use Bitsand\Config\Config;
use Bitsand\Controllers\Controller;

class EventsView extends Controller {
	public function index($event_id) {
		$this->load->model('events/event');
		$event_info = $this->model_events_event->getById($event_id);

		if ($event_info) {
			$this->document->setTitle('Manage Event - ' . $event_info['event_name']);
			$this->data['title'] = 'Manage Event - ' . $event_info['event_name'];

			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->data['payment_queue'] = $this->model_events_event->getPaymentQueueCount($event_id);

			$this->data['event_id'] = (int)$event_info['event_id'];
			$this->data['event_name'] = $event_info['event_name'];

			$this->data['link_edit'] = $this->router->link('events/event/edit', array('event_id'=>$event_id), \Bitsand\SSL);
			$this->data['link_queue'] = $this->router->link('events/booking/queue', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_payments'] = $this->router->link('events/payment/outstanding', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_booking_status'] = $this->router->link('events/booking/status', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_add_booking'] = $this->router->link('events/booking/add', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_bunks'] = $this->router->link('events/booking/bunks', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_meals'] = $this->router->link('events/booking/meals', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_marshals'] = $this->router->link('events/booking/marshals', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_cards'] = $this->router->link('events/report/cards', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_bookings'] = $this->router->link('events/report/bookings', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_purchases'] = $this->router->link('events/report/purchases', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_signin'] = $this->router->link('events/report/signin', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_medical'] = $this->router->link('events/report/medical', array('event_id'=>$event_id, \Bitsand\SSL));
			$this->data['link_diet'] = $this->router->link('events/report/diet', array('event_id'=>$event_id, \Bitsand\SSL));

			$this->setView('events/view');

			$this->view->setOutput($this->render());
		} else {
			$this->session->data['error'] = 'Unable to find event with ID of ' . var_export($event_id, true);

			$this->redirect($this->router->link('common/home', null, \Bitsand\SSL));
		}
	}
}