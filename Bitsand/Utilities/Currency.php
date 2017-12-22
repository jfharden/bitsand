<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Currency.php
 ||    Summary: Simple routine for formatting currency
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

namespace Bitsand\Utilities;

use Bitsand\Config\Config;

class Currency {
	/**
	 * Holds the prefix for outputting currency
	 * @var string
	 */
	private $prefix = '£';

	public function __construct() {
		if (Config::hasVal('currency_prefix')) {
			$this->prefix = htmlentities(Config::get('currency_prefix'), ENT_QUOTES, 'UTF-8', true);
		}
	}

	public function format($value) {
		return $this->prefix . number_format((float)$value, 2, '.', '');
	}
}