<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_viewdetails.php
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

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - View Details</h1>

<p><a href = "admin.php">Admin</a></p>

<?php
if ($_GET ['green'] != '')
	echo "<p class = 'green'>" . htmlentities ($_GET ['green']) . "</p>\n";
if ($_GET ['warn'] != '') {
	$sWarn = str_replace ("&lt;br&gt;", "<br>\n", htmlentities ($_GET ['warn']));
	$sWarn = str_replace ("&lt;b&gt;", "<b>", $sWarn);
	$sWarn = str_replace ("&lt;/b&gt;", "</b>", $sWarn);
	$sWarn = str_replace ("&amp;quot;", "&quot;", $sWarn);
	echo "<p class = 'warn'>$sWarn</p>\n";
}

//Player ID is passed as GET. Should be an integer, so cast to integer
$pid = (int) $_GET ['pid'];
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;

//Clear IC Details
if ($_GET ['btnClearIC'] != '' && $_GET['txtClearIC'] == "CONFIRM" && CheckReferrer ('admin_viewdetails.php')) {
		$sql = "DELETE FROM {$db_prefix}characters WHERE chPlayerID = $pid";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem clearing character details";
			LogError ("Error clearing character details (characters) (admin).\nPlayer ID: $pid");
		}

		$sql = "DELETE FROM {$db_prefix}ospstaken WHERE otPlayerID = $pid";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem clearing character details";
			LogError ("Error clearing character details (ospstaken) (admin).\nPlayer ID: $pid");
		}

		$sql = "DELETE FROM {$db_prefix}guildmembers WHERE gmPlayerID = $pid";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem clearing character details";
			LogError ("Error clearing character details (guildmembers) (admin).\nPlayer ID: $pid");
		}

		$sql = "DELETE FROM {$db_prefix}skillstaken WHERE stPlayerID = $pid";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem clearing character details";
			LogError ("Error clearing character details (skillstaken) (admin).\nPlayer ID: $pid");
		}

}


//If the 'not playing' button has been pressed, enter a monster booking.
if ($_GET ['btnSubmitNotPlaying'] != '' && CheckReferrer ('admin_viewdetails.php')) {
		//User does not wish to play - only monster/staff. Need a row in characters table so that SQL in bookings.php will work
		//Character details - check if character exists
		$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $pid";
		$result = ba_db_query ($link, $sql);
		//If character does not exist insert a row so that UPDATE query will work
		if (ba_db_num_rows ($result) == 0) {
			$sql = "INSERT INTO {$db_prefix}characters (chPlayerID) VALUES ($pid)";
			if (! ba_db_query ($link, $sql)) {
				$sWarn = "There was a problem updating the database";
				LogError ("Error inserting player ID into characters table prior to running UPDATE query.\nPlayer ID: $pid");
			}
		}
		$sql = "UPDATE {$db_prefix}characters SET chMonsterOnly = 1 " .
			"WHERE chPlayerID = $pid";
		//Run query
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating the database";
			LogError ("Error updating character details. Player ID: $pid");
		}

		//Set bkDateICConfirmed in bookings table
		$sDate = date ('Y-m-d');
		//Check if player already has an entry in bookings table
		$sql = "SELECT * FROM {$db_prefix}bookings WHERE bkPlayerID = $pid";
		$result = ba_db_query ($link, $sql);
		//If player has not booked insert a new row
		if (ba_db_num_rows ($result) == 0) {
			$sql = "INSERT INTO {$db_prefix}bookings (bkPlayerID, bkDateICConfirmed) VALUES ($pid, '$sDate')";
			if (! ba_db_query ($link, $sql)) {
				$sWarn = "There was a problem updating the database";
				LogError ("Error inserting new monster/staff booking.\nPlayer ID: $pid");
			}
		}
		else {
			//Update existing row
			$sql = "UPDATE {$db_prefix}bookings SET bkDateICConfirmed = '$sDate' WHERE bkPlayerID = $pid";
			if (! ba_db_query ($link, $sql)) {
				$sWarn = "There was a problem updating the database";
				LogError ("Error updating new monster/staff booking.\nPlayer ID: $pid");
			}
		}
}


