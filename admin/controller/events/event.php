<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/controller/events/event.php
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

class EventsEvent extends Controller {
	private $_site_directory = '';
	private $_site_theme = '';

	public function create() {
		// Edit routine handles everything if passed no event_id
		$this->edit(null);
	}

	public function edit($event_id) {
		if (is_null($event_id)) {
			$this->getForm();
		} else {
			$this->load->model('events/event');
			$event_info = $this->model_events_event->getById($event_id);

			if ($event_info) {
				if ($this->request->method() == 'POST') {
					$this->save($event_id, $this->request->post);
				} else {
					$this->getForm($event_info);
				}
			} else {
				$this->session->data['error'] = 'Unable to find event with ID of ' . var_export($event_id, true);

				$this->redirect($this->router->link('common/home', null, \Bitsand\SSL));
			}
		}
	}

	/**
	 * Handles saving the data
	 * @param  int $event_id [description]
	 * @param  array $data     [description]
	 */
	private function save($event_id, $data) {
		// Convert all dates into SQL dates
		$date_fields = array('event_date', 'booking_open', 'booking_close');
		foreach ($date_fields as $field) {
			$data[$field] = $this->db->date(strtotime($data[$field]));
		}
		foreach ($data['item'] as &$item) {
			$item['from'] = $this->db->date(strtotime($item['from']));
			$item['to'] = $this->db->date(strtotime($item['to']));
		}

		$this->load->model('events/event');
		$new_event_id = $this->model_events_event->editEvent($event_id, $data);

		if ($new_event_id) {
			$this->session->data['success'] = 'Successfully updated event <strong>' . $data['event_name'] . '</strong>';
		}

		$this->redirect($this->router->link('events/view', array('event_id'=>$event_id), \Bitsand\SSL));
	}

	/**
	 * Displays the main form
	 *
	 * @param  array $event_info
	 */
	private function getForm($event_info = null) {
		$this->data['title'] = 'Edit Event - ' . $event_info['event_name'];
		$this->document->setTitle($this->data['title']);
		$this->document->addStyle('styles/pikaday.css');

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$date_format = 'j M Y';

		if (!$event_info) {
			$this->data['event_id'] = null;
			$this->data['event_name'] = '';
			$this->data['slug'] = null;
			$this->data['description'] = '';
			$this->data['details'] = '';
			$this->data['event_date'] = date($date_format);
			$this->data['booking_open'] = date($date_format);
			$this->data['booking_close'] = date($date_format);
			$this->data['spaces'] = array(
				'player'  => 0,
				'monster' => 0,
				'staff'   => 0,
				'total'   => 0
			);
			$this->data['bunks'] = array(
				'player'  => 0,
				'monster' => 0,
				'staff'   => 0,
				'total'   => 0
			);
			$this->data['has_monster'] = true;
			$this->data['has_queue'] = true;
			$this->data['items'] = array();
		} else {
			$this->data['event_id'] = (int)$event_info['event_id'];
			$this->data['event_name'] = $event_info['event_name'];
			$this->data['slug'] = $event_info['slug'];
			$this->data['description'] = str_ireplace('Ã','',$event_info['description']);
			$this->data['details'] = str_ireplace('Ã','',$event_info['details']);
			// Set to something we can reliably convert into a timestamp
			$this->data['event_date'] = date($date_format, strtotime($event_info['event_date']));
			$this->data['booking_open'] = date($date_format, strtotime($event_info['booking_open']));
			$this->data['booking_close'] = date($date_format, strtotime($event_info['booking_close']));
			$this->data['spaces'] = array(
				'player'  => (int)$event_info['spaces_player'],
				'monster' => (int)$event_info['spaces_monster'],
				'staff'   => (int)$event_info['spaces_staff'],
				'total'   => (int)$event_info['spaces_total']
			);
			$this->data['bunks'] = array(
				'player'  => (int)$event_info['bunks_player'],
				'monster' => (int)$event_info['bunks_monster'],
				'staff'   => (int)$event_info['bunks_staff'],
				'total'   => (int)$event_info['bunks_total']
			);
			$this->data['has_monster'] = !!$event_info['has_monster'];
			$this->data['has_queue'] = !!$event_info['has_queue'];

			$items = array();
			foreach ($event_info['items'] as $item) {
				$items[] = array(
					'item_id'      => (int)$item['item_id'],
					'description'  => $item['description'],
					'ticket'       => !!$item['ticket'],
					'meal'         => !!$item['meal'],
					'bunk'         => !!$item['bunk'],
					'from'         => date($date_format, strtotime($item['from'])),
					'to'           => date($date_format, strtotime($item['to'])),
					'availability' => strtolower($item['availability']),
					'cost'         => number_format((float)$item['cost'], 2, '.', ''),
					'multiple'     => !!$item['multiple'],
					'mandatory'    => !!$item['mandatory']
				);
			}

			$this->data['items'] = $items;
		}

		if (empty($this->data['items'])) {
			$this->data['items'][] = array(
				'item_id'      => 'new1',
				'description'  => '',
				'ticket'       => true,
				'meal'         => false,
				'bunk'         => false,
				'from'         => '', //$this->data['booking_open'],
				'to'           => '', //$this->data['booking_close'],
				'availability' => 'all',
				'cost'         => '0.00',
				'multiple'     => false,
				'mandatory'    => true
			);
		}

		// Load in the feature block css
		$this->data['editor_css'] = $this->getFeatureBlockCSS(false);

		$event_route = explode('[a:event]', $this->getEventUrl());
		$this->data['event_base'] = HTTP_SERVER . '/' . $event_route[0];
		$this->data['event_suffix'] = isset($event_route[1]) ? $event_route[1] : '';

		$this->setView('events/edit');

		$this->view->setOutput($this->render());
	}

	/**
	 * Retrieves feature-block.css from the main site, modified for CKEditor
	 * @param  boolean $return_file If true then returns inline CSS
	 * @return string
	 */
	private function getFeatureBlockCSS($return_file = false) {
		if (empty($this->_site_directory)) {
			$this->getSite();
		}

		$css_file = '';

		if (file_exists($css_file_name = Config::getBasePath() . $this->_site_directory . '/view/' . $this->_site_theme . '/styles/feature-block.css')) {
			$css_file = file_get_contents($css_file_name);
		} elseif (file_exists($css_file_name = Config::getBasePath() . $this->_site_directory . '/view/default/styles/feature-block.css')) {
			$css_file = file_get_contents($css_file_name);
		}

		if ($return_file) {
			$css_file = str_replace('.feature-block', '.ck-editor__editable', $css_file);

			return $css_file;
		} else {
			return $this->router->getSiteUrl() . '/styles/feature-block.css';
		}
	}

	/**
	 * Returns the raw event URL route
	 * @return string
	 */
	private function getEventUrl() {
		$file = file_get_contents(Config::getBasePath() . $this->_site_directory . '/routes.php');

		preg_match('~\'GET\', \'(.*)\', \'event/view\'~', $file, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} else {
			return '';
		}
	}

	/**
	 * Retrieves some information from the main site config file.
	 */
	private function getSite() {
		$file = file_get_contents(Config::getBasePath() . 'index.php');

		preg_match('/setAppDirectory\(\'(.*)\'\)/', $file, $matches);
		if (isset($matches[1])) {
			$this->_site_directory = $matches[1];
		}

		preg_match('/setVal\(\'theme\',\s*\'(.*)\'\)/', $file, $matches);
		if (isset($matches[1])) {
			$this->_site_theme = $matches[1];
		}
	}
}