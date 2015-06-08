<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Registry.php
 ||    Summary: The Registry is a global store that holds various objects
 ||             together in one place.  This ensures we only construct them
 ||             once.
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

namespace Bitsand;

const SSL = 1;
const NONSSL = 0;

class Registry {
	protected static $_registered = array();

	public static function set($item, $class, $overwrite = false) {
		if (!isset(self::$_registered[strtolower($item)]) || $overwrite) {
			self::$_registered[strtolower($item)] = new $class;
		}
		return self::$_registered[strtolower($item)];
	}

	public static function &get($item) {
		if (isset(self::$_registered[strtolower($item)])) {
			return self::$_registered[strtolower($item)];
		} else {
			return null;
		}
	}
}