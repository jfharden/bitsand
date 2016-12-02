<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/controller/common/home.php
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

namespace Admin\Controller;
use Bitsand\Config\Config;
use Bitsand\Controllers\Controller;

class CommonHome extends Controller {
	public function index() {
		$this->document->setTitle('Admin');

		$this->children = array(
			'common/header',
			'common/footer'
		);

		// We have three event states: Current; Expired; Queued
		$this->load->model('events/event');
		$this->data['events_current'] = array();
		$this->data['events_expired'] = array();
		$this->data['events_queued'] = array();
		foreach ($this->model_events_event->getByType(\Admin\Model\EventsEvent::ALL) as $event) {
			$url_data = array('event_id' => (int)$event['evEventID']);
			$this->data['events_' . $event['evEventState']][] = array(
				'id'          => $event['evEventID'],
				'title'       => $event['evEventName'],
				'date'        => date(Config::getVal('short_date'), strtotime($event['evEventDate'])),
				'view'        => $this->router->link('event/view', $url_data, \Bitsand\SSL),
				'payments'    => $this->router->link('event/manage/payments', $url_data, \Bitsand\SSL),
				'queue'       => $this->router->link('event/manage/queue', $url_data, \Bitsand\SSL),
				'add_booking' => $this->router->link('event/manage/add_booking', $url_data, \Bitsand\SSL)
			);
		}

		$this->setView('common/home');

		$this->view->setOutput($this->render());
	}
}