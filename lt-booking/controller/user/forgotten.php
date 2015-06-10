<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/forgotten.php
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
use Bitsand\Utilities\Mailer;

class UserForgotten extends Controller {
	private $errors = array();

	public function index() {
		// If already logged in then send to the account page
		if ($this->user->isLogged()) {
			$this->redirect($this->router->link('user/account'));
		}

		$this->document->setTitle('Forgotten Password');

		if (isset($this->session->data['error_email'])) {
			$this->data['error_email'] = $this->session->data['error_email'];
			unset($this->session->data['error_email']);
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} elseif (isset($this->session->data['passthru_email'])) {
			$this->data['email'] = $this->session->data['passthru_email'];
			unset($this->session->data['passthru_email']);
		} else {
			$this->data['email'] = '';
		}


		$this->data['forgotten'] = $this->router->link('user/forgotten');

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/forgotten');

		$this->view->setOutput($this->render());
	}

	/**
	 * Handler for sending a link to reset the password.
	 */
	public function sendLink() {
		if ($this->request->method() === 'POST' && !$this->user->isLogged() && $this->validateEmail()) {
			$this->load->model('user/user');

			$email = strtolower(trim($this->request->post['email']));

			$user = $this->model_user_user->getBasicDetails($email, true);

			/*
			 * Check that the user exists.  If the user doesn't exist then
			 * still display the same message, this ensures that the forgotten
			 * password page can't be used to validate that an e-mail exists
			 * which will prevent potential hacking against that e-mail
			 * address.
			 */
			if (!empty($user)) {
				$mailer = new Mailer;
				$mailer->setMail('forgotten');
				$mailer->setMail('forgotten-plain', Mailer::PLAIN_TEXT);

				$mailer->data['email'] = strtolower($email);

				$mailer->data['firstname'] = $user['firstname'];
				$mailer->data['url'] = $this->router->link('common/home', null, \Bitsand\NONSSL, true);
				$mailer->data['reset_link'] = $this->router->link('user/reset/forgotten', array('token'=>$user['reset_token']), \Bitsand\SSL, true);
				$mailer->data['site_name'] = $this->config->get('site_name');

				// @todo - This needs to be setable within the backend
				$mailer->setSubject('Password Reset for {site_name}');
				$mailer->setFrom($this->config->get('event_contact_email'));
				$mailer->setSender($this->config->get('event_contact'));

				$mailer->sendTo($email);
			}

			$this->session->data['success'] = 'A password request email has been sent to the address you provided. If an email doesn\'t arrive shortly, please check your spam folder. If no email arrives, then no account exists with the email you provided.';

			$this->redirect($this->router->link('user/login'), null, \Bitsand\SSL);
		} else {
			if (isset($this->errors['error_email'])) {
				$this->session->data['error_email'] = $this->errors['error_email'];
			}

			if (isset($this->errors['warning'])) {
				$this->session->data['warning'] = $this->errors['warning'];
			}

			if (isst($this->request->post['email'])) {
				$this->session->data['passthru_email'] = $this->request->post['email'];
			}

			$this->redirect($this->router->link('user/forgotten'), null, \Bitsand\SSL);
		}
	}

	/**
	 * Checks that the person has entered something legitimate
	 * @return boolean
	 */
	private function validateEmail() {
		if (!isset($this->request->post['email'])) {
			$this->errors['error_email'] = 'No e-mail';
		} elseif (!$this->user->isValidEmail($this->request->post['email'])) {
			$this->errors['error_email'] = 'E-mail appears to be invalid';
		}

		return empty($this->errors);
	}
}