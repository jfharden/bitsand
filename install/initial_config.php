<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2012 The Bitsand Project (http://bitsand.googlecode.com/)

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

//Do not check that user is logged in
$bLoginCheck = False;
//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_forms.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

if ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] == CRYPT_KEY && CheckReferrer ('initial_config.php')) {
	//Set up update query to change config values
	$updateQuery = "UPDATE `{$db_prefix}config` SET ";
	$updateQuery.= "cnEVENT_CONTACT_NAME = '".ba_db_real_escape_string($link, $_POST ['txtEVENT_CONTACT_NAME'])."', ";
	$updateQuery.= "cnEVENT_CONTACT_MAIL = '".ba_db_real_escape_string($link, $_POST ['txtEVENT_CONTACT_MAIL'])."', ";
	$updateQuery.= "cnTECH_CONTACT_NAME = '".ba_db_real_escape_string($link, $_POST ['txtTECH_CONTACT_NAME'])."', ";
	$updateQuery.= "cnTECH_CONTACT_MAIL = '".ba_db_real_escape_string($link, $_POST ['txtTECH_CONTACT_MAIL'])."', ";
	$updateQuery.= "cnTITLE = '".ba_db_real_escape_string($link, $_POST ['txtTITLE'])."', ";
	$updateQuery.= "cnSYSTEM_NAME = '".ba_db_real_escape_string($link, $_POST ['txtSYSTEM_NAME'])."', ";
	$updateQuery.= "cnMIN_PASS_LEN = ".ba_db_real_escape_string($link, (int) $_POST ['txtMIN_PASS_LEN']).", ";
	$updateQuery.= "cnSEND_PASSWORD = ".setBoolValue($_POST ['chkSEND_PASSWORD']);

	//Get root's e-mail address
	$sql = "SELECT plEmail FROM {$db_prefix}players WHERE plPlayerID = " . ROOT_USER_ID;
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	$root_email = $row ['plEmail'];

	if (! ba_db_query ($link, $updateQuery)) {
		$sWarn = "There was a problem updating the config details";
		LogError ("There was a problem updating the config details. Admin ID: $PLAYER_ID");
		//E-mail root
		$subject = SYSTEM_NAME . " - Error updating config details";
		$body = "Someone tried to change the config details, but an error was encountered. See the log for more details";
		mail ($root_email, $subject, $body, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	}
	else {
		$sMessage = "The config settings have been successfully updated.";
		//E-mail root
		$subject = SYSTEM_NAME . " - Config details updated";
		$body = "The config details have been changed";
		mail ($root_email, $subject, $body, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	}

}
elseif ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] != CRYPT_KEY)
	$sWarn = "The value given for CRYPT_KEY was wrong. Settings not updated";

//Get config information from database
$sql = "SELECT * FROM {$db_prefix}config WHERE cnName = 'Default' ";

$result = ba_db_query ($link, $sql);
if (ba_db_num_rows($result) == 1)
{
	$row = ba_db_fetch_assoc ($result);
}
else
{
$sWarn = "Could not find config information in database";
}
?>

<h1><?php echo htmlspecialchars($row['cnTITLE']); ?> - Edit config settings</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>" . $sWarn . "</p>";
if ($sMessage !='')
	echo "<p class = 'green'>" . $sMessage . "</p>";
?>

<p>
Initial settings can be configured here. To configure other settings, log in as an admin or root user.
</p>

<p>
<form action = "initial_config.php" method = "post">
<table>
<tr><td><?php HelpLink ('help_config_login.php'); ?> MIN_PASS_LEN:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnMIN_PASS_LEN']); ?>" name="txtMIN_PASS_LEN" /></td></tr>
<tr><td><?php HelpLink ('help_config_login.php'); ?> SEND_PASSWORD:</td><td><input type="checkbox" <?php if ($row['cnSEND_PASSWORD']) {echo " checked";} ?> name="chkSEND_PASSWORD" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> SYSTEM_NAME:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnSYSTEM_NAME']); ?>" name="txtSYSTEM_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TITLE:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnTITLE']); ?>" name="txtTITLE" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> EVENT_CONTACT_NAME:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnEVENT_CONTACT_NAME']); ?>" name="txtEVENT_CONTACT_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> EVENT_CONTACT_MAIL:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnEVENT_CONTACT_MAIL']); ?>" name="txtEVENT_CONTACT_MAIL" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TECH_CONTACT_NAME:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnTECH_CONTACT_NAME']); ?>" name="txtTECH_CONTACT_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TECH_CONTACT_MAIL:</td><td><input type="text" value="<?php echo htmlspecialchars($row['cnTECH_CONTACT_MAIL']); ?>" name="txtTECH_CONTACT_MAIL" /></td></tr>

<tr><td>Value of CRYPT_KEY in configuration file:</td><td><input type = 'password' name = 'txtKey'></td></tr>

<tr><td colspan = "2" class = "mid"><input type = 'submit' value = 'Update values' name = 'btnSubmit'></td></tr>
</table>
</form>

</p>

<p><a href = "./">Installation Tests &amp; Tools</a></p>
<?php
include ('../inc/inc_foot.php');
?>
