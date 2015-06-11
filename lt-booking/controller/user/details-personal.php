<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/details.php
 ||    Summary: Handles all of the settings of the
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

class UserDetailsPersonal extends Controller {
	private $_errors = array();

	/**
	 * Displays the new password form
	 * @param array $url The url to use for submitting the data to, this varies
	 * between a normal reset or forgotten password
	 */
	public function index() {
		/*
		 * Decided not to use a datepicker for the date of birth field as it's
		 * easier to choose from 3 selectors than navigate back X years in a
		 * datepicker.
		 */
		// $this->document->addScript('scripts/datepicker.js', false);
		$this->document->setTitle('My Details (OOC)');

		$this->data['personal'] = $this->router->link('user/details-personal', null, \Bitsand\SSL);

		// Pass all of the necessary values to the view
		$details = $this->model_user_user->getPersonalDetails($this->user->getId());

		if (isset($this->request->post['firstname'])) {
			$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($details)) {
			$this->data['firstname'] = $details['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($details)) {
			$this->data['lastname'] = $details['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$this->data['address_1'] = $this->request->post['address_1'];
		} elseif (!empty($details)) {
			$this->data['address_1'] = $details['address_1'];
		} else {
			$this->data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$this->data['address_2'] = $this->request->post['address_2'];
		} elseif (!empty($details)) {
			$this->data['address_2'] = $details['address_2'];
		} else {
			$this->data['address_2'] = '';
		}

		if (isset($this->request->post['address_3'])) {
			$this->data['address_3'] = $this->request->post['address_3'];
		} elseif (!empty($details)) {
			$this->data['address_3'] = $details['address_3'];
		} else {
			$this->data['address_3'] = '';
		}

		if (isset($this->request->post['address_4'])) {
			$this->data['address_4'] = $this->request->post['address_4'];
		} elseif (!empty($details)) {
			$this->data['address_4'] = $details['address_4'];
		} else {
			$this->data['address_4'] = '';
		}

		if (isset($this->request->post['postcode'])) {
			$this->data['postcode'] = $this->request->post['postcode'];
		} elseif (!empty($details)) {
			$this->data['postcode'] = $details['postcode'];
		} else {
			$this->data['postcode'] = '';
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/details-personal');

		$this->view->setOutput($this->render());
	}
}