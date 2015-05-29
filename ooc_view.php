<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File ooc_view.php
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

//Get OOC details
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;

//Get number of players, monsters, staff booked. Compare to spaces available
/*
$sql = "SELECT COUNT(*) AS i_count FROM {$db_prefix}bookings, {$db_prefix}players " .
	"WHERE plBookAs LIKE 'Player' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
$temp = ba_db_query ($link, $sql);
$ap = ba_db_fetch_assoc ($temp);
$iPlayers = $ap ['i_count'];
$sql = "SELECT COUNT(*) AS i_count FROM {$db_prefix}bookings, {$db_prefix}players " .
	"WHERE plBookAs LIKE 'Monster' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
$temp = ba_db_query ($link, $sql);
$am = ba_db_fetch_assoc ($temp);
$iMonsters = $am ['i_count'];
$sql = "SELECT COUNT(*) AS i_count FROM {$db_prefix}bookings, {$db_prefix}players " .
	"WHERE plBookAs LIKE 'Staff' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
$temp = ba_db_query ($link, $sql);
$as = ba_db_fetch_assoc ($temp);
$iStaff = $as ['i_count'];
//Total number of bookings
$iTotalBookings = $iPlayers + $iMonsters + $iStaff;
*/
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
	"plRefNumber, ".
	"plMarshal, ";
	//if (AUTO_ASSIGN_BUNKS == False)
	//	$sql .= "plBunkRequested, ";
	$sql .= "plNotes, ";
	$sql .= "plEventPackByPost " .
	"FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID";


$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);

//Determine whether or not all required info is in database
$bAllOOCInfo = True;
if ($row ['plFirstName'] == '' || $row ['plSurname'] == '')
	$bAllOOCInfo = False;
if ($row ['dAddress1'] == '' || $row ['plEmergencyName'] == '')
	$bAllOOCInfo = False;
if ($row ['dEmergencyNumber'] == '' || $row ['plEmergencyRelationship'] == '')
	$bAllOOCInfo = False;
if ($row ['plCarRegistration'] == '' || $row ['plDietary'] == 'Select one')
	$bAllOOCInfo = False;


//Get bookings details. Determine if player is booked
$booking_sql = "SELECT * FROM {$db_prefix}bookings WHERE bkPlayerID = $PLAYER_ID";
$booking_result = ba_db_query ($link, $booking_sql);
$booking_row = ba_db_fetch_assoc ($booking_result);
$sOOC = $booking_row ['bkDateOOCConfirmed'];
if ($sOOC == '' || $sOOC == '0000-00-00')
	$bConfirmed = False;
else
	$bConfirmed = True;

if (strtolower ($_POST ['btnSubmit']) == 'edit' && CheckReferrer ('ooc_view.php')) {
	//Make up URL
	$sURL = fnSystemURL () . 'ooc_form.php';
	header ("Location: $sURL");
}
elseif (strtolower ($_POST ['btnSubmit']) == 'confirm' && CheckReferrer ('ooc_view.php')) {
	$sDate = date ('Y-m-d');
	//Check if player already has an entry in bookings table
	$sql = "SELECT * FROM {$db_prefix}bookings WHERE bkPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	//If player has not booked insert a new row
	if (ba_db_num_rows ($result) == 0) {
		$sql = "INSERT INTO {$db_prefix}bookings (bkPlayerID, bkDateOOCConfirmed) VALUES ($PLAYER_ID, '$sDate')";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem confirming your OOC details";
			LogError ("Error inserting new OOC booking.\nPlayer ID: $PLAYER_ID");
		}
	}
	else {
		//Update existing row
		$sql = "UPDATE {$db_prefix}bookings SET bkDateOOCConfirmed = '$sDate' WHERE bkPlayerID = $PLAYER_ID";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem confirming your OOC details";
			LogError ("Error inserting new OOC booking.\nPlayer ID: $PLAYER_ID");
		}
	}

	//Deals with whether this is a queued booking or not
	/*
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
	*/

	//Send e-mail
	$sBody = "Your OOC details have been confirmed at " . SYSTEM_NAME . ". " .
		"Both IC and OOC details must be confirmed before you can finalise your booking.\n\n" .
		"Player ID: " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "\n" .
		"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] .
		"\n\n" . fnSystemURL () . "\n";
	if ($bEmailOOCChange)
		mail ($row ['plEmail'], SYSTEM_NAME . ' - OOC details', $sBody,	"From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
	//Make up URL & redirect
	$sURL = fnSystemURL () . 'start.php?green=' . urlencode ('Your OOC details have been confirmed');
	header ("Location: $sURL");
}

include ('inc/inc_head_html.php');
include ('inc/inc_forms.php');

?>

<h1><?php echo TITLE?> - OOC Details</h1>

<p>
<?php
if ($bConfirmed)
	echo "Your OOC information has been confirmed. You will not be able to change it until after the upcoming event.";
else
	echo "Please check all of the information carefully. If anything needs to be amended, please click the <b>Edit</b> button. If you are happy that everything is correct, please click the <b>Confirm</b> button. Once you have confirmed that the information is correct, it will be frozen until after the upcoming event.";

?>
</p>

