<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File ic_view.php
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

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;

if (strtolower ($_POST ['btnSubmit']) == 'edit' && CheckReferrer ('ic_view.php')) {
	$sURL = fnSystemURL () . 'ic_form.php';
	header ("Location: $sURL");
}
elseif (strtolower ($_POST ['btnSubmit']) == 'confirm' && CheckReferrer ('ic_view.php')) {
	$sDate = date ('Y-m-d');
	//Check if player already has an entry in bookings table
	$sql = "SELECT * FROM {$db_prefix}bookings WHERE bkPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	//If player has not booked insert a new row
	if (ba_db_num_rows ($result) == 0) {
		$sql = "INSERT INTO {$db_prefix}bookings (bkPlayerID, bkDateICConfirmed) VALUES ($PLAYER_ID, '$sDate')";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem confirming your IC details";
			LogError ("Error inserting new IC booking.\nPlayer ID: $PLAYER_ID");
		}
	}
	else {
		//Update existing row
		$sql = "UPDATE {$db_prefix}bookings SET bkDateICConfirmed = '$sDate' WHERE bkPlayerID = $PLAYER_ID";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem confirming your IC details";
			LogError ("Error updating IC booking.\nPlayer ID: $PLAYER_ID");
		}
	}

	//Deals with whether this is a queued booking or not
	$pid = $PLAYER_ID;
	$queuebooking = 0;
	$dbprefix = DB_PREFIX;
	if (USE_QUEUE > 0)
	{
		$sql = "SELECT chFaction, plBookAs FROM {$dbprefix}characters, {$dbprefix}players WHERE plPlayerID = $pid AND chPlayerID = $pid";
		$result = ba_db_query ($link, $sql);
		$row = ba_db_fetch_assoc ($result);
		if ($row['plBookAs'] == 'Player' && $row['chFaction'] != DEFAULT_FACTION) { $queuebooking = 1;}
	}
	$sql = "UPDATE {$db_prefix}bookings SET bkInQueue = $queuebooking WHERE bkPlayerID = $pid";
	if (! ba_db_query ($link, $sql)) {
		LogError ("Error updating queue type of booking.\nPlayer ID: $PLAYER_ID");
	}


	//Get user's e-mail address
	$result = ba_db_query ($link, "SELECT plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID");
	$row = ba_db_fetch_assoc ($result);
	$email = $row ['plEmail'];
	//Set up e-mail body
	$sBody = "Your IC details have been confirmed at " . SYSTEM_NAME . ". " .
		"Both IC and OOC details must be confirmed before you can finalise your booking.\n\n" .
		"Player ID: " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "\n" .
		"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . fnSystemURL ();
	//Send e-mail
	if ($bEmailICChange)
		mail ($email, SYSTEM_NAME . ' - IC details', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	//Make up URL & redirect
	$sURL = fnSystemURL () . 'start.php?green=' . urlencode ('Your IC details have been confirmed');
	header ("Location: $sURL");
}

//Get bookings details. Determine if player is booked
$sql = "SELECT * FROM {$db_prefix}bookings WHERE bkPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$sIC = $row ['bkDateICConfirmed'];
if ($sIC == '' || $sIC == '0000-00-00')
	$bConfirmed = False;
else
	$bConfirmed = True;

include ('inc/inc_head_html.php');
include ('inc/inc_forms.php');

//Get existing details if there are any
$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$sCharName = $row ['chName'];
$sNotes = $row ['chNotes'];
$sSpecial = $row ['chOSP'];
?>

<h1><?php echo TITLE?> - IC Details</h1>

<p>
<?php
if ($bConfirmed)
	echo "Your IC information has been confirmed. You will not be able to change it until after the upcoming event.";
else
	echo "Please check all of the information carefully. If anything needs to be amended, please click the <b>Edit</b> button. If you are happy that everything is correct, please click the <b>Confirm</b> button. Once you have confirmed that the information is correct, it will be frozen until after the upcoming event.";
?>
</p>

<p>
<table><tr>
<td>Character Name:</td>
<td><?php echo htmlentities (stripslashes ($row ['chName']))?></td>
</tr><tr>
<td>Character Preferred Name:</td>
<td><?php echo htmlentities (stripslashes ($row ['chPreferredName']))?></td>
</tr><tr>
<td>Race</td>
<td><?php echo htmlentities (stripslashes ($row ['chRace']))?></td>
</tr>
<?php
if (LIST_GROUPS_LABEL != '') {
	echo "<tr><td>" . LIST_GROUPS_LABEL . "</td><td>";
	if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
		$sGroup = htmlentities (stripslashes ($row ['chGroupSel']));
	else
		$sGroup = htmlentities (stripslashes ($row ['chGroupText']));
	echo $sGroup;
	echo "</td></tr>\n";
}
?>
<tr>
<td>Faction:</td>
<td><?php echo htmlentities (stripslashes ($row ['chFaction']))?></td>
</tr><tr>
<?php
if (ANCESTOR_DROPDOWN != '') {
	echo "<td>Ancestor:</td><td>";
	if ($row ['chAncestor'] == 'Enter name here if not in above list' || $row ['chAncestor'] == '')
		$sAncestor = htmlentities (stripslashes ($row ['chAncestorSel']));
	else
		$sAncestor = htmlentities (stripslashes ($row ['chAncestor']));
	echo $sAncestor;
	echo "</td>\n";
}
else
{
echo "<td>Ancestor:</td><td>". htmlentities (stripslashes ($row ['chAncestor']))."</td>\n";
}
?>

