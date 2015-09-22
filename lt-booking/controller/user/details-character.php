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

class UserDetailsCharacter extends Controller {
	private $_errors = array();

	/**
	 * Displays the new password form
	 * @param array $url The url to use for submitting the data to, this varies
	 * between a normal reset or forgotten password
	 */
	public function index() {
		$this->document->addScript('scripts/xhr.js', false);

		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/details-character', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->load->model('user/user');


		if ($this->request->method() == 'POST' && $this->validate()) {
			$send_email = $this->model_user_user->changeCharacterDetails($this->user->getId(), $this->request->post);

			$this->session->data['success'] = 'Your character details have been updated';

			/*
			 * Send an e-mail to the user if we've updated anything and they've
			 * said they want to be notified.  Hook this in as a post page
			 * callback as we don't want to hold up the output
			 */
			if ($send_email) {
				// Need to send an e-mail saying they've been updated
				$this->view->addPostCallback(new ActionRoute('user/details-character/send-email', array('email'=>$send_email)));
			}

			$this->redirect($this->router->link('user/account'), null, \Bitsand\SSL);
		}

		$this->document->setTitle('My Details (IC)');

		$this->data['character'] = $this->router->link('user/details-character', null, \Bitsand\SSL);

		// Pass all of the necessary values to the view
		$details = $this->model_user_user->getCharacterDetails($this->user->getId());

		$this->handlePostData(array('character_name', 'alias', 'ancestor', 'ancestor_other', 'guilds', 'group', 'group_other', 'location'), $details);

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

		// Handle racial types.  Default race is Human
		$this->load->model('character/race');
		$this->handlePostData(array('race'), $details, 'human');
		$this->data['racial_types'] = $this->model_character_race->getAll();
		// Race 'other' is unique in that it doesn't have it's own column,
		// instead using the main race one.  This has the added benefit that if
		// we add or remove races it is handled better
		if ($this->data['race'] == 'other') {
			$this->data['race_other'] = $this->request->post['race_other'];
		} else {
			foreach ($this->data['racial_types'] as $race) {
				if (strtosafelower($this->data['race']) == strtosafelower($race)) {
					// Means we have a match
					$this->data['race_other'] = '';
					break;
				}
			}
			if (!isset($this->data['race_other'])) {
				$this->data['race_other'] = $this->data['race'];
				$this->data['race'] = 'other';
			}
		}

		// Handle genders.  Default gender is male
		$this->load->model('character/gender');
		$this->handlePostData(array('gender'), $details, 'male');
		$this->data['gender_types'] = $this->model_character_gender->getAll();

		// Factional items
		$this->load->model('character/faction');
		$this->data['faction_names'] = array();
		$this->handlePostData(array('faction'), $details, $this->config->get('default_faction'));
		foreach ($this->model_character_faction->getAll() as $faction) {
			$this->data['faction_names'][(int)$faction['faction_id']] = $faction['faction_name'];
		}

		// Group items, this is actually optional
		$this->data['group_label'] = $this->config->get('list_groups_label');
		$this->data['group_names'] = array();
		if ($this->data['group_label'] === true || $this->data['group_label'] == 1) {
			$this->data['group_label'] = 'Group';
		}
		if ($this->data['group_label']) {
			$this->load->model('character/group');
			foreach ($this->model_character_group->getAll() as $group) {
				$this->data['group_names'][$group['group_id']] = $group['group_name'];
			}
		}

		// Ancestors
		$this->data['ancestor_names'] = array();
		if ($this->config->get('ancestor_dropdown')) {
			$this->load->model('character/ancestor');

			// Failsafe incase dropdown has been enabled and characters already have an ancestor entered
			if (!$this->data['ancestor']) {
				$this->data['ancestor'] = 'other';
			}

			foreach ($this->model_character_ancestor->getAll() as $ancestor) {
				$this->data['ancestor_names'][$ancestor['ancestor_id']] = $ancestor['ancestor_name'];

				// It's possible that the entered name has been added to the list
				if ($this->data['ancestor'] == 'other' && \strtosafelower($ancestor['ancestor_name']) == strtosafelower($this->data['ancestor_other'])) {
					$this->data['ancestor'] = strtosafelower($ancestor['ancestor_name']);
					$this->data['ancestor_other'] = '';
				}
			}
		}

		// Character location
		// @todo Needs more options, required/optional, allow Other etc
		$this->data['location_names'] = array();

		if ($this->config->get('character_location_label')) {
			$this->data['location_label'] = $this->config->get('character_location_label');
			$this->load->model('character/location');

			foreach ($this->model_character_location->getAll() as $location) {
				$this->data['location_names'][$location['location_id']] = $location['location_name'];
			}
		} else {
			$this->data['location_label'] = '';
		}

		// Guilds
		$this->load->model('character/guild');
		$this->data['guild_names'] = array();
		foreach ($this->model_character_guild->getAll() as $guild) {
			$this->data['guild_names'][$guild['guild_id']] = $guild['guild_name'];
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/details-character');

		$this->view->setOutput($this->render());
	}

	/**
	 * Sends an e-mail to a user indicating that their characters details has
	 * been changed.  This should be called using a response post callback.
	 *
	 * @param string $email
	 */
	public function sendEmail($email) {
		$user = $this->model_user_user->getBasicDetails($email);

		$mailer = new Mailer;
		$mailer->setMail('character-change');
		$mailer->setMail('character-change-plain', Mailer::PLAIN_TEXT);

		$mailer->data['email'] = strtolower($email);
		$mailer->data['player_id'] = $this->model_user_user->playerId($this->user->getId());

		$mailer->data['firstname'] = $user['firstname'];
		$mailer->data['lastname'] = $user['lastname'];
		$mailer->data['url'] = $this->router->link('common/home', null, \Bitsand\NONSSL, true);
		$mailer->data['site_name'] = $this->config->get('site_name');

		// @todo - This needs to be setable within the backend
		$mailer->setSubject('Character details (IC) changed on {site_name}');
		$mailer->setFrom($this->config->get('event_contact_email'));
		$mailer->setSender($this->config->get('event_contact'));

		$mailer->sendTo($email);
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