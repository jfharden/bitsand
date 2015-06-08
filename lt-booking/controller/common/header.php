<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/common/footer.php
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

class CommonHeader extends Controller {
	public function index() {
		$this->document->addStyle('styles/reset.css');
		$this->document->addStyle('styles/body.css');

		$this->data['title'] = $this->document->getTitle();
		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeywords();
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();

		// See if we have a favicon
		$favicon_file = $this->config->getAppPath() . 'view' . DIRECTORY_SEPARATOR . $this->config->get('theme') . DIRECTORY_SEPARATOR . 'favicon.ico';
		$this->data['favicon'] = file_exists($favicon_file) ? HTTPS_SERVER . $this->router->getBaseUrl() . 'favicon.ico' : '';

		// Rss feed
		$this->data['rss_feed'] = $this->router->link('feed/booking-rss', null, \Bitsand\SSL, true);
		$this->data['rss_feed_title'] = $this->document->getTitle() . ' Booking List';

		// Login
		$this->data['register'] = $this->router->link('user/register');
		$this->data['login'] = $this->router->link('user/login/login');

		// Navigation
		$this->data['navigation'] = array();
		$this->addNavigationItem($this->data['navigation'], 'common/home', 'Home');
		$this->addNavigationItem($this->data['navigation'], 'event/list', 'Event List');



		$this->setView('common/header');

		return $this->render();
	}

	private function addNavigationItem(&$arr, $route, $text) {
		$arr[] = array(
			'href'     => $this->router->link($route),
			'text'     => $text,
			'selected' => $this->router->currentRoute() == $route
		);
	}
}