<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/reset.php
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

class UserReset extends Controller {
	private $_errors = array();

	public function index() {
		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/reset', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		if ($this->request->method() === 'POST' && $this->validatePassword($this->request->post)) {
			// We're ok to reset
			if ($this->model_user_user->changePassword($this->user->getUserId(), $this->request->post['password'])) {
				$this->session->data['success'] = 'You have successfully changed your password';
			} else {
				$this->session->data['warning'] = 'There was a problem changing your password';
			}

			$this->redirect($this->router->link('user/account', null, \Bitsand\SSL));
		}

		$this->showForm($this->router->link('user/reset', null, \Bitsand\SSL));
	}

	/**
	 * This is the method used when a user clicks on a link, $token is parsed
	 * using regex rules and can only be alphanumeric
	 * @param string $token
	 */
	public function forgotten($token) {
		$this->load->model('user/user');

		if (!($user_id = $this->model_user_user->getUserByToken($token))) {
			$this->session->data['error'] = 'The link used appears invalid, it may have expired or the user may have logged on since the e-mail was sent';

			$this->redirect($this->router->link('user/forgotten', null, \Bitsand\SSL));
		}

		if ($this->request->method() === 'POST' && $this->validatePassword($this->request->post)) {
			// We're ok to reset
			if ($this->model_user_user->changePassword($user_id, $this->request->post['password'])) {
				$this->session->data['success'] = 'You have successfully changed your password';
			} else {
				$this->session->data['warning'] = 'There was a problem changing your password';
			}

			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->showForm($this->router->link('user/reset/forgotten', array('token'=>$token), \Bitsand\SSL));
	}

	/**
	 * Displays the new password form
	 * @param array $url The url to use for submitting the data to, this varies
	 * between a normal reset or forgotten password
	 */
	private function showForm($url) {
		$this->document->setTitle('Reset Password');

		$this->data['password_length'] = $this->config->get('password_minimum');
		$this->data['suggested'] = \Bitsand\Utilities\Functions\url_get_contents('http://www.dinopass.com/password/strong');

		$this->data['reset'] = $url;


		if (isset($this->_errors['password'])) {
			$this->data['error_password'] = $this->_errors['password'];
		}

		if (isset($this->_errors['confirm'])) {
			$this->data['error_confirm'] = $this->_errors['confirm'];
		}

		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
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

		$this->setView('user/reset');

		$this->view->setOutput($this->render());
	}

	/**
	 * Validates that the entered passwords are long enough and identical
	 * @param array $data
	 * @return boolean
	 */
	private function validatePassword($data) {
		if (!isset($data['password']) || strlen(utf8_decode($data['password'])) < $this->config->get('password_minimum')) {
			$this->_errors['password'] = sprintf('Password must be at least %s characters', $this->config->get('password_minimum'));
		}

		if (!isset($data['confirm']) || strlen(utf8_decode($data['password'])) < $this->config->get('password_minimum')) {
			$this->_errors['confirm'] = sprintf('Password must be at least %s characters', $this->config->get('password_minimum'));
		}

		if (isset($data['password']) && isset($data['confirm']) && $data['password'] != $data['confirm']) {
			$this->_errors['confirm'] = 'Passwords do not match';
		}

		return empty($this->_errors);
	}
}