if ($pid != 0) {
	echo "<h2>Player ID " . PID_PREFIX . sprintf ('%03s', $pid) . "</h2>\n";
	//Get OOC details
	$sql = "SELECT plFirstName, " .
		"plSurname, " .
		"AES_DECRYPT(pleAddress1, '$key') AS dAddress1, " .
		"AES_DECRYPT(pleAddress2, '$key') AS dAddress2, " .
		"AES_DECRYPT(pleAddress3, '$key') AS dAddress3, " .
		"AES_DECRYPT(pleAddress4, '$key') AS dAddress4, " .
		"AES_DECRYPT(plePostcode, '$key') AS dPostcode, " .
		"AES_DECRYPT(pleTelephone, '$key') AS dTelephone, " .
		"AES_DECRYPT(pleMobile, '$key') AS dMobile, " .
		"plEmail, " .
		"plDOB, " .
		"AES_DECRYPT(pleMedicalInfo, '$key') AS dMedicalInfo, " .
		"plEmergencyName, " .
		"AES_DECRYPT(pleEmergencyNumber, '$key') AS dEmergencyNumber, " .
		"plEmergencyRelationship, " .
		"plCarRegistration, " .
		"plDietary, " .
		"plNotes, " .
		"plAdminNotes, " .
		"plMarshal, " .
		"plRefNumber, " .
		"plEventPackByPost " .
		"FROM {$db_prefix}players " .
		"WHERE plPlayerID = $pid";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);

	//Check if all required info is present
	$OOC_OK = True;
	if ($row ['plFirstName'] == '' || $row ['plSurname'] == '')
		$OOC_OK = False;
	if ($row ['dAddress1'] == '' || $row ['plEmergencyName'] == '')
		$OOC_OK = False;
	if ($row ['dEmergencyNumber'] == '' || $row ['plEmergencyRelationship'] == '')
		$OOC_OK = False;
	if ($row ['plCarRegistration'] == 'Enter NA if you do not drive' || $row ['plDietary'] == 'Select one')
		$OOC_OK = False;

	?>

	<hr>
	<h3>Event Bookings</h3>

<?php
	$sql = "Select * FROM {$db_prefix}bookings inner join {$db_prefix}events on bkEventID = evEventID where bkPlayerID = $pid ORDER BY evEventDate DESC";
	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no recorded bookings for this player</p>";
	}
	else
	{

		echo "<table>";
		while ($eventrow = ba_db_fetch_assoc ($result))
		{
			echo "<tr><td><a href='admin_manageevent.php?EventID=".$eventrow['evEventID']."'>".htmlentities (stripslashes ($eventrow['evEventName']))."</a></td><td>".$eventrow['evEventDate']."</td><td>".$eventrow['bkBookAs']."</td><td><a href='admin_booking.php?BookingID=".$eventrow['bkID']."'>View Booking</a></td></tr></td>";
		}
		echo "</table>";

	}
