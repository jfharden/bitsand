<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_head_html.php
 |     Author: Jonathan Harden
 |  Copyright: (C) 2006 - 2018 The Bitsand Project
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

function isHttpsScheme() {
	// If HTTPS connection was terminated on a load balancer and forwarded onto an HTTP host
	if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		return true;
	}

	// If _SERVER['HTTPS'] hasn't been set then it's definitely an HTTP connection
	if (empty($_SERVER['HTTPS'])) {
		return false;
	}

	// nginx with php-fpm sets _SERVER['HTTPS'] to 'off' when an http connection is used
	if ($_SERVER['HTTPS'] != 'off') {
		return true;
	}

	return false;
}

function url_scheme() {
	return isHttpsScheme() ? "https" : "http";
}

?>
