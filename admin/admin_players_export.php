<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_players_export.php
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

//Function to format data from database
function formatdata ($data, $bHTML) {
	$sReturn = stripslashes ($data);
	if ($bHTML)
		$sReturn = htmlentities ($sReturn);
	$sReturn = str_replace ("\n", "; ", $sReturn);
	return $sReturn;
}

if ($_GET ['action'] == 'save') {
	$rowstart = "";
	$cellstart = '"';
	$cellend = '"';
	$separator = ',';
	$rowend = "\n";
	//Send headers to tell browser that this is a CSV file
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=players.csv;");
	$bHTML = False;
}
elseif ($_GET ['action'] == 'view') {
	$rowstart = "<tr>";
	$cellstart = '<td>';
	$cellend = '</td>';
	$separator = '';
	$rowend = "</tr>\n";
	include ('../inc/inc_head_html.php');
	echo "<script src='../inc/sorttable.js' type='text/javascript'></script>";
	echo "<h1>" . TITLE . " - All Players</h1>\n";
	echo "<p>\n<a href = 'admin.php'>Admin</a></p>\n";
	echo "<p>\nClick on a column header to sort by that column.\n</p>\n";
	echo "<table border = '1' class='sortable'>";
	$bHTML = True;
}
else
	die ("Invalid GET request");

//Get list of players
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;
$sql = "SELECT plPlayerID, " .
	"plAccess, " .
	"plFirstName, " .
	"plSurname, " .
	"IFNULL(AES_DECRYPT(pleAddress1, '$key'),'') AS dAddress1, " .
	"IFNULL(AES_DECRYPT(pleAddress2, '$key'),'') AS dAddress2, " .
	"IFNULL(AES_DECRYPT(pleAddress3, '$key'),'') AS dAddress3, " .
	"IFNULL(AES_DECRYPT(pleAddress4, '$key'),'') AS dAddress4, " .
	"IFNULL(AES_DECRYPT(plePostcode, '$key'),'') AS dPostcode, " .
	"IFNULL(AES_DECRYPT(pleTelephone, '$key'),'') AS dTelephone, " .
	"IFNULL(AES_DECRYPT(pleMobile, '$key'),'') AS dMobile, " .
	"plEmail, " .
	"plDOB, " .
	"IFNULL(AES_DECRYPT(pleMedicalInfo, '$key'),'') AS dMedicalInfo, " .
	"plEmergencyName, " .
	"IFNULL(AES_DECRYPT(pleEmergencyNumber, '$key'),'') AS dEmergencyNumber, " .
	"plEmergencyRelationship, " .
	"plCarRegistration, " .
	"plDietary, " .
	"plNotes, " .
	"plMarshal, ".
	"plRefNumber, ".
	"plEventPackByPost ".
	"from {$db_prefix}players " .
	" ORDER BY IF(plSurname='',1,0),plSurname";

$result = ba_db_query ($link, $sql);

//Header row
echo $rowstart . $cellstart . 'PlayerID' . $cellend . $separator;
echo $cellstart . 'First Name' . $cellend . $separator;
echo $cellstart . 'Surname' . $cellend . $separator;
echo $cellstart . 'Address 1' . $cellend . $separator;
echo $cellstart . 'Address 2' . $cellend . $separator;
echo $cellstart . 'Address 3' . $cellend . $separator;
echo $cellstart . 'Address 4' . $cellend . $separator;
echo $cellstart . 'Postcode' . $cellend . $separator;
echo $cellstart . 'Telephone No.' . $cellend . $separator;
echo $cellstart . 'Mobile No.' . $cellend . $separator;
echo $cellstart . 'E-mail' . $cellend . $separator;
echo $cellstart . 'Date of Birth' . $cellend . $separator;
echo $cellstart . 'Medical Info' . $cellend . $separator;
echo $cellstart . 'Emergency Name' . $cellend . $separator;
echo $cellstart . 'Emergency Number' . $cellend . $separator;
echo $cellstart . 'Emergency Relationship' . $cellend . $separator;
echo $cellstart . 'Car Registration' . $cellend . $separator;
echo $cellstart . 'Dietary' . $cellend . $separator;
echo $cellstart . 'Marshal' . $cellend . $separator;
echo $cellstart . 'Ref Number' . $cellend . $separator;
if (ALLOW_EVENT_PACK_BY_POST)
{
	echo $cellstart . 'Event Pack by Post' . $cellend . $separator;
}
echo $cellstart . 'Notes' . $cellend . $separator;
echo $cellstart . 'Access Rights' . $cellend . $rowend;

while ($row = ba_db_fetch_assoc ($result)) {
	echo $rowstart . $cellstart . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plFirstName'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plSurname'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dAddress1'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dAddress2'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dAddress3'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dAddress4'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dPostcode'], $bHTML) . $cellend . $separator;
	echo "{$cellstart}. " . formatdata ($row ['dTelephone'], $bHTML) . " .{$cellend}" . $separator;
	echo "{$cellstart}. " . formatdata ($row ['dMobile'], $bHTML) . " .{$cellend}" . $separator;
	if ($bHTML === True)
		echo "$cellstart<a href = 'mailto:" . $row ['plEmail'] . "'>" . formatdata ($row ['plEmail'], $bHTML) . "</a>$cellend$separator";
	else
		echo $cellstart . formatdata ($row ['plEmail'], $bHTML) . $cellend . $separator;
	//Date of birth is stored in YYYYMMDD format - need to decode
	$sDoB = $row ['plDOB'];
	$iDobYear = substr ($sDoB, 0, 4);
	$iMonth = substr ($sDoB, 4, 2);
	$iDate = substr ($sDoB, 6, 2);
	echo $cellstart . "$iDate-$iMonth-$iDobYear" . $cellend . $separator;
	echo $cellstart . formatdata ($row ['dMedicalInfo'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plEmergencyName'], $bHTML) . $cellend . $separator;
	echo "{$cellstart}. " . formatdata ($row ['dEmergencyNumber'], $bHTML) . " .{$cellend}" . $separator;
	echo $cellstart . formatdata ($row ['plEmergencyRelationship'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plCarRegistration'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plDietary'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plMarshal'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plRefNumber'], $bHTML) . $cellend . $separator;
	if (ALLOW_EVENT_PACK_BY_POST)
	{
		echo $cellstart . formatdata ($row ['plEventPackByPost'], $bHTML) . $cellend . $separator;
	}
	echo $cellstart . formatdata ($row ['plNotes'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plAccess'], $bHTML) . $cellend . $rowend;
}
if ($_GET ['action'] == 'view') {
	echo "</table>\n";
	include ('../inc/inc_foot.php');
}