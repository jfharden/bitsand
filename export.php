<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File export.php
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
$db_prefix = DB_PREFIX;

// Get POST into variables
$email = $_POST ['email'];
$password = sha1 ($_POST ['password'] . PW_SALT);
$ic = (int) $_POST ['ic'];

//Set up & run query
$sql = "SELECT plPlayerID FROM {$db_prefix}players " .
	"WHERE plEmail LIKE '" . ba_db_real_escape_string ($link, $email) .
	"' AND plPassword = '$password'";

$result = ba_db_query ($link, $sql);
if (ba_db_num_rows ($result) > 1)
	//Log warning if there was more than one row returned
	LogWarning ("export.php - more than one result from e-mail and password\n$sql");
if (ba_db_num_rows ($result) > 0) {
	//Successfully logged in
	$row = ba_db_fetch_assoc ($result);
	$id = $row ['plPlayerID'];
}
else
	die ("ERROR: Wrong e-mail or password");

// Export as a CSV file
header("Content-Type: text/csv");

// Get OOC details
$key = CRYPT_KEY;
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
	"plDietary " .
	"FROM {$db_prefix}players WHERE plPlayerID = $id";

$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);

//OOC CSV line - replace double-quotes, newlines & commas
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plFirstName'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plSurname'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dAddress1'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dAddress2'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dAddress3'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dAddress4'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dPostcode'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dTelephone'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dMobile'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plEmail'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plDOB'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dMedicalInfo'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plEmergencyName'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['dEmergencyNumber'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plEmergencyRelationship'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plCarRegistration'])) . ',';
echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['plDietary'])) . "\n";

if ($ic == 1) {
	$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $id";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);

	//IC CSV - replace double-quotes, newlines & commas
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chName'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chPreferredName'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chRace'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chGender'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chFaction'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chNPC'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chNotes'])) . ',';
	echo str_replace (array ('"', "\n", ","), array ("'", ";", ";"), stripslashes ($row ['chOSP'])) . "\n";

	//Guilds CSV
	$sql = "SELECT gmName " .
		"FROM {$db_prefix}guildmembers " .
		"WHERE gmPlayerID = $id";
	$result = ba_db_query ($link, $sql);
	while ($row = ba_db_fetch_assoc ($result))
		echo stripslashes ($row ['gmName']) . ',';
	echo "\n";

	//Skills CSV
	$sql = "SELECT stSkillID " .
		"FROM {$db_prefix}skillstaken " .
		"WHERE stPlayerID = $id";
	$result = ba_db_query ($link, $sql);
	while ($row = ba_db_fetch_assoc ($result))
		echo $row ['stSkillID'] . ',';
	echo "\n";

	//OSPs
	$sql = "SELECT otOspID " .
		"FROM {$db_prefix}ospstaken " .
		"WHERE otPlayerID = $id";
	$result = ba_db_query ($link, $sql);
	while ($row = ba_db_fetch_assoc ($result))
		echo $row ['otOspID'] . ',';
	echo "\n";
}
?>
