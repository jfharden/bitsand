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
use Bitsand\Controllers\ActionRoute;
use Bitsand\Utilities\Mailer;

class UserDetailsPersonal extends Controller {
	private $_errors = array();

	/**
	 * Displays the new password form
	 * @param array $url The url to use for submitting the data to, this varies
	 * between a normal reset or forgotten password
	 */
	public function index() {
		$this->document->addScript('scripts/xhr.js', false);

		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/details-personal', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->load->model('user/user');


		if ($this->request->method() == 'POST' && $this->validate()) {
			$send_email = $this->model_user_user->changePersonalDetails($this->user->getId(), $this->request->post);

			$this->session->data['success'] = 'Your personal details have been updated';

			/*
			 * Send an e-mail to the user if we've updated anything and they've
			 * said they want to be notified.  Hook this in as a post page
			 * callback as we don't want to hold up the output
			 */
			if ($send_email) {
				// Need to send an e-mail saying they've been updated
				$this->view->addPostCallback(new ActionRoute('user/details-personal/send-email', array('email'=>$send_email)));
			}

			$this->redirect($this->router->link('user/account'), null, \Bitsand\SSL);
		}

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

		$this->handlePostData(array('firstname', 'lastname', 'address_1', 'address_2', 'address_3', 'address_4', 'postcode', 'telephone', 'mobile', 'medical', 'emergency_contact', 'emergency_relation', 'emergency_number', 'car_registration', 'dietary', 'marshal', 'marshal_number', 'notes'), $details);

		// Handle the errors here
		$this->data['error_firstname'] = isset($this->_errors['firstname']) ? $this->_errors['firstname'] : '';
		$this->data['error_lastname'] = isset($this->_errors['lastname']) ? $this->_errors['lastname'] : '';
		$this->data['error_address_1'] = isset($this->_errors['address_1']) ? $this->_errors['address_1'] : '';
		$this->data['error_telephone'] = isset($this->_errors['telephone']) ? $this->_errors['telephone'] : '';
		$this->data['error_dob'] = isset($this->_errors['dob']) ? $this->_errors['dob'] : '';
		$this->data['error_emergency_contact'] = isset($this->_errors['emergency_contact']) ? $this->_errors['emergency_contact'] : '';
		$this->data['error_emergency_relation'] = isset($this->_errors['emergency_relation']) ? $this->_errors['emergency_relation'] : '';
		$this->data['error_emergency_number'] = isset($this->_errors['emergency_number']) ? $this->_errors['emergency_number'] : '';
		$this->data['error_marshal_number'] = isset($this->_errors['marshal_number']) ? $this->_errors['marshal_number'] : '';
		if (!empty($this->_errors)) {
			$this->data['error'] = 'Please complete all fields';
		}

		// DOB is slightly different as it's a date
		if (isset($this->request->post['dob'])) {
			$dob = mktime(0,0,0, (int)$this->request->post['dob']['m'], (int)$this->request->post['dob']['d'], (int)$this->request->post['dob']['y']);
		} elseif (isset($details['dob']) && !empty($details['dob'])) {
			$dob = strtotime($details['dob']);
		} else {
			$dob = strtotime('-20 years');
		}

		$dob = explode('|',date('j|n|Y', $dob));
		$this->data['dob']['d'] = $dob[0];
		$this->data['dob']['m'] = $dob[1];
		$this->data['dob']['y'] = $dob[2];

		if (!empty($this->config->get('postcode_lookup'))) {
			$this->data['postcode_lookup'] = $this->router->link('user/details-personal/postcode-lookup', array('postcode'=>''));
		} else {
			$this->data['postcode_lookup'] = false;
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/details-personal');

		$this->view->setOutput($this->render());
	}

	/**
	 * Sends an e-mail to a user indicating that their personal details has
	 * been changed.  This should be called using a response post callback.
	 *
	 * @param string $email
	 */
	public function sendEmail($email) {
		$user = $this->model_user_user->getBasicDetails($email);

		$mailer = new Mailer;
		$mailer->setMail('personal-change');
		$mailer->setMail('personal-change-plain', Mailer::PLAIN_TEXT);

		$mailer->data['email'] = strtolower($email);
		$mailer->data['player_id'] = $this->model_user_user->playerId($this->user->getId());

		$mailer->data['firstname'] = $user['firstname'];
		$mailer->data['lastname'] = $user['lastname'];
		$mailer->data['url'] = $this->router->link('common/home', null, \Bitsand\NONSSL, true);
		$mailer->data['site_name'] = $this->config->get('site_name');

		// @todo - This needs to be setable within the backend
		$mailer->setSubject('Personal details (OOC) changed on {site_name}');
		$mailer->setFrom($this->config->get('event_contact_email'));
		$mailer->setSender($this->config->get('event_contact'));

		$mailer->sendTo($email);
	}

	/**
	 * Hooks in a postcode lookup mechanism.  Must be requested via AJAX
	 * @param string $postcode
	 */
	public function postcodeLookup($postcode) {
		$result = array('p'=>$postcode, 'a'=>$this->request->isAJAX());

		if ($this->request->isAJAX() && !empty($this->config->get('postcode_lookup')) && !empty($postcode)) {
			$postcode = strtoupper(str_replace(' ', '', $postcode));
			if ($this->config->get('postcode_lookup') == 'postcodes.io') {
				// Postcodes.io - returns the county and town only
				$answer = \Bitsand\Utilities\Functions\url_get_contents('http://api.postcodes.io/postcodes/' . $postcode);
				if (!empty($answer)) {
					$answer = json_decode($answer, true);
					if ($answer['status'] == '200') {
						$result['postcode'] = $answer['result']['postcode'];
						$result['city'] = $answer['result']['parliamentary_constituency'];
						$result['county'] = $answer['result']['admin_district'];
					}
				} else {
					$result['error'] = 'Postcode not found';
				}
			} elseif (substr($this->config->get('postcode_lookup'), 0, 7) == 'custom:') {
				// A custom api, we expect it to return a json encoded object, containing an item called "results"
				$url = substr($this->config->get('postcode_lookup'), 7) . '?postcode=' . $postcode;
				$answer = \Bitsand\Utilities\Functions\url_get_contents($url);
				if (!empty($answer)) {
					$answer = json_decode($answer, true);
					if (isset($answer['results'])) {
						// Sort the addresses numerically (ensuring 1 comes before 10);
						$sort = array();
						foreach ($answer['results'] as $result_idx => $postcode) {
							preg_match('/^([^\s]+)/', $postcode['address1'], $match);
							// Look at the first item to see if it's a number to cater for 10A etc
							if (is_numeric(substr($match[0], 0, 1))) {
								$index = ((int)$match[0] + 10000) . $postcode['address1'];
							} else {
								$index = $postcode['address1'];
							}
							$sort[$index] = $result_idx;
						}
					}
					ksort($sort);

					foreach ($sort as $result_idx) {
						$postcode = $answer['results'][$result_idx];
						$result['addresses'][] = array(
							'address_1' => $postcode['address1'],
							'address_2' => $postcode['address2'],
							'city'      => $postcode['town'],
							'county'    => $postcode['county'],
							'postcode'  => ''
						);
					}
				} else {
					$result['error'] = 'Postcode not found';
				}
			}
		}

		$this->view->setOutput(json_encode($result));
	}

	private function validate() {
		$firstname_len = isset($this->request->post['firstname']) ? strlen(utf8_decode(trim($this->request->post['firstname']))) : 0;
		if ($firstname_len < 2) {
			$this->_errors['firstname'] = 'First name must be at least 2 characters in length';
		}

		$lastname_len = isset($this->request->post['lastname']) ? strlen(utf8_decode(trim($this->request->post['lastname']))) : 0;
		if ($lastname_len < 2) {
			$this->_errors['lastname'] = 'Last name must be at least 2 characters in length';
		}

		$address_len = isset($this->request->post['address_1']) ? strlen(utf8_decode(trim($this->request->post['address_1']))) : 0;
		if ($address_len < 2) {
			$this->_errors['address_1'] = 'Address must be completed';
		}

		$telephone = isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : '';
		$mobile = isset($this->request->post['mobile']) ? trim($this->request->post['mobile']) : '';
		if (empty($telephone) && empty($mobile)) {
			$this->_errors['telephone'] = 'Please enter at least one contact number';
		}

		// The following ensures the date of birth is valid, including dates that aren't real
		if (isset($this->request->post['dob'])) {
			$dob_entered = (int)$this->request->post['dob']['y'] . '|' . (int)$this->request->post['dob']['m'] . '|' . (int)$this->request->post['dob']['d'];
			$dob = mktime(0,0,0, (int)$this->request->post['dob']['m'], (int)$this->request->post['dob']['d'], (int)$this->request->post['dob']['y']);
		} else {
			$dob_entered = 'not set';
			$dob = 0;
		}
		if ($dob_entered != date('Y|n|j', $dob)) {
			$this->_errors['dob'] = 'Please check, date of birth appears invalid';
		}

		$emergency_contact_len = isset($this->request->post['emergency_contact']) ? strlen(utf8_decode(trim($this->request->post['emergency_contact']))) : 0;
		if ($emergency_contact_len < 2) {
			$this->_errors['emergency_contact'] = 'Emergency contact must be at least 2 characters in length';
		}

		$emergency_relation_len = isset($this->request->post['emergency_relation']) ? strlen(utf8_decode(trim($this->request->post['emergency_relation']))) : 0;
		if ($emergency_relation_len < 2) {
			$this->_errors['emergency_relation'] = 'Emergency relationship must be at least 2 characters in length';
		}

		$emergency_number_len = isset($this->request->post['emergency_number']) ? strlen(utf8_decode(trim($this->request->post['emergency_number']))) : 0;
		if ($emergency_number_len < 2) {
			$this->_errors['emergency_number'] = 'Emergency relationship must be at least 2 characters in length';
		}

		$car_registration_len = isset($this->request->post['car_registration']) ? strlen(utf8_decode(trim($this->request->post['car_registration']))) : 0;
		if ($car_registration_len < 2) {
			$this->_errors['car_registration'] = 'Car registration must be at least 2 characters in length';
		}

		$is_marshal = isset($this->request->post['marshal']) ? strtolower($this->request->post['marshal']) != 'no' : false;
		$marshal_number_len = isset($this->request->post['marshal_number']) ? strlen(utf8_decode(trim($this->request->post['marshal_number']))) : 0;

		if ($is_marshal && $marshal_number_len == 0) {
			$this->_errors['marshal_number'] = 'Please enter a ' . strtolower($this->request->post['marshal']) . ' number';
		}

		return empty($this->_errors);
	}
}