<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand\Utilities\Functions.php
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

namespace Bitsand\Utilities\Functions;

/**
 * Makes the first character of the passed string uppercase.  This should be
 * used in conjunction with a preg_replace_callback for camel casing strings
 * such as used within Bitsand\Controllers\ActionRoute.
 * @param array $arr
 * @return string
 */
function ucfirst_array($arr) {
	return strtoupper($arr[1]);
}

/**
 * Utilises a global variable function for quickly outputing an html encoded
 * string to the screen.  This is available primarily for use within the code
 * of the various .phtml view pages.
 */
global $_;
$_ = (function($string) {
	echo htmlentities($string, ENT_QUOTES, 'UTF-8', true);
});

/**
 * This function performs a "safe" include of the passed file, this is useful
 * as it prevents (breaks) the object scope inheritance, ensuring the
 * Controller methods aren't available for the template files.  This in turn
 * forces good practice of making templates "dumb".
 * @param string $file
 * @param array $data
 */
function safeinclude($file, $data) {
	global $_;
	extract($data);
	include($file);
}