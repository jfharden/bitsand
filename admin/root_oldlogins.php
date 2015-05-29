<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/root_oldlogins.php
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
require ('../inc/inc_admin.php');
require ('../inc/inc_root.php');

//Initialise some useful variables
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;
$sMsg = '';

//Set age
if (isset ($_POST ['btnUpdate']) && CheckReferrer ('root_oldlogins.php'))
	$iAge = (int) $_POST ['txtAge'];
elseif (isset ($_POST ['hAge']) && CheckReferrer ('root_oldlogins.php'))
	$iAge = (int) $_POST ['hAge'];
else
	$iAge = 18;
$sDate = date ("Y-m-d", strtotime ("-$iAge months"));

//Delete users
if (isset ($_POST ['btnDelete']) && CheckReferrer ('root_oldlogins.php') && $_POST ['txtConfirm'] == 'CONFIRM') {

foreach ($_POST as $key => $value) {

	if (substr ($key, 0, 7) == "hPlayer") {
		$pid = (int) $value;
				$delete = (int) $_POST ["chkPl{$value}"];
		if ($delete > 0) { $delete = 1; }
		else { $delete = 0; }

		//Run DELETE queries for this user
		if ($delete == 1)
		{
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}access_log WHERE alPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}bookings WHERE bkPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}characters WHERE chPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}guildmembers WHERE gmPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}ospstaken WHERE otPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}sessions WHERE ssPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			if (ba_db_query ($link, "DELETE FROM {$db_prefix}skillstaken WHERE stPlayerID = $pid"))
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");

			$sql = "DELETE FROM {$db_prefix}players WHERE plPlayerID = $pid";
			if (ba_db_query ($link, $sql) === False) {
				$sWarn = "There was a problem deleting the users";
				LogError ("There was a problem deleting inactive users.\nSQL:\n$sql\n");
			}
		}
	}
}
}

include ('../inc/inc_head_html.php');
?>
<script type="text/javascript">
// <!--
function selectAll (Selected) {
	var f = document.getElementById ("frmOldLogins");
	for (var i=0; i < f.length; i++)
		f.elements[i].checked = Selected;
}
// -->
</script>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Inactive Users</h1>

<?php
if ($sMsg != '')
	echo "<p>$sMsg</p>";
?>


<p>
<a href = 'admin.php'>Admin</a>
</p>
<p>
The following users have not logged in for at least <?php echo $iAge ?> months. You may wish to delete their details.</br>
Click on a column header to sort by that column.
</p>

<p>
<a href = "#" onclick = "selectAll (1)">Select All</a> <a href = "#" onclick = "selectAll (0)">Select None</a>
</p>
<form action = 'root_oldlogins.php' method = 'post' id = 'frmOldLogins'>
<table border = 1 class="sortable">
<tr><th>Select</th>
<th>Player ID</th>
<th>First Name</th>
<th>Surname</th>
<th>E-mail</th></tr>

<?php
$sql = "SELECT plPlayerID, plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plLastLogin < '$sDate' or plLastLogin IS NULL";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr>";
	echo "<td><input type = 'hidden' name = 'hPlayer" . $row ['plPlayerID'] . "' value = '" . $row ['plPlayerID'] . "'>";
	echo "<input type = 'checkbox' name = 'chkPl{$row ['plPlayerID']}' value = '{$row ['plPlayerID']}'></td>\n";
	echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</td>";
	echo "<td>{$row ['plFirstName']}</td>";
	echo "<td>{$row ['plSurname']}</td>";
	echo "<td>{$row ['plEmail']}</td>";
	echo "</tr>\n";
}
?>
</table>
<p>
<a href = "#" onclick = "selectAll (1)">Select All</a> <a href = "#" onclick = "selectAll (0)">Select None</a>
</p>

<p>
Change period to <input class = 'text' name = 'txtAge' style = 'width: 3ex' value = '<?php echo $iAge ?>'> months
<input type = 'submit' value = 'Update' name = 'btnUpdate'>
</p>
<p>
<input type = 'hidden' name = 'hAge' value = '<?php echo $iAge; ?>'>
To guard against mistakes, enter <b>confirm</b> (in capital letters) in the box below, then click "Delete these users"<br>
<input name = 'txtConfirm'>&nbsp;
<input type = 'submit' value = 'Delete these users' name = 'btnDelete'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');
?>
