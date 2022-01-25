<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_bookings_csv.php
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

//Export bookings list to csv
include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

//Send headers to tell browser that this is a CSV file
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=bookings.csv;");

//Get list of players marked as paid
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;
$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"plPlayerNumber, " .
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
	"plDietary, " .
	"bkBookAs, " .
	"bkBunkAllocated, " .
	"bkBunkRequested, " .
	"plNotes, " .
	"plAdminNotes, " .
	"plEventPackByPost, ".
	"plCarRegistration, ".
	"plMarshal, ".
	"plRefNumber, ".
	"chName, chPreferredName, chRace, chGroupSel, chGroupText, chFaction, chAncestor, chAncestorSel, chLocation, chNPC, chNotes, chOSP, chMonsterOnly, " .
	"bkDatePaymentConfirmed, bkMealTicket, bkAmountPaid, bkAmountExpected, bkPayOnGate " .
	"FROM {$db_prefix}players ".
	"LEFT JOIN {$db_prefix}characters ON plPlayerID = chPlayerID " .
	"INNER JOIN {$db_prefix}bookings ON plPlayerID = bkPlayerID " .
	"WHERE bkEventID = $eventid " .
	"ORDER BY bkBookAs, plSurname";
//echo $sql;
$result = ba_db_query ($link, $sql);

//Header row
echo '"ID","Player Number","Player First Name","Player Surname",';
echo '"Address 1","Address 2","Address 3","Address 4","Postcode","Telephone","Mobile","E-mail","DoB","Next of Kin","NOK contact",';
echo '"NOK Relationship","Medical","Medical details","Diet","Booking As","Monster Only","Bunk Requested","Bunk Allocated","Meal Ticket",';
if (ALLOW_EVENT_PACK_BY_POST)
{
	echo '"Eventpack by Post",';
}
echo '"Paying on Gate","Marshal","Ref Number","OOC Notes","Admin Notes","Car Registration",';
echo '"Character name","Preferred Name","Race","Group","Faction","Ancestor","Location","IC Notes",';
echo '"Special items/powers/creatures","Guilds",';
//Skills ordered by skID
echo '"Ambidexterity","Ritual Magic 2","Large Melee Weapon Use","Ritual Magic 3","Projectile Weapon Use","Contribute To Ritualist",';
echo '"Shield Use","Invocation","Thrown Weapon","Power 1","Body Development 1","Power 2","Body Development 2","Power 3",';
echo '"Light Armour Use","Power 4","Medium Armour Use","Potion Lore","Heavy Armour Use","Poison Lore","Healing 1","Cartography",';
echo '"Healing 2","Sense Magic","Spellcasting 1","Evaluate","Spellcasting 2","Recognise Forgery","Incantations 1","Physician",';
echo '"Incantations 2","Bind Wounds","Ritual Magic 1",';
echo '"Date Paid","Amount Paid","Amount Expected","Power Cards Per Day","OSPs (1 per column)"' . "\n";

