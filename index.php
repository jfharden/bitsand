<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File index.php
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

//Do not check that user is logged in
$bLoginCheck = False;

include ('inc/inc_head_db.php');
$sMessage = '';

function is_email_or_password_empty() {
	// Explicitly disallow logging in with a blank email address field
	if (!array_key_exists('txtEmail', $_POST)) {
		return "You must enter an email address to try and log in";
	}

	$match = preg_match("/^\s*$/", $_POST['txtEmail']);
	if ($match === FALSE || $match === 1) {
		return "You must enter an email address to try and log in";
	}

	// Explicitly disallow logging in with a blank email address field
	if (!array_key_exists('txtPassword', $_POST)) {
		return "You must enter a password to try and log in";
	}
	$match = preg_match("/^\s*$/", $_POST['txtPassword']);
	if ($match === FALSE || $match === 1) {
		return "You must enter a password to try and log in";
	}

	return false;
}

$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmit'] != '' && !is_email_or_password_empty()) {
	//User is logging in
	$sEmail = SafeEmail ($_POST ['txtEmail']);
	//Work out which salt to use
	$sql = "SELECT plPlayerID, plOldSalt FROM {$db_prefix}players WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	$UseOldSalt = $row ['plOldSalt'];
	//Get SHA-1 hash of password using appropriate salt
	if ($UseOldSalt == 1)
		$sPass = sha1 ($_POST ['txtPassword'] . OLD_PW_SALT);
	else
		$sPass = sha1 ($_POST ['txtPassword'] . PW_SALT);
	//Set up & run query
	$sql = "SELECT plPlayerID FROM {$db_prefix}players " .
		"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) .
		"' AND plPassword = '$sPass'";
	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows ($result) > 1)
		//Log warning if there was more than one row returned
		LogWarning ("index.php - more than one result from e-mail and password\n$sql");
	//If rows returned, set cookies & redirect to start page
	if (ba_db_num_rows ($result) > 0) {
		//Successfully logged in. If using old salt, update password & plOldSalt
		if ($UseOldSalt == 1) {
			$sPass = sha1 ($_POST ['txtPassword'] . PW_SALT);
			$sql = "UPDATE {$db_prefix}players SET plPassword = '$sPass', plOldSalt = 0 " .
				"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
			if (ba_db_query ($link, $sql) == False)
				LogError ("Player logged in using old password salt. Unable to update plPassword and plOldSalt.\nSQL:\n{$sql}");
		}
		//Store player ID & login time in cookies
		$row = ba_db_fetch_assoc ($result);
		$sErr = '';
		//Get values to store in cookies & sessions table
		$iPlayerID = $row ['plPlayerID'];
		//Record login date
		$sql = "UPDATE {$db_prefix}players SET plLastLogin = '" . date ('Y-m-d') . "' WHERE plPlayerID = $iPlayerID";
		if (ba_db_query ($link, $sql) == False)
			LogError ("Unable to record login date for user ID $iPlayerID.\nSQL:\n{$sql}");
		//Random string is appended to login time (down to microsecond), then combined string is hashed.
		//Hash is stored in sessions table and in cookie
		$sLoginTime = sha1 (microtime () . RandomString (10, 20));
		$iLastAccess = time ();
		//Set cookies
		set_session_login($iPlayerID, $sLoginTime);
		if ($sErr == '') {
			//Cookies set OK. Reset login counter
			$sql = "UPDATE {$db_prefix}players SET plLoginCounter = 0 WHERE plPlayerID = $iPlayerID";
			ba_db_query ($link, $sql);
			//Store details in sessions table then redirect to start page
			$sql = "SELECT ssPlayerID FROM {$db_prefix}sessions WHERE ssPlayerID = $iPlayerID";
			$result = ba_db_query ($link, $sql);
			//Only store first two octets of remote IP to avoid issue with dial-up etc (see issue 170)
			$aIP = explode (".", $_SERVER ['REMOTE_ADDR']);
			$sIP = ba_db_real_escape_string ($link, $aIP [0] . "." . $aIP [1]);
			//Insert new record if no rows returned, otherwise update existing row
			if (ba_db_num_rows ($result) == 0) {
				$sql = "INSERT INTO {$db_prefix}sessions (ssPlayerID, ssLoginTime, ssIP, ssLastAccess) " .
					"VALUES ($iPlayerID, '$sLoginTime', '$sIP', $iLastAccess)";
			}
			else {
				$sql = "UPDATE {$db_prefix}sessions " .
					"SET ssLoginTime = '$sLoginTime', ssIP = '$sIP', ssLastAccess = $iLastAccess " .
					"WHERE ssPlayerID = $iPlayerID";
			}
			//Run query to update/insert session, then redirect to start page
			ba_db_query ($link, $sql);

			header ("Location: ". fnSystemURL() ."start.php");		}
		else
			//Problem setting cookies. Append error message
			$sMessage .= $sErr;
	}
	else {
		//No rows returned. E-mail or password must be wrong
		if ($sMessage != '')
			$sMessage .= "<br>\n";
		$sMessage .= "Wrong e-mail/password. Please try again";
		//Increment login count and store in players table
		$sql = "SELECT plPassword, plLoginCounter FROM {$db_prefix}players " .
			"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
		$result = ba_db_query ($link, $sql);
		$row = ba_db_fetch_assoc ($result);
		$iLoginCounter = $row ['plLoginCounter'];
		$sql = "UPDATE {$db_prefix}players SET plLoginCounter = " . ++$iLoginCounter . " " .
			"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
		//Log failed login attempt
		$sLogWarn = "Failed login attempt\nE-mail: $sEmail\n" .
			"Attempt was made from IP address {$_SERVER ['REMOTE_ADDR']}";
		LogWarning ($sLogWarn);

		//Check for too many failed logins
		if ($iLoginCounter > LOGIN_TRIES && $row ['plPassword'] != 'ACCOUNT DISABLED') {
			//Change SQL query so that plPassword and plLoginCounter are both updated
			$sql = "UPDATE {$db_prefix}players SET plPassword = 'ACCOUNT DISABLED', plLoginCounter = " . $iLoginCounter .
				" WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
			$sMessage = "You have entered an incorrect password too many times. Your account has been disabled.<br>" .
				"An e-mail has been sent to your e-mail address with instructions on how to re-enable your account.";
			//E-mail user
			$sBody = "This is an automated message from " . SYSTEM_NAME . ". Your account has been disabled, because " .
				"an incorrect password was entered too many times. You can re-enable your account by resetting your " .
				"password (Follow the 'Get a new password' link from the front page). If you have any problems, " .
				"please contact " . TECH_CONTACT_NAME . " at " .
				TECH_CONTACT_MAIL . " to have your account re-enabled.\n\n" . fnSystemURL ();
			mail ($sEmail, SYSTEM_NAME . ' - account disabled', $sBody, "From:" . SYSTEM_NAME . " <" . TECH_CONTACT_MAIL . ">");
			//E-mail admin and log a warning
			$sBody = "Account with e-mail address $sEmail has been disabled, after too many failed login attempts.\n" .
				"Latest attempt was from IP address {$_SERVER ['REMOTE_ADDR']}\n" .
				"An e-mail has been sent to the user.\n\n" . fnSystemURL ();
			mail (TECH_CONTACT_MAIL, SYSTEM_NAME . ' - account disabled', $sBody, "From:" . SYSTEM_NAME . " <" . TECH_CONTACT_MAIL . ">");
			LogWarning ($sBody);
		}
		elseif ($row ['plPassword'] == 'ACCOUNT DISABLED')
			//Account has been previously disabled. Just display message - do not send e-mail
			$sMessage = "Your account has been disabled. To re-enable it, either <a href = 'retrieve.php'>request a new password</a>" .
				" or e-mail " . TECH_CONTACT_NAME . ", using the link below";
		//Run query to update plLoginCounter (and plPassword, if account is being disabled)
		ba_db_query ($link, $sql) . $sql;
	}
}
elseif ($_POST ['btnSubmit'] != '') {
	// Attempt to login with no email or password
	$sMessage = is_email_or_password_empty();
}
else {
	destroy_session();
}
include ('inc/inc_head_html.php');
?>

