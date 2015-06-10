<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/user/mailing.php
 ||    Summary: Allows a user to edit when they receive e-mail notifications
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

class UserMailing extends Controller {
	private $errors = array();

	public function index() {
		if (!$this->user->isLogged()) {
			$this->session->data['redirect'] = $this->router->link('user/mailing', null, \Bitsand\SSL);
			$this->redirect($this->router->link('user/login', null, \Bitsand\SSL));
		}

		$this->load->model('user/user');

		// No need to validate anything
		if ($this->request->method() === 'POST') {
			$data = array(
				'ooc'     => isset($this->request->post['ooc']),
				'ic'      => isset($this->request->post['ic']),
				'payment' => isset($this->request->post['payment']),
				'queue'   => isset($this->request->post['queue'])
			);
			$this->model_user_user->changeMailing($this->user->getId(), $data);

			$this->data['success'] = 'Your e-mail preferences have been updated';

			$this->redirect($this->router->link('user/account'), null, \Bitsand\SSL);
		}

		$this->document->setTitle('E-mail Preferences');

		$mailing_details = $this->model_user_user->getMailingDetails($this->user->getId());

		$this->data += $mailing_details;
		$this->data['mailing'] = $this->router->link('user/mailing', null, \Bitsand\SSL);
		$this->data['account'] = $this->router->link('user/account', null, \Bitsand\SSL);

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('user/mailing');

		$this->view->setOutput($this->render());
	}
}