while ($row = ba_db_fetch_assoc ($result)) {
	echo '"' . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . '",';
	echo '"' . str_replace('"', '""', stripslashes($row['plPlayerNumber'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plFirstName'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plSurname'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dAddress1'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dAddress2'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dAddress3'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dAddress4'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dPostcode'])) . '",';
	echo '". ' . str_replace('"', '""', stripslashes ($row ['dTelephone'])) . ' .",';
	echo '". ' . str_replace('"', '""', stripslashes ($row ['dMobile'])) . ' .",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plEmail'])) . '",';
	$dDOB = stripslashes ($row ['plDOB']);
	$iYear = substr ($dDOB, 0, 4);
	$iMonth = substr ($dDOB, 4, 2);
	$iDate = substr ($dDOB, 6, 2);
	$sDOB = "$iDate-$iMonth-$iYear";
	echo '"' . $sDOB . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plEmergencyName'])) . '",';
	echo '". ' . str_replace('"', '""', stripslashes ($row ['dEmergencyNumber'])) . ' .",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plEmergencyRelationship'])) . '",';
	if ($row ['dMedicalInfo'] != '')
		echo '"MED",';
	else
		echo '"",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['dMedicalInfo'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plDietary'])) . '",';
	echo '"' . stripslashes ($row ['bkBookAs']) . '",';
	if ($row ['chMonsterOnly'] == 0)
		echo '"No",';
	else
		echo '"Yes",';
	if ($row ['bkBunkRequested'] == 0)
		echo '"No",';
	else
		echo '"Yes",';
	if ($row ['bkBunkAllocated'] == 0)
		echo '"No",';
	else
		echo '"Yes",';
	if ($row ['bkMealTicket'] == 0)
		echo '"No",';
	else
		echo '"Yes",';

	if (ALLOW_EVENT_PACK_BY_POST)
	{
		if ($row ['plEventPackByPost'] == 0)
			echo '"No",';
		else
			echo '"Yes",';
	}

	if ($row ['bkPayOnGate'] == 0)
		echo '"No",';
	else
		echo '"Yes",';

	echo '"' . str_replace('"', '""', stripslashes ($row ['plMarshal'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plRefNumber'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plNotes'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plAdminNotes'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plCarRegistration'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['chName'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['chPreferredName'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['chRace'])) . '",';
	if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
		echo '"' . stripslashes ($row ['chGroupSel']) . '",';
	else
		echo '"Other: ' . str_replace('"', '""', stripslashes ($row ['chGroupText'])) . '",';
	echo '"' . stripslashes ($row ['chFaction']) . '",';
	if ($row ['chAncestor'] == 'Enter name here if not in above list' || $row ['chAncestor'] == '')
		echo '"' . stripslashes ($row ['chAncestorSel']) . '",';
	else
		echo '"Other: ' . str_replace('"', '""', stripslashes ($row ['chAncestor'])) . '",';
	echo '"' . stripslashes ($row ['chLocation']) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['chNotes'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['chOSP'])) . '",';
	//Guilds - currently just put them in a single cell
	$guilds = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = " . $row ['plPlayerID']);
	echo '"';
	while ($record = ba_db_fetch_assoc ($guilds)) {
		echo stripslashes ($record ['gmName']) . "; ";
	}
	echo '",';
	//Get this character's skills. Fill an array with the skills. This array can then be queried, reducing database access
	$skillstaken = ba_db_query ($link, "SELECT * FROM {$db_prefix}skillstaken " .
		"WHERE stPlayerID = {$row ['plPlayerID']}");
	//$aiSkillID will hold the skill ID's
	$aiSkillID = array ();
	while ($record = ba_db_fetch_assoc ($skillstaken))
		$aiSkillID [] = $record ['stSkillID'];
	$skills = ba_db_query ($link, "SELECT * FROM {$db_prefix}skills ORDER BY skID");

	$iCards = 0;

	while ($record = ba_db_fetch_assoc ($skills)) {
		//Find out if character has this skill
		$has = array_search ($record ['skID'], $aiSkillID);
		if ($has !== False)
		{
			//Character has the skill. Echo skill name
			echo '"' . $record ['skShortName']. '",';
			//Add to their cards total
			if ($record ['skID'] == 21 || $record ['skID'] == 25 || $record ['skID'] == 29)
				$iCards = $iCards + 4;
			// Level 2 magics
			if ($record ['skID'] == 23 || $record ['skID'] == 27 || $record ['skID'] == 31)
				$iCards = $iCards + 12;
			// Power skills.
			if ($record ['skID'] == 10)
				$iCards = $iCards + 4;
			if ($record ['skID'] == 12)
				$iCards = $iCards + 8;
			if ($record ['skID'] == 14)
				$iCards = $iCards + 12;
			if ($record ['skID'] == 16)
				$iCards = $iCards + 16;
		}
		else
		{
			//Character does not have the skill. Echo empty cell
			echo '"",';
		}
	}
	//Date payment received
	$dPaid = stripslashes ($row ['bkDatePaymentConfirmed']);
	$iYear = substr ($dPaid, 0, 4);
	$iMonth = substr ($dPaid, 5, 2);
	$iDate = substr ($dPaid, 8, 2);
	$sPaid = "$iDate-$iMonth-$iYear";
	echo '"' . $sPaid . '",';

	//Amounts paid
	echo '"' . $row ['bkAmountPaid'] . '",';
	echo '"' . $row ['bkAmountExpected'] . '",';

	//OSPs - one per column
	if (USE_SHORT_OS_NAMES)
	{
		$osps = ba_db_query ($link, "SELECT ospShortName as ospExportName, otOspID, otAdditionalText FROM {$db_prefix}ospstaken, {$db_prefix}osps " .
		"WHERE otPlayerID = {$row ['plPlayerID']} AND ospID = otOspID ORDER BY ospShortName");
	}
	else
	{
		$osps = ba_db_query ($link, "SELECT ospName as ospExportName, otOspID, otAdditionalText FROM {$db_prefix}ospstaken, {$db_prefix}osps " .
		"WHERE otPlayerID = {$row ['plPlayerID']} AND ospID = otOspID ORDER BY ospName");
	}
	$sOSList = "";
	while ($record = ba_db_fetch_assoc ($osps)) {
		$sOSList .= '"' . stripslashes ($record ['ospExportName']);
		if ($record['otAdditionalText'] != "") { $sOSList .= " (".stripslashes ($record['otAdditionalText']).")"; }
		$sOSList .= '",';
		//Extra spell card OSPs
		if ($record ['otOspID'] == 6)
			$iCards = $iCards + 4;
		if ($record ['otOspID'] == 7)
			$iCards = $iCards + 8;
		if ($record ['otOspID'] == 3)
			$iCards = $iCards + 12;
		if ($record ['otOspID'] == 4)
			$iCards = $iCards + 16;
	}
	echo '"' . $iCards . '",';
	echo $sOSList;
	echo "\n";
}

//Close link to database
ba_db_close ($link);