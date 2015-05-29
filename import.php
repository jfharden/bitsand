<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File import.php
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
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;

//Check e-mail address is not already registered
$sql = "SELECT plEmail FROM {$db_prefix}players WHERE plEmail " .
	"LIKE '" . ba_db_real_escape_string ($link, $_POST ['email']) . "'";

$result = ba_db_query ($link, $sql);
if (ba_db_num_rows ($result) > 0) {
	$msg = "<html><body>\n";
	$msg .= "<span style = 'color: red; font-weight: bold;'>\n";
	$msg .= "The e-mail address " . htmlentities ($_POST ['email']);
	$msg .= " is already registered.</span><br>\n";
	$msg .= "You can <a href = 'retrieve.php?text=";
	$msg .= urlencode ($_POST ['email']);
	$msg .= "'>reset your password</a>.";
	$msg .= "</body></html>\n";
	die ($msg);
}

$sURL = $_POST ['selSystem'];

//Create POST headers
if (isset ($_POST ['ic']))
	$ic = 1;
else
	$ic = 0;
$data = array ('email' => $_POST ['email'], 'password' => $_POST ['password'], 'ic' => $ic);
$data = http_build_query ($data);

//Get context
$opts = array (
	'http' => array (
		'method' => 'POST',
		'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
			. "Content-Length: " . strlen ($data) . "\r\n",
		'content' => $data
	)
);
$context = stream_context_create($opts);

//Get data
$csv = file ($sURL, FILE_SKIP_EMPTY_LINES, $context);
if (substr ($csv [0], 0, 6) == "ERROR:") {
	$msg = "<html><body>The remote system returned the following error:<br>\n";
	$msg .= "<span style = 'color: red; font-weight: bold;'>" . substr ($csv [0], 7) . "</span>\n";
	$msg .= "</body></html>\n";
	die ($msg);
}

// OOC details
$sPlayerCSV = explode (",", trim ($csv [0]));

$sFirstName = ba_db_real_escape_string ($link, $sPlayerCSV [0]);
$sSurname = ba_db_real_escape_string ($link, $sPlayerCSV [1]);
$sEmail = ba_db_real_escape_string ($link, $sPlayerCSV [9]);
$sDOB = ba_db_real_escape_string ($link, $sPlayerCSV [10]);
$sEmergencyName = ba_db_real_escape_string ($link, $sPlayerCSV [12]);
$sEmergencyRelationship = ba_db_real_escape_string ($link, $sPlayerCSV [14]);
$sCarRegistration = ba_db_real_escape_string ($link, $sPlayerCSV [15]);
$sDietary = ba_db_real_escape_string ($link, $sPlayerCSV [16]);
$sAddress1 = ba_db_real_escape_string ($link, $sPlayerCSV [2]);
$sAddress2 = ba_db_real_escape_string ($link, $sPlayerCSV [3]);
$sAddress3 = ba_db_real_escape_string ($link, $sPlayerCSV [4]);
$sAddress4 = ba_db_real_escape_string ($link, $sPlayerCSV [5]);
$sPostcode = ba_db_real_escape_string ($link, $sPlayerCSV [6]);
$sTelephone = ba_db_real_escape_string ($link, $sPlayerCSV [7]);
$sMobile = ba_db_real_escape_string ($link, $sPlayerCSV [8]);
$sMedicalInfo = ba_db_real_escape_string ($link, $sPlayerCSV [11]);
$sEmergencyNumber = ba_db_real_escape_string ($link, $sPlayerCSV [13]);

//Get salted hash of password
$sHashPass = sha1 ($_POST ['password'] . PW_SALT);

