<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_ba_db.php
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

/*
All database connections are handled through the functions in this file. This allows
for some level of database-independence. The type of database to use is set by the
DB_TYPE constant. The function names are (where relevant) the same as their mysqli
counterparts, but with "ba_db" in place of "mysqli"
*/

//Log an error. $sErr is a descriptive error message
function ba_db_log_error ($sErr) {
	$sErr .= "\nFile name: {$_SERVER['SCRIPT_FILENAME']}";
	$sErr .= "\nPlayer ID: $PLAYER_ID";
	LogError ($sErr);
}

//Open link to database. Returns MySQL link identifier
function ba_db_connect () {
	if (DB_TYPE == 'mysqli') {
		$link = mysqli_connect (DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($link === False)
			ba_db_log_error ("Could not connect to database.\nMySQL error number: " .
				mysqli_connect_errno () . "\nMySQL error description: " . mysqli_connect_error ());
	}
	if (DB_TYPE == 'mysql') {
		$link = mysql_connect (DB_HOST, DB_USER, DB_PASS);
		if (!$link)
			ba_db_log_error ("Could not connect to database using mysql_connect ()");
		else
			if (mysql_select_db (DB_NAME, $link) == False)
				ba_db_log_error ("Could not select database");
	}
	return $link;
}

function ba_db_close ($link) {
	if (DB_TYPE == 'mysqli')
		if (mysqli_close ($link) == False)
			ba_db_log_error ("Could not close database connection");
	if (DB_TYPE == 'mysql')
		if (mysql_close ($link) == False)
			ba_db_log_error ("Could not close database connection");
}

function ba_db_query ($link, $sQuery) {
	if (DB_TYPE == 'mysqli')
		$retval = mysqli_query ($link, $sQuery);
	if (DB_TYPE == 'mysql')
		$retval = mysql_query ($sQuery, $link);
	if ($retval === False) {
		$err = ("Error running query\nSQL:\n$sQuery\n");
		if (DB_TYPE == 'mysqli') {
			$err .= "MySQL error number: " . mysqli_errno ($link) . "\n";
			$err .= "MySQL error description: " . mysqli_error ($link) . "\n";
		}
		if (DB_TYPE == 'mysql') {
			$err .= "MySQL error number: " . mysql_errno ($link) . "\n";
			$err .= "MySQL error description: " . mysql_error ($link) . "\n";
		}
		ba_db_log_error ($err);
	}
	return $retval;
}

function ba_db_num_rows ($result) {
	if (DB_TYPE == 'mysqli')
		return mysqli_num_rows ($result);
	if (DB_TYPE == 'mysql')
		return mysql_num_rows ($result);
}

function ba_db_fetch_assoc ($result) {
	if (DB_TYPE == 'mysqli')
		return mysqli_fetch_assoc ($result);
	if (DB_TYPE == 'mysql')
		return mysql_fetch_assoc ($result);
}

function ba_db_fetch_row ($result) {
	if (DB_TYPE == 'mysqli')
		return mysqli_fetch_row ($result);
	if (DB_TYPE == 'mysql')
		return mysql_fetch_row ($result);
}

function ba_db_real_escape_string ($link, $string) {
	if (DB_TYPE == 'mysqli')
		return mysqli_real_escape_string ($link, $string);
	if (DB_TYPE == 'mysql')
		return mysql_real_escape_string ($string, $link);
}

function ba_db_affected_rows ($link) {
	if (DB_TYPE == 'mysqli')
		return mysqli_affected_rows ($link);
	if (DB_TYPE == 'mysql')
		return mysql_affected_rows ($link);
}

function ba_insert_id($link)
{
	if (DB_TYPE == 'mysqli')
		return mysqli_insert_id ($link);
	if (DB_TYPE == 'mysql')
		return mysql_insert_id ($link);
}
?>
