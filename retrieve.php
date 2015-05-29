<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File retrieve.php
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

//Do not need login check for this page
$bLoginCheck = False;
include ('inc/inc_head_db.php');
include ('inc/inc_head_html.php');

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('retrieve.php')) {
	$sEmail = SafeEmail ($_POST ['txtEmail']);
	//Generate new password, update the players table, e-mail the password
	$sNewPass = '';
	//New password length will be up to 2x as long as minimum length specified in config file
	$iLen = rand (MIN_PASS_LEN, MIN_PASS_LEN * 2);
	for ($iPos = 1; $iPos <= $iLen; $iPos++)
		switch (rand (1, 3)) {
			case 1:
				$sNewPass .= chr (rand (48, 57));
				break;
			case 2:
				$sNewPass .= chr (rand (65, 90));
				break;
			case 3:
				$sNewPass .= chr (rand (97, 122));
				break;
		}
	//Get salted hash of new password and run UPDATE query
	$sHashPass = sha1 ($sNewPass . PW_SALT);
	$sql = "UPDATE " . DB_PREFIX . "players SET plPassword = '$sHashPass', plLoginCounter = 0 " .
		"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
	$result = ba_db_query ($link, $sql);
	if (ba_db_affected_rows ($link) == 0)
		//No changes made.
		$sMsg = 'E-mail not found. Password not reset. Please check and try again';
	else {
		//Send e-mail
		$sTo = $sEmail;
		$sSubject = SYSTEM_NAME . " - password reset";
		$sBody = "Hi,\nYour password at " . SYSTEM_NAME . " has been reset. " .
			"Your new password is:\n$sNewPass\nYou can log in using this new password.\n\n" . fnSystemURL ();

		ini_set("sendmail_from", EVENT_CONTACT_MAIL);
		$mail = mail ($sTo, $sSubject, $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">", '-f'.EVENT_CONTACT_MAIL);
		if ($mail) {
			$sMsg = "A new password has been sent to $sEmail. Please check your e-mail for your new password.<br />\n" .
			"If you do not get the e-mail, check your Junk/Spam folder - it may have been marked as spam " .
			"(this appears to be particularly common with web-based e-mail services)";
		}
		else
		{
			$sMsg = "There was an error sending your reset email. Please contact <a href = 'mailto:" .
			Obfuscate (TECH_CONTACT_MAIL) . "'>" . TECH_CONTACT_NAME . "</a> to reset your password manually";
		}
	}
	if (ba_db_affected_rows ($link) > 1)
		//More than one record updated - log warning
		LogWarning ("retrieve.php - Multiple records updated from SQL query\n$sql");
}

?>


<h1><?php echo TITLE?> - Lost Password</h1>

<?php
if ($sMsg != '')
	echo "<p class = 'green'>$sMsg</p>\n";
?>
<p>
If you have forgotten your password, enter your e-mail address below and click the &quot;Get new password&quot; button. A new password will then be e-mailed to you.
</p>

<form action = 'retrieve.php' method = 'post'>
<table class = 'blockmid'>
<tr>
<td>E-mail address:</td>
<td><input name = 'txtEmail' class = 'text'></td>
</tr><tr><td>&nbsp;</td></tr><tr>
<td colspan = '2' class = 'mid'><input type = 'submit' name = 'btnSubmit' value = 'Get new password'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>

<ul>
<li>Not registered? <a href = "register.php">Register</a></li>
<li><a href = "index.php">Login page</a></li>
</ul>

<?php
include ('inc/inc_foot.php');