<form action = "ooc_view.php" method = "post">
<table><tr>
<td>First name:</td>
<td><?php echo htmlentities (stripslashes ($row ['plFirstName']))?></td>
</tr><tr>
<td>Surname:</td>
<td><?php echo htmlentities (stripslashes ($row ['plSurname']))?></td>
</tr><tr><td colspan = "2">&nbsp;</td></tr><tr>
<td>Address:</td>
<td>
<?php
echo htmlentities (stripslashes ($row ['dAddress1'])) . "<br>\n";
if ($row ['dAddress2'] != '')
	echo htmlentities (stripslashes ($row ['dAddress2'])) . "<br>\n";
if ($row ['dAddress3'] != '')
	echo htmlentities (stripslashes ($row ['dAddress3'])) . "<br>\n";
if ($row ['dAddress4'] != '')
	echo htmlentities (stripslashes ($row ['dAddress4'])) . "<br>\n";
?>
</td>
</tr><tr><td colspan = "2">&nbsp;</td></tr><tr>
<td>Postcode:</td>
<td><?php echo htmlentities (stripslashes ($row ['dPostcode']))?></td>
</tr><tr>
<td>Telephone number:</td>
<td><?php echo htmlentities (stripslashes ($row ['dTelephone']))?></td>
</tr><tr>
<td>Mobile number:</td>
<td><?php echo htmlentities (stripslashes ($row ['dMobile']))?></td>
</tr><tr>
<td>E-mail address:</td>
<td><?php echo htmlentities (stripslashes ($row ['plEmail']))?></td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Date of birth:</td>
<td>
<?php
$sDoB = $row ['plDOB'];
$iDobYear = substr ($sDoB, 0, 4);
$iMonth = substr ($sDoB, 4, 2);
$iDate = substr ($sDoB, 6, 2);
echo "$iDate-$iMonth-$iDobYear";
?>
</td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Medical information:</td>
<td>
<?php
$sMedInfo = htmlentities (stripslashes ($row ['dMedicalInfo']));
$sMedInfo = str_replace ("\n", "<br>", $sMedInfo);
echo $sMedInfo;
?>
</td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Emergency contact name:</td>
<td><?php echo htmlentities (stripslashes ($row ['plEmergencyName']))?></td>
</tr><tr>
<td>Emergency contact number:</td>
<td><?php echo htmlentities (stripslashes ($row ['dEmergencyNumber']))?></td>
</tr><tr>
<td>Relationship to emergency contact:</td>
<td><?php echo htmlentities (stripslashes ($row ['plEmergencyRelationship']))?></td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Car registration:</td>
<td><?php echo htmlentities (stripslashes ($row ['plCarRegistration']))?></td>
</tr><tr>
<td>Dietary requirements:</td>
<td class = "border: thin black"><?php echo htmlentities (stripslashes ($row ['plDietary'] ))?></td>
</tr>
<!--<tr>
<td>Booking as:</td>
<td><?php echo htmlentities (stripslashes ($row ['plBookAs'] ))?></td>
</tr>-->
<tr><td>Marshal Status:</td><td>".htmlentities (stripslashes ($row ['plMarshal']))."</td></tr>
<tr><td>Ref Number:</td><td>".htmlentities (stripslashes ($row ['plRefNumber']))."</td></tr>
<?php
/*
if (AUTO_ASSIGN_BUNKS == False) {
	echo "<tr><td>Bunk requested:</td><td>";
	if ($row ['plBunkRequested'] == 1)
		echo "Yes";
	else
		echo "No";
	echo "</td></tr>\n";
}
*/
if (ALLOW_EVENT_PACK_BY_POST)
{
echo "<tr><td>Request Event Pack by Post:</td><td>";
if ($row ['plEventPackByPost'] == 1)
	echo "Yes";
else
	echo "No";
echo "</td></tr>\n";
}
?>
<tr>
<td>General Notes (not medical/allergy):</td>
<td><?php echo htmlentities (stripslashes ($row ['plNotes'] ))?></td>
</tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<?php
if ($bConfirmed == False) {
	echo "<tr><td colspan = '2'><span class = 'warn'>Confirming will freeze details until after the upcoming event</span></td></tr>\n";
	echo "<tr><td colspan = '2'>&nbsp;</td></tr>\n";
	echo "<tr><td class = 'mid'><input type = 'submit' value = 'Edit' name = 'btnSubmit'></td>\n";
	echo "<td class = 'mid'>";
	//Check that bookings are still available
	/*
	if (($row ['plBookAs'] == 'Player' && $iPlayers >= PLAYER_SPACES) || ($iTotalBookings >= TOTAL_SPACES))
		echo "<span class = 'sans-warn'>Character bookings are closed</a>\n";
	elseif (($row ['plBookAs'] == 'Monster' && $iMonsters >= MONSTER_SPACES) || ($iTotalBookings >= TOTAL_SPACES))
		echo "<span class = 'sans-warn'>Monster bookings are closed</a>\n";
	elseif (($row ['plBookAs'] == 'Staff' && $iStaff >= STAFF_SPACES) || ($iTotalBookings >= TOTAL_SPACES))
		echo "<span class = 'sans-warn'>Staff bookings are closed</a>\n";
	else
	*/
		if ($bAllOOCInfo)
			echo "<input type = 'submit' value = 'Confirm' name = 'btnSubmit'>";
		else
			echo "<span class = 'sans-warn'>Some required information missing - cannot confirm</span>\n";
	echo "</td></tr>\n";
}
?>
</table>
</form>

<?php
include ('inc/inc_foot.php');
?>
