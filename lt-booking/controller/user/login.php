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
	public function index() {}

	public function login() {
		$this->load->model('user/user');

		$response = $this->model_user_user->login($this->request->post['email'], $this->request->post['password']);

		if ($response === $this->model_user_user::LOGGED_IN) {
			var_dump('ok');
		} elseif ($response === $this->model_user_user::INCORRECT) {
			var_dump('wrong/not recognised');
		} elseif ($response === $this->model_user_user::JUST_LOCKED) {
			var_dump('wrong again, now locked');
		} elseif ($response === $this->model_user_user::LOCKED) {
			var_dump('locked out');
		} else {
			var_dump('no idea how we`ve got here');
		}


		// Perform a redirect now
		var_dump($this->request->post);
	}
}