?>


	<hr>
	<h3>OOC Details</h3>
	<table>
	<?php
	echo "<tr class = 'highlight'><td>Name:</td><td>" . htmlentities (stripslashes ($row ['plFirstName'])) . " " .
		htmlentities (stripslashes ($row ['plSurname'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Address:</td><td>";
	echo htmlentities (stripslashes ($row ['dAddress1'])) . "<br>\n";
	if ($row ['dAddress2'] != '')
		echo htmlentities (stripslashes ($row ['dAddress2'])) . "<br>\n";
	if ($row ['dAddress3'] != '')
		echo htmlentities (stripslashes ($row ['dAddress3'])) . "<br>\n";
	if ($row ['dAddress4'] != '')
		echo htmlentities (stripslashes ($row ['dAddress4'])) . "<br>\n";
	echo "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Postcode:</td><td>" . htmlentities (stripslashes ($row ['dPostcode'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Telephone:</td><td>" . htmlentities (stripslashes ($row ['dTelephone'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Mobile:</td><td>" . htmlentities (stripslashes ($row ['dMobile'])) . "</td></tr>\n";
	$sEmail = htmlentities (SafeEmail (stripslashes ($row ['plEmail'])));
	echo "<tr class = 'highlight'><td>E-mail:</td><td><a href = 'mailto:$sEmail'>$sEmail</a></td></tr>\n";
	//Date of birth is stored in YYYYMMDD format - need to decode
	$sDoB = $row ['plDOB'];
	$iDobYear = substr ($sDoB, 0, 4);
	$iMonth = substr ($sDoB, 4, 2);
	$iDate = substr ($sDoB, 6, 2);
	echo "<tr class = 'highlight'><td>Date of Birth:</td><td>$iDate-$iMonth-$iDobYear</td></tr>\n";
	$sMedicalInfo = str_replace ('\r\n', "\n", $row ['dMedicalInfo']);
	echo "<tr class = 'highlight'><td>Medical Info:</td><td>" . nl2br (htmlentities (stripslashes ($sMedicalInfo))) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Emergency Contact:</td><td>" . htmlentities (stripslashes ($row ['plEmergencyName'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Emergency Contact (number):</td><td>" .
		htmlentities (stripslashes ($row ['dEmergencyNumber'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Emergency Contact (relationship):</td><td>" .
		htmlentities (stripslashes ($row ['plEmergencyRelationship'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Car Registration:</td><td>" . htmlentities (stripslashes ($row ['plCarRegistration'])) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Dietary Requirements:</td><td>" . htmlentities (stripslashes ($row ['plDietary'])) . "</td></tr>\n";
	echo "<tr><td>Marshal Status:</td><td>".htmlentities (stripslashes ($row ['plMarshal']))."</td></tr>";
	echo "<tr><td>Ref Number:</td><td>".htmlentities (stripslashes ($row ['plRefNumber']))."</td></tr>";
	if ($row ['plEventPackByPost'] == 0)
		$sEventPackByPost = 'No';
	else
		$sEventPackByPost = 'Yes';
	if (ALLOW_EVENT_PACK_BY_POST)
	{
		echo "<tr class = 'highlight'><td>Eventpack by Post:</td><td>$sEventPackByPost</td></tr>\n";
	}
	echo "<tr class = 'highlight'><td>General Notes (not medical/allergy):</td>";
	echo "<td>" . htmlentities (stripslashes ($row ['plNotes'] )) . "</td></tr>\n";
	echo "<tr class = 'highlight'><td>Admin Notes (only visible to admins):</td>";
	echo "<td>" . htmlentities (stripslashes ($row ['plAdminNotes'] )) . "</td></tr>\n";
	echo "<tr><td colspan = '2'>&nbsp;</td></tr>\n";
	echo "<tr><td class = 'mid'><a href = 'admin_edit_ooc.php?pid=$pid'>Edit OOC Details</a></td>\n";
	echo "</tr>\n";
	?>
	</table>
	</p>
	<hr>

	<h3>IC Details</h3>
	<?php
	$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $pid";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	$sNotes = $row ['chNotes'];
	$sOSP = $row ['chOSP'];

	//Check if all required info is present
	$IC_OK = True;
	if ($row ['chName'] == '')
		$IC_OK = False;
	if ($row ['chNPC'] == '1' && $sNotes == '')
		$IC_OK = False;
	?>
	<p>
	<table><tr class = 'highlight'>
	<td>Character Name:</td>
	<td><?php echo htmlentities (stripslashes ($row ['chName']))?></td>
	</tr><tr class = 'highlight'>
	<td>Preferred Character Name:</td>
	<td><?php echo htmlentities (stripslashes ($row ['chPreferredName']))?></td>
	</tr><tr class = 'highlight'>
	<td>Race</td>
	<td><?php echo htmlentities (stripslashes ($row ['chRace']))?></td>
	</tr>
	<?php
	if (LIST_GROUPS_LABEL != '') {
		echo "<tr class = 'highlight'><td>Group:</td><td>";
		if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
			$sGroup = htmlentities (stripslashes ($row ['chGroupSel']));
		else
			$sGroup = htmlentities (stripslashes ($row ['chGroupText']));
		echo $sGroup;
		echo "</td></tr>";
	}
	?>
	<tr class = 'highlight'>
	<td>Faction:</td>
	<td><?php echo htmlentities (stripslashes ($row ['chFaction']))?></td>
	</tr><tr class = 'highlight'>
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
		echo "<tr class = 'highlight'><td>" . LOCATIONS_LABEL . "</td><td>";
		echo htmlentities (stripslashes ($row ['chLocation']));
		echo "</td></tr>";
	}
	?>
	</table>
	</p>

	<p><table>
	<tr><th colspan = '2'>Guilds</th></tr>
	<?php
	//Get character's guilds & display them
	$result = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = $pid ORDER BY gmName");
	while ($row = ba_db_fetch_assoc ($result))
		echo "<tr><td colspan = '2'>" . htmlentities (stripslashes ($row ['gmName'])) . "</td></tr>";
	if (ba_db_num_rows ($result) == 0)
		echo "<tr><td colspan = '2'>None</td></tr>";
	?>

	<tr><td colspan = "2">&nbsp;</td></tr>
	<tr><th colspan = '2'>Skills</th></tr>
	<?php
	$sql = "SELECT skID, skName FROM {$db_prefix}skills, {$db_prefix}skillstaken WHERE stPlayerID = $pid AND skID = stSkillID ORDER BY skName";
	$result = ba_db_query ($link, $sql);
	while ($row = ba_db_fetch_assoc ($result))
		echo "<tr><td colspan = '2'>" . htmlentities (stripslashes ($row ['skName'])) . "</td></tr>";
	?>
	<tr><td colspan = "2">&nbsp;</td></tr>
	<tr><td colspan = '2'><b>Notes</b><br>
	<?php
	$sNotes = htmlentities (stripslashes ($sNotes));
	if ($sNotes == '')
		echo "<i>None</i>\n";
	else
		echo str_replace ("\n", "<br>", $sNotes);
	?>
	</td></tr>

	<tr><td colspan = "2">&nbsp;</td></tr>
	<tr><td colspan = '2'><b>Special items/powers/creatures:</b></td></tr>
	<tr><td colspan = '2'>
	<?php
	$sOSP = htmlentities (stripslashes ($sOSP));
	if ($sOSP == '')
		echo "<i>None</i>\n";
	else
		echo str_replace ("\n", "<br>", $sOSP);
	echo "</td></tr>\n";


	echo "<tr><td colspan = '2'>&nbsp;</td></tr>";
	echo "<tr><th colspan = '2'>OSPs</th></tr>";

	//Get character's OSPs & display them
	$result = ba_db_query ($link, "SELECT ospName, ospAllowAdditionalText, otAdditionalText FROM {$db_prefix}ospstaken, {$db_prefix}osps WHERE otPlayerID = $pid AND ospID = otOspID ORDER BY ospName");
	while ($row = ba_db_fetch_assoc ($result))
	{
		echo "<tr><td colspan = '2'>" . htmlentities (stripslashes ($row ['ospName']));
		if ($row['ospAllowAdditionalText'] == "1") { echo " (". htmlentities (stripslashes ($row ['otAdditionalText'])).")"; }
		echo "</td></tr>";
	}
	if (ba_db_num_rows ($result) == 0)
		echo "<tr><td colspan = '2'>None</td></tr>";

	echo "</table>\n";


	echo "</p>\n<table>\n";
	echo "<tr><td colspan = '2'>&nbsp;</td></tr>\n";
	echo "<tr><td class = 'mid'><a href = 'admin_edit_ic.php?pid=$pid'>Edit IC Details</a></td>\n";
	echo "<td class = 'mid'>Enter 'CONFIRM' and click to clear the IC details fully<br/>";
	echo "<form method='get' action='admin_viewdetails.php'>";
	echo "<input type = 'hidden' name = 'pid' value='$pid'>";
	echo "<input type = 'text' name = 'txtClearIC'/><input type = 'submit' value = 'Clear IC details' name = 'btnClearIC' />";
	echo "</form>";
	echo "</tr>\n";
	echo "</table>\n";
}
?>
</p>


<?php
include ('../inc/inc_foot.php');