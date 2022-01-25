<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_chars_export.php
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
	header("Content-Disposition: attachment; filename=characters.csv;");
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
	echo "<h1>" . TITLE . " - All Characters</h1>\n";
	echo "<p>\n<a href = 'admin.php'>Admin</a></p>\n";
	echo "<p>\nClick on a column header to sort by that column.\n</p>\n";
	echo "<table border = '1' class='sortable'>";
	$bHTML = True;
}
else
	die ("Invalid GET request");

//Get list of players
$db_prefix = DB_PREFIX;
$sql = "SELECT plPlayerID, plFirstName, " .
	"plSurname, chName, chRace, " .
	"chGroupSel, chGroupText, chFaction, chAncestor, chLocation, chNotes, chOSP " .
	"FROM {$db_prefix}players, {$db_prefix}characters WHERE plPlayerID = chPlayerID ORDER BY plSurname";
$result = ba_db_query ($link, $sql);

//Header row
echo $rowstart . $cellstart . 'Player ID' . $cellend . $separator;
echo $cellstart . 'First Name' . $cellend . $separator;
echo $cellstart . 'Surname' . $cellend . $separator;
echo $cellstart . 'IC Name' . $cellend . $separator;
echo $cellstart . 'Race' . $cellend . $separator;
echo $cellstart . 'Faction' . $cellend . $separator;
echo $cellstart . 'Guilds' . $cellend . $separator;
echo $cellstart . 'Group' . $cellend . $separator;
echo $cellstart . 'Ancestor' . $cellend . $separator;
echo $cellstart . 'Notes' . $cellend . $separator;
echo $cellstart . 'OSPs' . $cellend . $separator;
echo $cellstart . 'Skills' . $cellend . $rowend;

while ($row = ba_db_fetch_assoc ($result)) {
	echo $rowstart . $cellstart . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plFirstName'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['plSurname'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['chName'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['chRace'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['chFaction'], $bHTML) . $cellend . $separator;
	echo $cellstart;
		//Guilds - currently just put them in a single cell
	$guilds = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = " . $row ['plPlayerID']);
	$guildList = "";
	while ($record = ba_db_fetch_assoc ($guilds)) {
		$guildList .= stripslashes ($record ['gmName']) . "; ";
	}
	echo formatdata ($guildList, $bHTML). $cellend . $separator;
	if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
		echo $cellstart . formatdata ($row ['chGroupSel'], $bHTML) . $cellend . $separator;
	else
		echo $cellstart . formatdata ($row ['chGroupText'], $bHTML) . $cellend . $separator;
	//echo $cellstart . formatdata ($row ['chGuilds'], $bHTML) . $cellend . $separator;
	if ($row ['chAncestor'] == 'Enter name here if not in above list' || $row ['chAncestor'] == '')
		echo $cellstart . formatdata ($row ['chAncestorSel'], $bHTML) . $cellend . $separator;
	else
		echo $cellstart . formatdata ($row ['chAncestor'], $bHTML) . $cellend . $separator;
	echo $cellstart . formatdata ($row ['chNotes'], $bHTML) . $cellend . $separator;

	//Get OSPs
	$db_prefix = DB_PREFIX;
	$ospSql = "SELECT otID, ospName, otAdditionalText FROM {$db_prefix}osps, {$db_prefix}ospstaken " .
		"WHERE otPlayerID = " . $row ['plPlayerID'] . " AND otospID = ospID order by ospName";
	$rOSPs = ba_db_query ($link, $ospSql);
	echo $cellstart;
	while ($record = ba_db_fetch_assoc ($rOSPs))
	{
		$celldata = $record ['ospName'];
		if ($record['otAdditionalText'] != "") { $celldata .= " (".$record['otAdditionalText'].")"; }

		echo formatdata ($celldata, $bHTML) . '; ';
	}
	echo $cellend . $separator;

	//Get skills
	$db_prefix = DB_PREFIX;
	$skSql = "SELECT stSkillID, skName FROM {$db_prefix}skills, {$db_prefix}skillstaken " .
		"WHERE stPlayerID = " . $row ['plPlayerID'] . " AND stSkillID = skID order by skName";
	$rSkills = ba_db_query ($link, $skSql);
	echo $cellstart;
	while ($record = ba_db_fetch_assoc ($rSkills))
		echo formatdata ($record ['skName'], $bHTML) . '; ';
	echo $cellend . $rowend;
}
if ($_GET ['action'] == 'view') {
	echo "</table>\n";
	include ('../inc/inc_foot.php');
}