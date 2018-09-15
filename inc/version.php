<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/version.php
 |    Summary: Provides basic information pertaining to the Bitsand project
 |             code base version being run
 |
 |     Author: Pete Allison
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/

/**
 * Holds the current version of Bitsand
 * @const string
 */
define('BITSAND_VERSION', '8.5');

class BitsandVersion {
	/**
	 * Gets the current code version
	 * @return string
	 */
	static function get() {
		return BITSAND_VERSION;
	}

	/**
	 * Checks if the current code version matches the passed version
	 * @param string $version
	 * @return boolean
	 */
	static function is($version) {
		return version_compare(BITSAND_VERSION, $version, '=');
	}

	/**
	 * Checks if the current code version is greater than the passed version
	 * @param string $version
	 * @return boolean
	 */
	static function greater($version) {
		return version_compare(BITSAND_VERSION, $version, '>');
	}

	/**
	 * Checks if the current code version is under the passed version
	 * @param string $version
	 * @return boolean
	 */
	static function under($version) {
		return version_compare(BITSAND_VERSION, $version, '<');
	}
}