//Build up OOC SQL
$sql = "INSERT INTO {$db_prefix}players (" .
	"plPassword, " .
	"plFirstName, " .
	"plSurname, " .
	"pleAddress1, " .
	"pleAddress2, " .
	"pleAddress3, " .
	"pleAddress4, " .
	"plePostcode, " .
	"pleTelephone, " .
	"pleMobile, " .
	"plEmail, " .
	"plDOB, " .
	"pleMedicalInfo, " .
	"plEmergencyName, " .
	"pleEmergencyNumber, " .
	"plEmergencyRelationship, " .
	"plCarRegistration, " .
	"plDietary) " .
	"VALUES (" .
	"'$sHashPass', " .
	"'$sFirstName', " .
	"'$sSurname', " .
	"AES_ENCRYPT('$sAddress1', '$key'), " .
	"AES_ENCRYPT('$sAddress2', '$key'), " .
	"AES_ENCRYPT('$sAddress3', '$key'), " .
	"AES_ENCRYPT('$sAddress4', '$key'), " .
	"AES_ENCRYPT('$sPostcode', '$key'), " .
	"AES_ENCRYPT('$sTelephone', '$key'), " .
	"AES_ENCRYPT('$sMobile', '$key'), " .
	"'$sEmail', " .
	"'$sDOB', " .
	"AES_ENCRYPT('$sMedicalInfo', '$key'), " .
	"'$sEmergencyName', " .
	"AES_ENCRYPT('$sEmergencyNumber', '$key'), " .
	"'$sEmergencyRelationship', " .
	"'$sCarRegistration', " .
	"'$sDietary')";

// Insert OOC info
ba_db_query ($link, $sql);

// Get new player ID
$sql = "SELECT plPlayerID FROM {$db_prefix}players " .
	"WHERE plEmail = '$sEmail' AND " .
	"plPassword = '$sHashPass'";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$iPlayerID = (int) $row ['plPlayerID'];

// Check if IC details were exported
if ($ic == 1) {
	// Character details
	$sCharacterCSV = explode (",", trim ($csv [1]));

	$sName = ba_db_real_escape_string ($link, $sCharacterCSV [0]);
	$sPreferredname = ba_db_real_escape_string ($link, $sCharacterCSV [1]);
	$sRace = ba_db_real_escape_string ($link, $sCharacterCSV [2]);
	$sGender = ba_db_real_escape_string ($link, $sCharacterCSV [3]);
	$sFaction = ba_db_real_escape_string ($link, $sCharacterCSV [4]);
	$sNpc = ba_db_real_escape_string ($link, $sCharacterCSV [5]);
	$sNotes = ba_db_real_escape_string ($link, $sCharacterCSV [6]);
	$sSpecial = ba_db_real_escape_string ($link, $sCharacterCSV [7]);

	//Build up character SQL
	$sql = "INSERT INTO {$db_prefix}characters (" .
		"chPlayerID, " .
		"chName, " .
		"chPreferredName, " .
		"chRace, " .
		"chGender, " .
		"chFaction, " .
		"chNPC, " .
		"chNotes, " .
		"chOSP) " .
		"VALUES (" .
		"$iPlayerID, " .
		"'$sName', " .
		"'$sPreferredname', " .
		"'$sRace', " .
		"'$sGender', " .
		"'$sFaction', " .
		"'$sNpc', " .
		"'$sNotes', " .
		"'$sSpecial')";

	// Insert character details
	ba_db_query ($link, $sql);

	// Guilds
	$sGuildCSV = explode (",", trim ($csv [2]));
	foreach ($sGuildCSV as $guild) {
		$sql = "INSERT INTO {$db_prefix}guildmembers (gmPlayerID, gmName) " .
			"VALUES ($iPlayerID, '$guild')";
		ba_db_query ($link, $sql);
	}

	// Skills
	$sSkillsCSV = explode (",", trim ($csv [3]));
	foreach ($sSkillsCSV as $skill) {
		$sql = "INSERT INTO {$db_prefix}skillstaken (stPlayerID, stSkillID) " .
			"VALUES ($iPlayerID, $skill)";
		ba_db_query ($link, $sql);
	}

	// OSPs
	$sOspCSV = explode (",", trim ($csv [4]));
	foreach ($sOspCSV as $osp) {
		$sql = "INSERT INTO {$db_prefix}ospstaken (otPlayerID, otOspID) " .
			"VALUES ($iPlayerID, $osp)";
		ba_db_query ($link, $sql);
	}

}

//Close link to database
ba_db_close ($link);

//Redirect to index page
$sURL = fnSystemURL () . "index.php";
$sURL .= "?green=" . urlencode ("Details imported. Please log in and check your details to ensure that they are correct");
header ("Location: $sURL");