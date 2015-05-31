<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File register.php
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

//URL of file that contains details of systems that user details can be copied from
define('IMPORT_SYSTEMS_URL', 'https://cdn.rawgit.com/PeteAUK/bitsand/master/NON_WEB/systems');

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('register.php')) {
	$sProblem = '';
	$sEmail = SafeEmail ($_POST ['txtEmail']);
	//Check e-mail address is reasonable
	if (!eregi ("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]*)$", $sEmail))
		$sProblem .= htmlentities ($sEmail) . " is not a valid e-mail address<br>\n";
	//Generate password
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
	//Get salted hash of password
	$sHashPass = sha1 ($sNewPass . PW_SALT);
	//Check e-mail address is not already registered
	$sql = "SELECT plEmail FROM {$db_prefix}players WHERE plEmail " .
		"LIKE '" . ba_db_real_escape_string ($link, $sEmail) . "'";
	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows ($result) > 0)
		$sProblem .= "The e-mail address " . htmlentities ($sEmail) . " is already registered<br>\n";
	//If there are no problems, register user
	if ($sProblem == '') {
		//Set up INSERT SQL query
		$sql = "INSERT INTO {$db_prefix}players (plEmail, plPassword) VALUES ('" .
			ba_db_real_escape_string ($link, $sEmail) . "', '$sHashPass')";
		//Run query
		ba_db_query ($link, $sql);
		//E-mail user
		$sBody = "You are now registered at " . SYSTEM_NAME . ". " .
			"You can use the following details to log in:\n\n" .
			"E-mail: $sEmail\nPassword: $sNewPass\n\n" .
			"Once you are logged in, you will be able to change your password to something else.\n\n" . fnSystemURL ();

		ini_set("sendmail_from", EVENT_CONTACT_MAIL);
		$mail = mail ($sEmail, SYSTEM_NAME . ' - registered', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">", '-f'.EVENT_CONTACT_MAIL);
		if ($mail) {
			$msg = 'Registration successful. Please check your e-mail, and use the supplied password to log in';
		} else {
			$msg = 'Registration successful. Email sending failed, please contact the system admin for assistance';
		}

		//Make up URL & redirect to index.php with message
		$sURL = fnSystemURL () . 'index.php' . '?green=' . urlencode ($msg);
		header ("Location: $sURL");
	}
}

include ('inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Register</h1>

<p>
To register, enter your e-mail address below, then click <b>Register</b>. A randomly-generated password will be e-mailed to you, and you will then be able to use your e-mail address and the password to log in. If you do not get the e-mail, check your Junk/Spam folder - it may have been marked as spam (this appears to be particularly common with web-based e-mail services)<br>
<i>Note that because a password will be e-mailed to you, you must supply a valid e-mail address</i>.
</p>

<?php
//Report any problems
if ($sProblem != '')
	echo '<p class="warn">' , $sProblem , '</p>' , PHP_EOL;
?>

<form action="register.php" method="post" onsubmit="return fnCheck()">
  <table class="blockmid">
    <tr>
      <td>E-mail address:</td>
      <td><input name="txtEmail" type="email" class="text"></td>
    </tr>
    <tr>
      <td colspan="2">Please ensure that you have read and understood the <a href="terms.php" target="_blank">terms &amp; conditions</a></td>
    </tr>
    <tr>
      <td colspan="2" class="mid">
        <input type="submit" name="btnSubmit" value="Register">&nbsp;
        <input type = 'reset' value = "Reset form">
      </td>
    </tr>
  </table>
</form>

<h2>Already Registered</h2>

<p>Already registered? <a href="index.php">Login</a><br/>
Forgotten your password? <a href="retrieve.php">Get a new password</a></p>

<?php
// Get latest system list from Git, ensure we're not using the old SVN repo
if (strpos(IMPORT_SYSTEMS_URL, 'googlecode') === false) {
	$asSystems = file(IMPORT_SYSTEMS_URL, FILE_SKIP_EMPTY_LINES);
} else {
	$asSystems = file('https://cdn.rawgit.com/PeteAUK/bitsand/NON_WEB/systems', FILE_SKIP_EMPTY_LINES);
}

if ($asSystems === False)
	LogError ("Unable to get list of systems from " . SYSTEM_URLS);
else {
?>
<h2>Copy Details From Another System</h2>

<p>
If you are registered on another copy of Bitsand, simply select the system from the drop-down box below and enter your user name and password on that system. Your details will be copied over automatically.
</p>

<form action="import.php" method="post">
  <table class="blockmid">
    <tr>
      <td>System to copy from:</td>
      <td>
        <select name = "selSystem">
<?php
	foreach ($asSystems as $asSystemLine) {
		// Ignore comments
		if ($asSystemLine [0] != "#") {
			$aSystem = explode ("\t", trim ($asSystemLine));
			echo '          <option value="' , $aSystem [2] , '">' , $aSystem [0] , ' (OOC details only)</option>' , PHP_EOL;
		}
	}
?>
        </select>
      </td>
    </tr>
    <tr>
      <td>E-mail:</td>
      <td><input name="email" type="email" /></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="password" type="password" /></td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input name="ic" id="ic" type="checkbox" />&nbsp;
        <label for="ic">Tick to copy IC details</label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="mid">
        <input type="submit" name="btnSubmit" value="Copy Details" />&nbsp;
        <input type="reset" value="Reset form" />
      </td>
    </tr>
  </table>
</form>

<?php
} // end of "if $asSystems !== False" block

include ('inc/inc_foot.php');