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

include ('inc/inc_head_db.php');
include ('inc/inc_head_html.php');
$sWarn = '';
$sGreen = '';
$db_prefix = DB_PREFIX;

if ($_POST ['btnChangePass'] != '' && CheckReferrer ('change_password.php')) {
	//Check password is at least MIN_PASS_LEN characters, and both fields match
	if (strlen ($_POST ['txtPassword']) < MIN_PASS_LEN)
		$sWarn .= "The password must be at least " . MIN_PASS_LEN . " characters long<br>\n";
	if ($_POST ['txtPassword'] != $_POST ['txtPassword2'])
		$sWarn .= "The passwords entered did not match<br>\n";
	if ($sWarn == '') {
		//Get user's e-mail address
		$result = ba_db_query ($link, "SELECT plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID");
		$row = ba_db_fetch_assoc ($result);
		$sEmail = SafeEmail ($row ['plEmail']);
		//Run update query & set message
		$sHashPass = sha1 ($_POST ['txtPassword'] . PW_SALT);
		$sql = "UPDATE {$db_prefix}players SET plPassword = '$sHashPass' WHERE plPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		if ($result === False)
			$sWarn = "There was a problem updating your password";
		else {
			$sGreen = "Password reset. Next time you log in, you will have to use your new password";
			//E-mail user with new password
			if (SEND_PASSWORD) {
				$sBody = "You have changed your password at " . SYSTEM_NAME . ". " .
					"Your new details are below:\n\n" .
					"E-mail: $sEmail\nPassword: {$_POST [txtPassword]}\n" .
					"Player ID: " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "\n" .
					"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . fnSystemURL ();
				mail ($sEmail, SYSTEM_NAME . ' - password change', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
			}
		}
	}
}
if ($_POST ['btnChangeEmail'] != '' && CheckReferrer ('change_password.php')) {
	$sNewMail = ba_db_real_escape_string ($link, SafeEmail ($_POST ['txtEmail']));
	//Check requested e-mail address does not already exist in database
	$sql = "SELECT COUNT(*) AS cMail FROM {$db_prefix}players WHERE plEmail = '$sNewMail'";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	if ($row ['cMail'] != '0') {
		$sWarn = "The e-mail address $sNewMail is already registered";
	}
	else {
		//Get user's current e-mail address
		$result = ba_db_query ($link, "SELECT plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID");
		$row = ba_db_fetch_assoc ($result);
		//Run update query & set message
		$sCode = RandomString (10, 20);
		$sql = "UPDATE {$db_prefix}players SET plNewMail = '$sNewMail', plNewMailCode = '" .
			ba_db_real_escape_string ($link, $sCode) . "' " .
			"WHERE plPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		$sGreen = "A confirmation code has been sent to both your existing, and your new, e-mail addresses.<br>" .
			"Follow the instructions in the e-mail to confirm the change of e-mail address";
		//E-mail user with confirmation code and instructions
		$sBody = "A request has been received for your e-mail address to be changed at " . SYSTEM_NAME . ". " .
			"In order to make this change, you must log on to " . SYSTEM_NAME . " at " . fnSystemURL () .
			" using your existing e-mail address and password, then go to the 'Change password' page " .
			"and enter the code below:\n\nCode: $sCode\n\n" .
			"Note that the code must be entered *exactly* as above - it is probably easiest to copy and paste it.\n\n" .
			"If you have any problems, or questions, e-mail " . TECH_CONTACT_NAME . " at " . TECH_CONTACT_MAIL . "\n\n" .
			"Player ID: " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "\n" .
			"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . fnSystemURL ();
		mail ($row ['plEmail'], SYSTEM_NAME . ' - email change', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
		mail (SafeEmail ($_POST ['txtEmail']), SYSTEM_NAME . ' - email change', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	}
}
if ($_POST ['btnConfirm'] != '' && CheckReferrer ('change_password.php')) {
	//Get user's e-mail address
	$result = ba_db_query ($link, "SELECT plNewMail, plNewMailCode FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID");
	$row = ba_db_fetch_assoc ($result);
	if ($row ['plNewMailCode'] == $_POST ['txtCode']) {
		//Run update query & set message
		$sql = "UPDATE {$db_prefix}players SET plEmail = '" . SafeEmail ($row ['plNewMail']) . "', plNewMail = '', plNewMailCode = '' " .
			"WHERE plPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		if ($result === False)
			$sWarn = "There was a problem updating your e-mail address";
		else
			$sGreen = "Your e-mail address has been updated";
	}
}
if ($_POST ['btnUpdateEmailPreferences'] != '' && CheckReferrer ('change_password.php')) {
if ($_POST ['chkEmailOOCChange'] == 'on') { $bOOCChange = 1; } else { $bOOCChange = 0; }
if ($_POST ['chkEmailICChange'] == 'on') { $bICChange = 1; } else { $bICChange = 0; }
if ($_POST ['chkEmailPaymentReceived'] == 'on') { $bPaymentReceived = 1; } else { $bPaymentReceived = 0; }
if ($_POST ['chkEmailRemovedFromQueue'] == 'on') { $bRemovedFromQueue = 1; } else { $bRemovedFromQueue = 0; }

$sql = "UPDATE {$db_prefix}players SET ".
			"plEmailOOCChange = $bOOCChange, ".
			"plEmailICChange = $bICChange, ".
			"plEmailPaymentReceived = $bPaymentReceived, ".
			"plEmailRemovedFromQueue = $bRemovedFromQueue ".
			"WHERE plPlayerID = $PLAYER_ID";

		$result = ba_db_query ($link, $sql);
		if ($result === False)
			$sWarn = "There was a problem updating your e-mail preferences";
		else
			{
			$sGreen = "Your e-mail preferences have been updated";
			 $bEmailICChange = $bICChange;
			 $bEmailOOCChange = $bOOCChange;
			 $bEmailPaymentReceived = $bPaymentReceived;
			 $bEmailRemovedFromQueue = $bRemovedFromQueue;
			}
}
?>

<h1><?php echo TITLE?> - Manage Account</h1>

<p>
<?php
if ($sWarn != '')
	echo "<p class = 'warn'>\n$sWarn</p>\n";
if ($sGreen != '')
	echo "<p class = 'green'>\n$sGreen</p>\n";
?>

<p>
Manage your email preferences here
</p>
<form action='change_password.php' method = 'post'>
<table class='midtable'>
<tr><td>Email me when my OOC information changes</td><td><input type = 'checkbox' name='chkEmailOOCChange' <?php if ($bEmailOOCChange) { echo "checked ";} ?> /></td></tr>
<tr><td>Email me when my IC information changes</td><td><input type = 'checkbox' name='chkEmailICChange' <?php if ($bEmailICChange) { echo "checked ";} ?> /></td></tr>
<tr><td>Email me when my payment is received</td><td><input type = 'checkbox'  name='chkEmailPaymentReceived' <?php if ($bEmailPaymentReceived) { echo "checked ";} ?> /></td></tr>
<tr><td>Email me when my booking is removed from a queue</td><td><input type = 'checkbox'  name='chkEmailRemovedFromQueue' <?php if ($bEmailRemovedFromQueue) { echo "checked ";} ?> /></td></tr>
<tr><td>&nbsp;</td></tr>
<tr>
<td><input type = 'submit' name = 'btnUpdateEmailPreferences' value = 'Update Email Preferences'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>


<p><hr style = "width: 60ex"><p>

To change your password, enter a new password below. The new password must be at least <?php echo MIN_PASS_LEN?> characters long:<br>

<form action = 'change_password.php' method = 'post'>
<table class = 'midtable'>
<tr>
<td>New password:</td>
<td><input name = 'txtPassword' class = 'text' type = 'password'></td>
</tr>
<tr>
<td>Confirm new password:</td>
<td><input name = 'txtPassword2' class = 'text' type = 'password'></td>
</tr>
<tr><td>&nbsp;</td></tr><tr>
<td colspan = '2' class = 'mid'><input type = 'submit' name = 'btnChangePass' value = 'Change password'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>

<p><hr style = "width: 60ex"><p>

To change your e-mail address, enter a new address below. You will then be sent a confirmation code by e-mail.<br>
<i>Note that once you have confirmed this change, you will need to use the new e-mail address to login.</i><br>

<form action = 'change_password.php' method = 'post'>
<table class = 'midtable'>
<tr>
<td>New e-mail:</td>
<td><input name = 'txtEmail' class = 'text'></td>
</tr>
<tr><td>&nbsp;</td></tr><tr>
<td colspan = '2' class = 'mid'><input type = 'submit' name = 'btnChangeEmail' value = 'Change e-mail'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>

<p><hr style = "width: 60ex"><p>

If you have requested an e-mail change, and received a confirmation code, enter it in the box below to confirm the change:<br>

<form action = 'change_password.php' method = 'post'>
<table class = 'midtable'>
<tr>
<td>E-mail confirmation code:</td>
<td><input name = 'txtCode' class = 'text'></td>
</tr>
<tr><td>&nbsp;</td></tr><tr>
<td colspan = '2' class = 'mid'><input type = 'submit' name = 'btnConfirm' value = 'Confirm'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>

<?php
include ('inc/inc_foot.php');
?>
