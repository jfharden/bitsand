<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File admin/controller/common/footer.php
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

namespace Admin\Controller;

use Bitsand\Controllers\Controller;
use Bitsand\Utilities\GitRepository;

class CommonFooter extends Controller {
	public function index() {
		$this->setView('common/footer');

		$this->data['scripts'] = $this->document->getScripts(false);

		$this->data['link_download'] = $this->router->link('common/download');

		//$git_repository = new GitRepository($this->config->getVal('git_repository'));
		$this->data['version'] = 'v' . BITSAND_VERSION;

		return $this->render();
	}
}