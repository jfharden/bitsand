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

$db_prefix = DB_PREFIX;
$sGreen = '';
$sWarn = '';
$iLid = (int) $_GET ['id'];

if ($_GET ['action'] == 'delete' && CheckReferrer ('admin_locations.php')) {
	$sql = "DELETE FROM {$db_prefix}locations WHERE lnID = $iLid";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error deleting location";
		LogError ($sWarn);
	}
	else
		$sGreen = "location deleted";
}
elseif ($_POST ['btnEdit'] != '' && CheckReferrer ('admin_locations.php')) {
	$sql = "UPDATE {$db_prefix}locations " .
		"SET lnName = '" . ba_db_real_escape_string ($link, $_POST ['txtName']) . "' " .
		"WHERE lnID = " . (int) $_POST ['hID'];
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error updating location.";
		LogError ($sWarn);
	}
	else
		$sGreen = "location updated";
}

if ($_POST ['btnSubmit'] == 'Add' && CheckReferrer ('admin_locations.php')) {
	$sql = "INSERT INTO {$db_prefix}locations (lnName) " .
		"VALUES ('" . ba_db_real_escape_string ($link, $_POST ['txtAddName']) . "')";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "There was an error adding the location.";
		$sAddName = $_POST ['txtAddName'];
	}
	else
		$sGreen = "The location was added successfully.";
}

include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Edit Locations</h1>

<?php
if ($sGreen != '')
	echo "<p class = 'green'>$sGreen</p>";
elseif ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>";
?>

<table border = '0'>
<?php
$sql = "SELECT lnID, lnName FROM {$db_prefix}locations ORDER BY lnName";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	$sName = htmlentities (stripslashes ($row ['lnName']));

	echo "<tr><td><form action = 'admin_locations.php' method = 'post'>\n";
	echo "<input name = 'hID' value = '{$row ['lnID']}' type = 'hidden'>";
	echo "<input name = 'txtName' value = \"" . htmlentities (stripslashes ($row ['lnName'])) . "\"></td>\n";
	echo "<td><input type = 'submit' value = 'Save Changes' name = 'btnEdit'></td>";
	echo "<td><a href = 'admin_locations.php?action=delete&amp;id={$row ['lnID']}'>Delete</a>\n";
	echo "</form></td></tr>";
}
?>
</table>

<h2><a name = 'add'>Add a New Location</a></h2>

<p>
<ul>
<li>To add a new location, enter the name and click Add.
<li>HTML is not allowed.
</ul>
</p>

<form action = 'admin_locations.php' method = 'post'>
<table>
<tr><td>location Name:</td>
<td><input name = 'txtAddName' value = "<?php echo htmlentities ($sAddName) ?>"></td></tr>
<tr><td colspan = "2" class = "mid"><input type = 'submit' value = 'Add' name = 'btnSubmit'>
<input type = 'reset' value = "Reset form"></td></tr>
</table>
</form>

<?php
include ('../inc/inc_foot.php');
?>
