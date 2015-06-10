<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/account.php
 ||    Summary: Provides a central portal to various items for doing account
 ||             management items.
 ||
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

class UserAccount extends Controller {
	private $errors = array();

	public function index() {
		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/account', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->document->setTitle('My Account');

		$this->data['reset'] = $this->router->link('user/reset', null, \Bitsand\SSL);
		$this->data['mailing'] = $this->router->link('user/mailing', null, \Bitsand\SSL);
		$this->data['change_email'] = $this->router->link('user/change_email', null, \Bitsand\SSL);
		$this->data['personal_details'] = $this->router->link('user/details/personal', null, \Bitsand\SSL);
		$this->data['character_details'] = $this->router->link('user/details/character', null, \Bitsand\SSL);

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/account');

		$this->view->setOutput($this->render());
	}
}