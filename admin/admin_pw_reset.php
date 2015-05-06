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

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_forms.php');
//Initialise warning string
$sWarn = '';
$admin_player_id = (int) $_GET ['pid'];

$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmit'] != '') {
	if ($_POST ['txtPassword1'] != $_POST ['txtPassword2'])
		$sWarn = "Passwords do not match<br>\n";
	//Check password length
	if (strlen ($_POST ['txtPassword1']) < MIN_PASS_LEN)
		$sWarn .= "Password must be at least " . MIN_PASS_LEN . " characters long<br>\n";
	if ($sWarn == '') {
		//Set up UPDATE query
		$sHashPass = sha1 ($_POST ['txtPassword1'] . PW_SALT);
		$sql = "UPDATE {$db_prefix}players SET plPassword = '$sHashPass', plLoginCounter = 0 " .
			"WHERE plPlayerID = $admin_player_id";

		//Run UPDATE query
		if (ba_db_query ($link, $sql)) {
			//Query should affect exactly one row. Log a warning if it affected more
			if (ba_db_affected_rows ($link) > 1)
				LogWarning ("More than one row updated during password reset (admin_pw_reset.php). Player ID: $admin_player_id");
			//Get user's e-mail address
			$result = ba_db_query ($link, "SELECT plEmail FROM {$db_prefix}players WHERE plPlayerID = $admin_player_id");
			$row = ba_db_fetch_assoc ($result);
			$sEmail = $row ['plEmail'];
			if (SEND_PASSWORD) {
				//E-mail user with new password
				$sBody = "Your password for " . SYSTEM_NAME . " has been changed. " .
					"Your new details are below:\n\n" .
					"E-mail: $sEmail\nPassword: {$_POST [txtPassword1]}\n" .
					"Player ID: " . PID_PREFIX . sprintf ('%03s', $admin_player_id) . "\n" .
					"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . fnSystemURL ();
				mail ($sEmail, SYSTEM_NAME . ' - password change', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
			}
		}
		else {
			$sWarn = "There was a problem resetting the password<br>\n";
			LogError ("Error updating OOC information (admin_pw_reset.php). Player ID: $admin_player_id");
		}

		//Redirect to start page
		$sURL = fnSystemURL () . '../start.php?green=' . urlencode ('Password has been reset, and account enabled, for player ID ' .
			PID_PREFIX . sprintf ('%03s', $admin_player_id));
		if (SEND_PASSWORD)
			$sURL .= '. The new password has been e-mailed to the player';
		header ("Location: $sURL");
	}
}

include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Password Reset</h1>

<h2>Reset Password for Player ID <?php echo PID_PREFIX . sprintf ('%03s', $admin_player_id)?></h2>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>";
?>

<form action = 'admin_pw_reset.php?pid=<?php echo $admin_player_id?>' method = 'post'>

Note that the password must be at least <?php echo MIN_PASS_LEN?> characters long.

<table><tr>
<td>New password:</td>
<td><input type = "password" name = "txtPassword1"></td>
</tr><tr>
<td>Repeat:</td>
<td><input type = "password" name = "txtPassword2"></td>
</tr><tr>
<td colspan = '2'>&nbsp;</td>
</tr><tr>
<td class = 'mid'><input type = 'submit' value = "Submit" name = "btnSubmit"></td>
<td class = 'mid'><input type = 'reset' value = "Reset form"></td></tr>
</table>

</form>

<?php
include ('../inc/inc_foot.php');
?>
