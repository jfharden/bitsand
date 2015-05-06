<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2014 The Bitsand Project (http://bitsand.googlecode.com/)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Get access level for logged-in user
$sql = "SELECT plAccess FROM " . DB_PREFIX . "players WHERE plPlayerID = $PLAYER_ID";
LogWarning ("SQL to check player is admin:\n$sql");
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);

//Redirect to start page if user is not an admin
//Note that root user is also an admin
$inc_admin_log = "Checking user is an admin\n";
$inc_admin_log .= "ROOT_USER_ID: " . ROOT_USER_ID . "\n";
$inc_admin_log .= '$PLAYER_ID: ' . "$PLAYER_ID\n";
$inc_admin_log .= '$row ["plAccess"] : ' . $row ['plAccess'] . "\n";
if (ROOT_USER_ID == $PLAYER_ID && $PLAYER_ID != 0)
	$inc_admin_log .= "User is root\n";
elseif ($row ['plAccess'] == 'admin')
	$inc_admin_log .= "User is an admin\n";
else
	$inc_admin_log .= "User is NOT an admin\n";
LogWarning ($inc_admin_log);

if (ROOT_USER_ID != $PLAYER_ID && $row ['plAccess'] != 'admin') {
	LogWarning ("Player ID $PLAYER_ID tried to access an admin-only page (" .
		basename ($_SERVER ["SCRIPT_FILENAME"]) . ")\n");
	//Make up URL & redirect
	$sURL = SYSTEM_URL . 'start.php?warn=' . urlencode ('You do not have permission to access that page');
	header ("Location: $sURL");
}

//If this script is included, then the page is an admin page. Set CSS prefix
$CSS_PREFIX = '../';

?>
