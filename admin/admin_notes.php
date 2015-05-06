<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2014 The Bitsand Project (http://bitsand.googlecode.com/)

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

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');

$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmitClearSelected'] != '' && CheckReferrer ('admin_notes.php')) {
	foreach ($_POST as $key => $value) {
		if (substr ($key, 0, 7) == "hPlayer") {
			$iPlayerID = (int)$value;
			$clear = (int) $_POST ["chkClearAdmin{$value}"];
			if ($clear > 0) {
				//Set up UPDATE query
				$sql = "UPDATE {$db_prefix}players SET plAdminNotes = '' WHERE plPlayerID = " . $iPlayerID;
				//Run UPDATE query to clear notes;
				ba_db_query ($link, $sql);
			}
		}
	}
}

if ($_POST ['btnSubmitClearAll'] != '' && CheckReferrer ('admin_notes.php')) {
	if ($_POST['txtClearAll'] == "CONFIRM")
	{
		//Set up UPDATE query
		$sql = "UPDATE {$db_prefix}players SET plAdminNotes = ''";
		//Run UPDATE query to clear notes;
		ba_db_query ($link, $sql);
	}
}

$sql = "SELECT plPlayerID, plFirstName, plSurname, plAdminNotes, chNotes FROM {$db_prefix}players, {$db_prefix}characters " .
	"WHERE (plAdminNotes <> '' OR chNotes LIKE '%Illegal set of skills entered%') AND plPlayerID = chPlayerID";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Admin Notes &amp; Illegal Skills</h1>

<p><a href = 'admin.php'>Admin</a></p>
<p>
Listed below are all players with an admin note, or who have an illegal set of skills entered.<br>
Click on a column header to sort by that column.
</p>

<form action ='admin_notes.php' method='POST'>
<table border = '1' class = 'sortable'>
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>Admin Note</th>
<th>Clear Admin Notes</th>
<th>IC Notes</th>
<th>View Details</th>
</tr>
<?php
//$bNone is True if no rows were displayed
$bNone = True;
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n";
	echo "<input type = 'hidden' name = 'hPlayer" . $row ['plPlayerID'] . "' value = '" . $row ['plPlayerID'] . "'>";
	echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']);
	echo "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plFirstName"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plSurname"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plAdminNotes"])) . "</td>\n";
	echo "<td><input type = 'checkbox' name = 'chkClearAdmin".htmlentities (stripslashes ($row ['plPlayerID']))."' value = '" . $row ['plPlayerID'] . "' /></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["chNotes"])) . "</td>\n";
	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>view OOC &amp; IC details</a></td>\n";
	echo "</tr>\n";
	$bNone = False;
}

if ($bNone)
{
	echo "<tr><td colspan = '7'><i>No players with notes found</i></td></tr>\n";
}
echo "</table>\n";

if (!$bNone)
{
	echo "<p>";
	echo "<table>";
	echo "<tr><td><input type='submit' name='btnSubmitClearSelected' value='Clear Selected Notes'/></td><td></td><td></td></tr>";
	echo "<tr><td>Clear all admin notes:</td><td><input type='text' name='txtClearAll' /><input type='submit' name='btnSubmitClearAll' value='Clear'/></td><td>Enter CONFIRM to clear all admin notes.</td></tr>";
	echo "</table>";
}
echo "</form>";
include ('../inc/inc_foot.php');
?>