<h1><?php echo TITLE?></h1>

<?php echo ANNOUNCEMENT_MESSAGE; ?>

<p>
To log in, enter your e-mail address and password below, then click <b>Login</b>.<br>
Note that this site uses cookies to handle logins, so you must have cookies enabled in order to login. The cookies will be deleted when you close the browser.
</p>
<?php
//Display message if one was included in URL
if ($_GET ['green'] != '')
	echo "<p class = 'green'>" . htmlentities ($_GET ['green']) . "</p>\n";
if ($_GET ['warn'] != '' || $sMessage != '')
	echo "<p class = 'warn'>" . htmlentities ($_GET ['warn']) . $sMessage . "</p>\n";
?>

<form action="index.php" method="post">
<table class="blockmid">
<tr>
<td>E-mail address:</td>
<td><input name="txtEmail" type="email" class="text"<?php if (isset($sEmail) && !empty($sEmail)) echo ' value="' , $sEmail , '"'; ?>></td>
</tr><tr>
<td>Password:</td>
<td><input name="txtPassword" type="password" class="text"></td>
</tr><tr>
<td colspan="2" class="mid"><input type="submit" name="btnSubmit" value="Login">&nbsp;<input type="reset" value="Reset form"></td>
</tr>
</table>
</form>

<ul>
<li>Not registered? <a href = "register.php">Register</a></li>
<li>Forgotten your password? <a href = "retrieve.php">Get a new password</a></li>
<li>Please ensure that you have read and understood the <a href = "terms.php">terms &amp; conditions</a></li>
<?php
echo "<li>Problem? See the <a href = 'faq.php'>FAQ</a> or <a href = 'mailto:" .
	Obfuscate (EVENT_CONTACT_MAIL) . "'>E-mail " . EVENT_CONTACT_NAME . "</a> with event queries, <a href = 'mailto:" .
	Obfuscate (TECH_CONTACT_MAIL) . "'>E-mail " . TECH_CONTACT_NAME . "</a> with web site problems.</li>\n";
?>
<li><a href='iCalendar.php'>iCalendar feed of events</a></li>
</ul>

<?php
include ('inc/inc_foot.php');
