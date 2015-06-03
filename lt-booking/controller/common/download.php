<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/controller/common/download.php
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

class CommonDownload extends Controller {
	public function index() {
		$this->document->setTitle('Download Bitsand');

		$this->data['link_repository'] = $this->config->getVal('git_repository');
		$this->data['link_issues'] = $this->config->getVal('git_repository') . 'issues/';
		$this->data['link_license'] = 'http://www.gnu.org/licenses/gpl-3.0.en.html';

		$this->data['version'] = 'v' . BITSAND_VERSION;

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->setView('common/download');

		$this->view->setOutput($this->render());
	}
}