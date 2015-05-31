<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/get_info.php
 |    Summary: Retrieves basic information on the version of PHP, MySQL and
 |             bitsand being run.
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

include('../inc/version.php');

// Retrieve the MySQL version
if (function_exists('mysql_get_server_info')) {
	$sql_version = mysql_get_server_info();
} elseif (function_exists('mysqli_get_server_info')) {
	$sql_version = mysqli_get_server_info();
} else {
	$sql_version = '0';
}

$data = array(
	'bitsand' => BitsandVersion::get(),
	'php'  => PHP_VERSION,
	'mysql'  => $sql_version
);

/*
 * We need to obfuscicate this data so that if the file is "chanced" upon then
 * we aren't giving out easily readable versions and with them any
 * vulnerabilities that exist.  We use json_encode rather than serialize
 * because serialize can be replaced and as such may prevent cross-server
 * sharing of this data.  We then base64 encode it into a plain illegible
 * string.
 */
$data = json_encode($data);
echo base64_encode($data);