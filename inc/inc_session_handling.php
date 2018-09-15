<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_session_handling.php
 |     Author: Jonathan Harden
 |  Copyright: (C) 2018 The Bitsand Project
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

session_start();
$PLAYER_ID = player_id_from_session();

function destroy_session() {
	global $PLAYER_ID;

	$_SESSION = array();

	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	session_destroy();

	session_start();

	$_SESSION['BA_PlayerID'] = 0;
	$PLAYER_ID = 0;
}

function player_id_from_session() {
	if (!is_player_id_in_session()) {
		$_SESSION['BA_PlayerID'] = 0;
		return 0;
	}

	return (int) $_SESSION['BA_PlayerID'];
}

function login_time_from_session() {
	return $_SESSION['BA_LoginTime'];
}

function is_player_id_in_session() {
	return (
		isset($_SESSION['BA_PlayerID']) &&
		is_numeric($_SESSION['BA_PlayerID']) &&
		$_SESSION['BA_PlayerID'] > 0
	);
}

function is_player_logged_in() {
	global $PLAYER_ID;

	return $PLAYER_ID > 0;
}

function set_session_login($logged_in_player_id, $login_time) {
	global $PLAYER_ID;

	$_SESSION['BA_PlayerID'] = $logged_in_player_id;
	$_SESSION['BA_LoginTime'] = $login_time;

	$PLAYER_ID = $logged_in_player_id;
}