</tr>
<?php
if (LOCATIONS_LABEL != '') {
	echo "<tr><td>" . LOCATIONS_LABEL . "</td><td>";
	echo htmlentities (stripslashes ($row ['chLocation']));
	echo "</td></tr>\n";
}

//Removing this as we no longer have 20 point characters
/*
echo "<tr><td>Character Type:</td><td>";

if ($row ['chNPC'] == '1') {
	echo "You are a ".MAX_NPC_PTS." point character";
	$iMaxPoints = MAX_NPC_PTS;
}
else {
	echo "You are a ".MAX_CHAR_PTS." point character";
	$iMaxPoints = MAX_CHAR_PTS;
}
echo "</td></tr>";
*/
?>

</table>

<p>
<form action = "ic_view.php" method = "post">
<table>
<tr><th colspan = "2" class = "left">Guilds</th></tr>
<?php
//Get character's guilds & display them
$result = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = $PLAYER_ID ORDER BY gmName");
while ($row = ba_db_fetch_assoc ($result))
	echo "<tr><td colspan = '2'>" . htmlentities ($row ['gmName']) . "</td></tr>\n";
if (ba_db_num_rows ($result) == 0)
	echo "<tr><td colspan = '2'><i>None</i></td></tr>\n";
?>
</p>

<tr><th colspan = "2" class = "left">Skills</th></tr>
<?php
//Get character's skills
$sMsg = '';
$iTotalPoints = 0;
//$bPower is set to True if power skills are selected, $bRip is set to True if card-ripping skills are selected
$bPower = False;
$bRip = False;
$result = ba_db_query ($link, "SELECT skID, skName, skCost FROM {$db_prefix}skills, {$db_prefix}skillstaken WHERE stPlayerID = $PLAYER_ID AND skID = stSkillID ORDER BY skName");
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr><td colspan = '2'>" . htmlentities (stripslashes ($row ['skName'])) . "</td></tr>\n";
	$iTotalPoints += $row ['skCost'];
	//Check for Power skill
	if ($row ['skID'] == 10 || $row ['skID'] == 12 || $row ['skID'] == 14 || $row ['skID'] == 16)
		$bPower = True;
	//Check for card-ripping skill
	if ($row ['skID'] == 21 || $row ['skID'] == 23 || $row ['skID'] == 25 || $row ['skID'] == 27 || $row ['skID'] == 29 || $row ['skID'] == 31)
		$bRip = True;
}
if ($iTotalPoints < $iMaxPoints)
	$sMsg = "You have used fewer character points ($iTotalPoints) than you are allowed ($iMaxPoints).<br>\n";
if ($bPower == True && $bRip == False)
	$sMsg .= "You have selected one or more power skills, but no card-ripping skills.<br>\n";
if ($sMsg != '')
	$sMsg .= "<br>\n";
?>

<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><th colspan = '2' class = "left">Notes</th></tr>
<tr><td>
<?php
$sNotes = htmlentities (stripslashes ($sNotes));
if ($sNotes == '')
	echo "<i>None</i>\n";
else
	echo str_replace ("\n", "<br>", $sNotes);
?>
</td></tr>

<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><th colspan = '2' class = "left">Special items/powers/creatures:</th></tr>
<tr><td>
<?php
$sSpecial = htmlentities (stripslashes ($sSpecial));
if ($sSpecial == '')
	echo "<i>None</i>\n";
else
	echo str_replace ("\n", "<br>", $sSpecial);
?>
</td></tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><th colspan = '2' class = "left">OSPs:</th></tr>
<?php
//Get character's OSPs
$result = ba_db_query ($link, "SELECT ospName FROM {$db_prefix}ospstaken, {$db_prefix}osps WHERE otPlayerID = $PLAYER_ID AND ospID = otOspID ORDER BY ospName");
while ($row = ba_db_fetch_assoc ($result))
	echo "<tr><td colspan = '2'>{$row ['ospName']}</td></tr>\n";
if (ba_db_num_rows ($result) == 0)
	echo "<tr><td colspan = '2'><i>None</i></td></tr>\n";

if ($bConfirmed == False) {
	echo "<tr><td colspan = '2'><p class = 'warn'>{$sMsg}Confirming will freeze your details until after the upcoming event</p></td></tr>\n";
	echo "</table>";

	echo "<table>";
	echo "<tr><td class = 'mid'><input type = 'submit' value = 'Edit' name = 'btnSubmit'></td>\n";
	if ($sCharName != '')
		echo "<td class = 'mid'><input type = 'submit' value = 'Confirm' name = 'btnSubmit'></td>";
	else
		echo "<td class = 'mid'><span class = 'sans-warn'>Some required information missing - cannot confirm</span></td>";

	echo "</form>";
	echo "<form method='POST' action='ic_confirmclear.php'>";
	echo "<td class='mid'><input type = 'submit' value = 'Clear' name = 'btnSubmit' /></td>";
	echo "</tr>\n";
}
?>
</table>
</form>

<?php
include ('inc/inc_foot.php');