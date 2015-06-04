<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Loader.php
 ||    Summary: Handles loading classes on the fly
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

namespace Bitsand\Controllers;

use Bitsand\Registry;
use Bitsand\Config\Config;

class Loader {
	/**
	 * Loads a model into the registry and sets it to be available using
	 * $this->model_nameing_using_underscores.
	 * @param string $model
	 * @return mixed
	 */
	public function model($model) {
		$class = preg_replace('/[^a-zA-Z0-9]/', '', ucwords(str_replace(array('_', '/'), ' ', $model)));

		return Registry::set('model_' . str_replace(array('/', '-'), '_', $model), str_replace('/','\\',Config::getVal('namespace') . '/Model/' . $class));
	}
}