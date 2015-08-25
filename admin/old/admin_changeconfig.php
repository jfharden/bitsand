<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_changeconfig.php
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

//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_admin.php');
include ($CSS_PREFIX . 'inc/inc_forms.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_changeconfig.php')) {
	//Get old values
	$sql = "SELECT * FROM {$db_prefix}config WHERE cnName = 'Default' ";
	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows ($result) == 1)
		$oldconfig = ba_db_fetch_assoc ($result);
	else
		$sWarn = "Could not find config information in database";

	//Set up update query to change config values
	$updateQuery = "UPDATE `{$db_prefix}config` SET ";
	$updateQuery.= "cnANNOUNCEMENT_MESSAGE = '".ba_db_real_escape_string($link, $_POST ['txtANNOUNCEMENT_MESSAGE'])."', ";
	$updateQuery.= "cnDISCLAIMER_TEXT = '".ba_db_real_escape_string($link, $_POST ['txtDISCLAIMER_TEXT'])."', ";
	$updateQuery.= "cnEVENT_CONTACT_NAME = '".ba_db_real_escape_string($link, $_POST ['txtEVENT_CONTACT_NAME'])."', ";
	$updateQuery.= "cnEVENT_CONTACT_MAIL = '".ba_db_real_escape_string($link, $_POST ['txtEVENT_CONTACT_MAIL'])."', ";
	$updateQuery.= "cnTECH_CONTACT_NAME = '".ba_db_real_escape_string($link, $_POST ['txtTECH_CONTACT_NAME'])."', ";
	$updateQuery.= "cnTECH_CONTACT_MAIL = '".ba_db_real_escape_string($link, $_POST ['txtTECH_CONTACT_MAIL'])."', ";
	$updateQuery.= "cnTITLE = '".ba_db_real_escape_string($link, $_POST ['txtTITLE'])."', ";
	$updateQuery.= "cnSYSTEM_NAME = '".ba_db_real_escape_string($link, $_POST ['txtSYSTEM_NAME'])."', ";
	$updateQuery.= "cnBOOKING_FORM_FILE_NAME = '".ba_db_real_escape_string($link, $_POST ['txtBOOKING_FORM_FILE_NAME'])."', ";
	$updateQuery.= "cnBOOKING_LIST_IF_LOGGED_IN = ".setBoolValue($_POST ['chkBOOKING_LIST_IF_LOGGED_IN']).", ";
	$updateQuery.= "cnLIST_GROUPS_LABEL = '".ba_db_real_escape_string($link, $_POST ['txtLIST_GROUPS_LABEL'])."', ";
	$updateQuery.= "cnLOCATIONS_LABEL = '".ba_db_real_escape_string($link, $_POST ['txtLOCATIONS_LABEL'])."', ";
	$updateQuery.= "cnANCESTOR_DROPDOWN = ".setBoolValue($_POST ['chkANCESTOR_DROPDOWN']).", ";
	$updateQuery.= "cnDEFAULT_FACTION = '".ba_db_real_escape_string($link, $_POST ['selDEFAULT_FACTION'])."', ";
	$updateQuery.= "cnNON_DEFAULT_FACTION_NOTES = ".setBoolValue($_POST ['chkNON_DEFAULT_FACTION_NOTES']).", ";
	$updateQuery.= "cnIC_NOTES_TEXT = '".ba_db_real_escape_string($link, $_POST ['txtIC_NOTES_TEXT'])."', ";
	$updateQuery.= "cnLOGIN_TIMEOUT = ".ba_db_real_escape_string($link, (int) $_POST ['txtLOGIN_TIMEOUT']).", ";
	$updateQuery.= "cnLOGIN_TRIES = ".ba_db_real_escape_string($link, (int) $_POST ['txtLOGIN_TRIES']).", ";
	$updateQuery.= "cnMIN_PASS_LEN = ".ba_db_real_escape_string($link, (int) $_POST ['txtMIN_PASS_LEN']).", ";
	$updateQuery.= "cnSEND_PASSWORD = ".setBoolValue($_POST ['chkSEND_PASSWORD']).", ";
	$updateQuery.= "cnUSE_PAY_PAL = ".setBoolValue($_POST ['chkUSE_PAY_PAL']).", ";
	$updateQuery.= "cnPAYPAL_EMAIL = '".ba_db_real_escape_string($link, $_POST ['txtPAYPAL_EMAIL'])."', ";
	$updateQuery.= "cnNPC_LABEL = '".ba_db_real_escape_string($link, $_POST ['txtNPC_LABEL'])."', ";
	$updateQuery.= "cnPAYPAL_AUTO_MARK_PAID = ".setBoolValue($_POST ['chkPAYPAL_AUTO_MARK_PAID']).", ";
	$updateQuery.= "cnUSE_SHORT_OS_NAMES = ".setBoolValue($_POST ['chkUSE_SHORT_OS_NAMES']).", ";
	$updateQuery.= "cnALLOW_EVENT_PACK_BY_POST = ".setBoolValue($_POST ['chkALLOW_EVENT_PACK_BY_POST']).", ";
	$updateQuery.= "cnSTAFF_LABEL = '".ba_db_real_escape_string($link, $_POST ['txtSTAFF_LABEL'])."', ";
	$updateQuery.= "cnQUEUE_OVER_LIMIT = ".setBoolValue($_POST ['chkQUEUE_OVER_LIMIT']);

	//Update database
	$bUpdate = ba_db_query ($link, $updateQuery);
}

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_changeconfig.php')) {
	//Get new config information from database
	$sql = "SELECT * FROM {$db_prefix}config WHERE cnName = 'Default' ";

	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows ($result) == 1)
		$row = ba_db_fetch_assoc ($result);
	else
		$sWarn = "Could not find config information in database";
	//Compare old & new configs
	foreach ($row as $col => $value)
		if ($row [$col] != $oldconfig [$col])
			$sChanges .= "$col changed: '{$oldconfig [$col]}' => '{$row [$col]}'\n";

	//Get root's e-mail address
	$sql = "SELECT plEmail FROM {$db_prefix}players WHERE plPlayerID = " . ROOT_USER_ID;
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	$root_email = $row ['plEmail'];

	if (! $bUpdate) {
		$sWarn = "There was a problem updating the config details";
		LogError ("There was a problem updating the config details. Admin ID: $PLAYER_ID");
		//E-mail root
		$subject = SYSTEM_NAME . " - Error updating config details";
		$body = "Player ID $PLAYER_ID tried to change the config details, but an error was encountered. See the log for more details";
		mail ($root_email, $subject, $body, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	}
	else {
		$sMessage = "The config settings have been successfully updated.";
		//E-mail root
		$subject = SYSTEM_NAME . " - Config details updated";
		$body = "The config details have been changed by player ID $PLAYER_ID:\n" . $sChanges;
		mail ($root_email, $subject, $body, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	}
}

//Get config information from database
$sql = "SELECT * FROM {$db_prefix}config WHERE cnName = 'Default' ";

$result = ba_db_query ($link, $sql);
if (ba_db_num_rows ($result) == 1)
	$row = ba_db_fetch_assoc ($result);
else
	$sWarn = "Could not find config information in database";
?>

<h1><?php echo TITLE?> - Edit config settings</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>" . $sWarn . "</p>";
if ($sMessage !='')
	echo "<p class = 'green'>" . $sMessage . "</p>";
?>

<p>
<form action = "admin_changeconfig.php" method = "post">
<table>
<tr><td><?php HelpLink ('help_config_login.php'); ?> LOGIN_TIMEOUT:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars($row['cnLOGIN_TIMEOUT']); ?>" name="txtLOGIN_TIMEOUT" /></td></tr>
<tr><td><?php HelpLink ('help_config_login.php'); ?> LOGIN_TRIES:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars($row['cnLOGIN_TRIES']); ?>" name="txtLOGIN_TRIES" /></td></tr>
<tr><td><?php HelpLink ('help_config_login.php'); ?> MIN_PASS_LEN:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars($row['cnMIN_PASS_LEN']); ?>" name="txtMIN_PASS_LEN" /></td></tr>
<tr><td><?php HelpLink ('help_config_login.php'); ?> SEND_PASSWORD:</td><td><input type="checkbox" <?php if ($row['cnSEND_PASSWORD']) {echo " checked";} ?> name="chkSEND_PASSWORD" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> SYSTEM_NAME:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnSYSTEM_NAME'])); ?>" name="txtSYSTEM_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TITLE:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnTITLE'])); ?>" name="txtTITLE" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> ANNOUNCEMENT_MESSAGE:</td><td><textarea rows="4" cols="60" name="txtANNOUNCEMENT_MESSAGE"><?php echo htmlspecialchars(stripslashes($row['cnANNOUNCEMENT_MESSAGE'])); ?></textarea></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> DISCLAIMER_TEXT:</td><td><textarea rows="4" cols="60" name="txtDISCLAIMER_TEXT" ><?php echo htmlspecialchars(stripslashes ($row['cnDISCLAIMER_TEXT'])); ?></textarea></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> EVENT_CONTACT_NAME:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnEVENT_CONTACT_NAME'])); ?>" name="txtEVENT_CONTACT_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> EVENT_CONTACT_MAIL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnEVENT_CONTACT_MAIL'])); ?>" name="txtEVENT_CONTACT_MAIL" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TECH_CONTACT_NAME:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnTECH_CONTACT_NAME'])); ?>" name="txtTECH_CONTACT_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> TECH_CONTACT_MAIL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnTECH_CONTACT_MAIL'])); ?>" name="txtTECH_CONTACT_MAIL" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> BOOKING_FORM_FILE_NAME:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnBOOKING_FORM_FILE_NAME'])); ?>" name="txtBOOKING_FORM_FILE_NAME" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> BOOKING_LIST_IF_LOGGED_IN:</td><td><input type="checkbox" <?php if($row['cnBOOKING_LIST_IF_LOGGED_IN']) { echo " checked";} ?> name="chkBOOKING_LIST_IF_LOGGED_IN" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> QUEUE_OVER_LIMIT:</td><td><input type="checkbox" <?php if($row['cnQUEUE_OVER_LIMIT']) { echo " checked";} ?> name="chkQUEUE_OVER_LIMIT" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> STAFF_LABEL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnSTAFF_LABEL'])); ?>" name="txtSTAFF_LABEL" /></td></tr>
<tr><td><?php HelpLink ('help_config_display.php'); ?> NPC_LABEL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnNPC_LABEL'])); ?>" name="txtNPC_LABEL" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> LOCATIONS_LABEL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes ($row['cnLOCATIONS_LABEL'])); ?>" name="txtLOCATIONS_LABEL" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> LIST_GROUPS_LABEL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes($row['cnLIST_GROUPS_LABEL'])); ?>" name="txtLIST_GROUPS_LABEL" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> ANCESTOR_DROPDOWN:</td><td><input type="checkbox" <?php if ($row['cnANCESTOR_DROPDOWN']) { echo " checked";} ?> name="chkANCESTOR_DROPDOWN" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> USE_SHORT_OS_NAMES:</td><td><input type="checkbox" <?php if($row['cnUSE_SHORT_OS_NAMES']) { echo " checked";} ?> name="chkUSE_SHORT_OS_NAMES" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> DEFAULT_FACTION:</td><td><select name = "selDEFAULT_FACTION"><?php ListNames ($link, DB_PREFIX . 'factions', 'faName', $row['cnDEFAULT_FACTION']); ?></select></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> NON_DEFAULT_FACTION_NOTES:</td><td><input type="checkbox" <?php if($row['cnNON_DEFAULT_FACTION_NOTES']) { echo " checked";} ?> name="chkNON_DEFAULT_FACTION_NOTES" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> ALLOW_EVENT_PACK_BY_POST:</td><td><input type="checkbox" <?php if($row['cnALLOW_EVENT_PACK_BY_POST']) { echo " checked";} ?> name="chkALLOW_EVENT_PACK_BY_POST" /></td></tr>
<tr><td><?php HelpLink ('help_config_character.php'); ?> IC_NOTES_TEXT:</td><td><textarea rows="4" cols="60" name="txtIC_NOTES_TEXT"><?php echo htmlspecialchars(stripslashes($row['cnIC_NOTES_TEXT'])); ?></textarea></td></tr>

<tr><td><?php HelpLink ('help_config_paypal.php'); ?> USE_PAY_PAL:</td><td><input type="checkbox" <?php if ($row['cnUSE_PAY_PAL']) { echo " checked";} ?> name="chkUSE_PAY_PAL" /></td></tr>
<tr><td><?php HelpLink ('help_config_paypal.php'); ?> PAYPAL_EMAIL:</td><td><input type="text" class = "text" value="<?php echo htmlspecialchars(stripslashes($row['cnPAYPAL_EMAIL'])); ?>" name="txtPAYPAL_EMAIL" /></td></tr>

<tr><td><?php HelpLink ('help_config_paypal.php'); ?> PAYPAL_AUTO_MARK_PAID:</td><td><input type="checkbox" <?php if ($row['cnPAYPAL_AUTO_MARK_PAID']) { echo " checked";} ?> name="chkPAYPAL_AUTO_MARK_PAID" /></td></tr>

<tr><td colspan = "2" class = "mid"><input type = 'submit' value = 'Update values' name = 'btnSubmit'></td></tr>
</table>
</form>

</p>

<?php
include ('../inc/inc_foot.php');