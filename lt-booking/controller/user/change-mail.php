<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/email.php
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
/*
 * Changing e-mail is a two-part process.  Firstly the user submits their new
 * e-mail address (which we verify).  An e-mail is generated to the user with a
 * unique code in it (this is stored within the database) which they need to
 * click in order to approve the change.  This link should expire after 3 days.
 *
 * Previously you would request a change, be sent a unique token which you then
 * had to enter on the change password page.  The new routine follows a more
 * standardised approach.
 */

namespace LTBooking\Controller;

use Bitsand\Controllers\Controller;
use Bitsand\Utilities\Mailer;

class UserChangeMail extends Controller {
	private $_errors = array();

	public function index() {
		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/reset', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		if ($this->request->method() === 'POST' && $this->validateEmail($this->request->post)) {
			// OK to start the reset, tidy up the e-mail, sorry only lowercase and no trailing spaces
			$email = trim(strtolower($this->request->post['email']));
			if (($token = $this->model_user_user->changeEmail($this->user->getId(), $email))) {
				$this->sendLink($token, $email);
				$this->session->data['success'] = 'An e-mail has been sent with instructions on how to complete your e-mail change.';
				$this->redirect($this->router->link('user/account', null, \Bitsand\SSL));
			} else {
				$this->session->data['warning'] = 'There was a problem changing your e-mail';
			}
		}

		$this->showForm();
	}

	/**
	 * Completes the e-mail change.  In theory it would be possible to brute
	 * force this.  However the only way of getting a valid token is having the
	 * user logged in so there is no real benefit of doing this at all.
	 *
	 * @param string $token
	 */
	public function change($token) {
		$this->load->model('user/user');

		if (($user = $this->model_user_user->authoriseEmail($token))) {
			$this->session->data['success'] = 'Your e-mail address has been successfully changed';
			// We now need to send an e-mail to the old address - REGARDLESS of settings
			$this->sendChangeMail($user);

			if (!$this->user->isLogged()) {
				$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
			} else {
				$this->redirect($this->router->link('user/account', null, \Bitsand\SSL));
			}
		} else {
			$this->session->data['warning'] = 'There was a problem with the link you used, it is possible it has expired.  Please try changing your e-mail again.';
			if (!$this->user->isLogged()) {
				$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
			} else {
				$this->redirect($this->router->link('user/change-mail', null, \Bitsand\SSL));
			}
		}
	}

	/**
	 * Displays the new password form
	 * @param array $url The url to use for submitting the data to, this varies
	 * between a normal reset or forgotten password
	 */
	private function showForm() {
		$this->document->setTitle('Change E-mail');
		$this->load->model('user/user');

		$this->data['reset'] = $this->router->link('user/change-mail', null, \Bitsand\SSL);

		$this->data['current_email'] = $this->model_user_user->getEmail($this->user->getId());

		if (isset($this->_errors['email'])) {
			$this->data['error_email'] = $this->_errors['email'];
		}

		if (isset($this->_errors['confirm'])) {
			$this->data['error_confirm'] = $this->_errors['confirm'];
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['confirm'])) {
			$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/change-mail');

		$this->view->setOutput($this->render());
	}

	/**
	 * Sends the approval link to the user
	 *
	 * @param string $token
	 * @param string $email
	 */
	private function sendLink($token, $email) {
		$this->load->model('user/user');

		$mailer = new Mailer;
		$mailer->setMail('change-mail');
		$mailer->setMail('change-mail-plain', Mailer::PLAIN_TEXT);

		$current_email = strtolower($this->model_user_user->getEmail($this->user->getId()));
		$user = $this->model_user_user->getBasicDetails($current_email, true);

		$mailer->data['email'] = $email;
		$mailer->data['current_email'] = $current_email;

		$mailer->data['firstname'] = $user['firstname'];
		$mailer->data['url'] = $this->router->link('user/change-mail/change', array('token'=>$token), \Bitsand\SSL, true);
		$mailer->data['site_name'] = $this->config->get('site_name');

		// @todo - This needs to be setable within the backend
		$mailer->setSubject('E-mail Change for {site_name}');
		$mailer->setFrom($this->config->get('event_contact_email'));
		$mailer->setSender($this->config->get('event_contact'));

		$mailer->sendTo($email);
	}

	private function sendChangeMail($user) {
		$this->load->model('user/user');

		$mailer = new Mailer;
		$mailer->setMail('changed-mail');
		$mailer->setMail('changed-mail-plain', Mailer::PLAIN_TEXT);

		$current_email = $user['email'];
		$old_email = $user['old_email'];

		$mailer->data['old_email'] = $old_email;
		$mailer->data['current_email'] = $current_email;

		$mailer->data['firstname'] = $user['firstname'];
		$mailer->data['contact_link'] = $this->router->link('common/contact', null, \Bitsand\NONSSL, true);
		$mailer->data['site_name'] = $this->config->get('site_name');

		// @todo - This needs to be setable within the backend
		$mailer->setSubject('E-mail Change for {site_name}');
		$mailer->setFrom($this->config->get('event_contact_email'));
		$mailer->setSender($this->config->get('event_contact'));

		$mailer->sendTo($old_email);
	}

	/**
	 * Validates that the entered emails are legitimate and identical
	 * @param array $data
	 * @return boolean
	 */
	private function validateEmail($data) {
		if (!isset($data['email']) || !$this->user->isValidEmail($data['email'])) {
			$this->_errors['email'] = 'E-mail does not appear to be valid';
		} else {
			$this->load->model('user/user');

			if (strtolower(trim($data['email'])) == $this->model_user_user->getEmail($this->user->getId())) {
				$this->_errors['email'] = 'New e-mail matches current one';
			}
		}

		if (!isset($data['confirm']) || (isset($data['email']) && isset($data['confirm']) && $data['email'] != $data['confirm'])) {
			$this->_errors['confirm'] = 'E-mails do not match';
		}

		return empty($this->_errors);
	}
}