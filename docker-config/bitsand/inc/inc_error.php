<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_error.php
 |     Author: Russell Phillips
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

//inc_error.php - error handling/logging routines
if (DEBUG_MODE)
	//Report all errors except E_NOTICE - use for debugging
	error_reporting (E_ALL ^ E_NOTICE);
else
	//Turn off all error reporting
	error_reporting (0);

//Log an error. $sMsg is text to write to file
function LogError ($sMsg) {
	if (ERROR_LOG) {
		$sWrite = date ('d M Y H:i:s') . "\n$sMsg\n------------\n";
		error_log($sWrite, 0);
	}
}

//Log a warning. $sMsg is text to write to file
function LogWarning ($sMsg) {
	if (WARNING_LOG) {
		$sWrite = date ('d M Y H:i:s') . "\n$sMsg\n------------\n";
		error_log($sWrite, 0);
	}
}
