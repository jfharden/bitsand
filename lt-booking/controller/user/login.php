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
			if (isset($this->session->data['redirect'])) {
				$redirect_to = $this->session->data['redirect'];
				unset($this->session->data['redirect']);
			} else {
				$redirect_to = $this->router->link('common/home');
			}
			$this->redirect($redirect_to);
		}

		$this->document->setTitle('Log In');

		if (isset($this->session->data['error_email'])) {
			$this->data['error_email'] = $this->session->data['error_email'];
			unset($this->session->data['error_email']);
		}

		if (isset($this->session->data['error_password'])) {
			$this->data['error_password'] = $this->session->data['error_password'];
			unset($this->session->data['error_password']);
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = '';
		}


		$this->data['login'] = $this->router->link('user/login/login');
		$this->data['forgotten'] = $this->router->link('user/forgotten');
		$this->data['register'] = $this->router->link('user/register');
		$this->data['terms'] = $this->router->link('common/terms');

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
				$this->session->data['success'] = 'Succesfully logged on';
			} elseif ($response === \LTBooking\Model\UserUser::INCORRECT) {
				$this->session->data['warning'] = 'E-mail or password wrong or not recognised';
			} elseif ($response === \LTBooking\Model\UserUser::JUST_LOCKED) {
				$this->session->data['error'] = 'Password incorrect, your account has now been locked out';
			} elseif ($response === \LTBooking\Model\UserUser::LOCKED) {
				$this->session->data['error'] = 'Account has been locked out';
			} else {
				var_dump('no idea how we`ve got here');die();
			}

			if (isset($this->session->data['redirect'])) {
				$redirect_to = $this->session->data['redirect'];
				unset($this->session->data['redirect']);
				$this->redirect($redirect_to);
			}
		} else {
			// Pass errors back
			if (isset($this->errors['error_email'])) {
				$this->session->data['error_email'] = $this->errors['error_email'];
			}

			if (isset($this->errors['error_password'])) {
				$this->session->data['error_password'] = $this->errors['error_password'];
			}
		}

		$this->redirect($this->router->link('user/login'), null, \Bitsand\SSL);
	}

	public function logout() {
		$this->load->model('user/user');

		$this->model_user_user->logout();

		$this->session->data['success'] = 'You have successfully logged out';

		$this->redirect($this->router->link('user/login'), null, \Bitsand\SSL);
	}

	private function validateLogin() {
		if (!isset($this->request->post['email'])) {
			$this->errors['error_email'] = 'No e-mail';
		} elseif (!$this->user->isValidEmail($this->request->post['email'])) {
			$this->errors['error_email'] = 'E-mail appears to be invalid';
		}

		if (!isset($this->request->post['password'])) {
			$this->errors['error_password'] = 'No password';
		}

		return empty($this->errors);
	}
}