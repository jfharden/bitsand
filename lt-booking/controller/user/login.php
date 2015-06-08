<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/login.php
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

class UserLogin extends Controller {
	private $errors = array();

	public function index() {
		// If already logged in then send back to home page
		if ($this->user->isLogged()) {
			$this->redirect($this->router->link('common/home'));
		}

		$this->document->setTitle('Log In');

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		if (isset($this->session->data['error_email'])) {
			$this->data['error_email'] = $this->session->data['error_email'];
			unset($this->session->data['error_email']);
		}

		if (isset($this->session->data['error_password'])) {
			$this->data['error_password'] = $this->session->data['error_password'];
			unset($this->session->data['error_password']);
		}

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/login');

		$this->view->setOutput($this->render());
	}

	public function login() {
		if ($this->request->method() === 'POST' && !$this->user->isLogged() && $this->validateLogin()) {
			$this->load->model('user/user');

			$response = $this->model_user_user->login($this->request->post['email'], $this->request->post['password']);

			if ($response === \LTBooking\Model\UserUser::LOGGED_IN) {
				var_dump('ok');
			} elseif ($response === \LTBooking\Model\UserUser::INCORRECT) {
				var_dump('wrong/not recognised');
			} elseif ($response === \LTBooking\Model\UserUser::JUST_LOCKED) {
				var_dump('wrong again, now locked');
			} elseif ($response === \LTBooking\Model\UserUser::LOCKED) {
				var_dump('locked out');
			} else {
				var_dump('no idea how we`ve got here');
			}


			// Perform a redirect to the home page
			$this->redirect($this->router->link('common/home'));
		} else {
			// Pass errors back
			if (isset($this->errors['error_email'])) {
				$this->session->data['error_email'] = $this->errors['error_email'];
			}

			if (isset($this->errors['error_password'])) {
				$this->session->data['error_password'] = $this->errors['error_password'];
			}

			$this->redirect($this->router->link('user/login'), null, \Bitsand\SSL);
		}
	}

	public function logout() {
		$this->load->model('user/user');

		$this->model_user_user->logout();

		$this->session->data['success'] = 'You have successfully logged off';

		$this->redirect($this->router->link('user/login'), null, \Bitsand\SSL);
	}

	private function validateLogin() {
		if (!isset($this->request->post['email'])) {
			$this->errors['error_email'] = 'No e-mail';
		}

		if (!isset($this->request->post['password'])) {
			$this->errors['error_password'] = 'No password';
		}

		return empty($this->errors);
	}
}