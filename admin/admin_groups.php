<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_groups.php
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

$db_prefix = DB_PREFIX;
$sGreen = '';
$sWarn = '';
$iGid = (int) $_GET ['id'];

if ($_GET ['action'] == 'delete' && CheckReferrer ('admin_groups.php')) {
	$sql = "DELETE FROM {$db_prefix}groups WHERE grID = $iGid";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error deleting group";
		LogError ($sWarn);
	}
	else
		$sGreen = "Group deleted";
}
elseif ($_POST ['btnEdit'] != '' && CheckReferrer ('admin_groups.php')) {
	$sql = "UPDATE {$db_prefix}groups " .
		"SET grName = '" . ba_db_real_escape_string ($link, $_POST ['txtName']) . "' " .
		"WHERE grID = " . (int) $_POST ['hID'];
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error updating group.";
		LogError ($sWarn);
	}
	else
		$sGreen = "Group updated";
}

if ($_POST ['btnSubmit'] == 'Add' && CheckReferrer ('admin_groups.php')) {
	$sql = "INSERT INTO {$db_prefix}groups (grName) " .
		"VALUES ('" . ba_db_real_escape_string ($link, stripslashes ($_POST ['txtAddName'])) . "')";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "There was an error adding the group.";
		$sAddName = $_POST ['txtAddName'];
	}
	else
		$sGreen = "The group was added successfully.";
}

if ($_POST ['btnSubmit'] == 'Assign To Selected' && CheckReferrer ('admin_groups.php')) {
	$sql = "UPDATE {$db_prefix}characters " .
		"SET chGroupText = '', chGroupSel = '" . ba_db_real_escape_string ($link, $_POST ['cboAssign']) . "'" .
		" WHERE chGroupText = '". ba_db_real_escape_string ($link, $_POST ['txtFreeValue']). "'";

	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "There was an error assigning the group to the linked characters.";
	}
}

include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Edit Groups</h1>

<?php
if ($sGreen != '')
	echo "<p class = 'green'>$sGreen</p>";
elseif ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>";
?>

<table border = '1'>
<?php
$groupnames = array();
$sql = "SELECT grID, grName FROM {$db_prefix}groups ORDER BY grName";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	$sName = htmlentities (stripslashes ($row ['grName']));

	echo "<tr><td><form action = 'admin_groups.php' method = 'post'>\n";
	echo "<input name = 'hID' value = '{$row ['grID']}' type = 'hidden'>";
	echo "<input name = 'txtName' value = \"" . htmlentities (stripslashes ($row ['grName'])) . "\"></td>\n";
	echo "<td><input type = 'submit' value = 'Save Changes' name = 'btnEdit'></td>";
	echo "<td><a href = 'admin_groups.php?action=delete&amp;id={$row ['grID']}'>Delete</a>\n";
	echo "</form></td></tr>";
	$groupnames[] = $sName;
}
?>
</table>

<h2><a name = 'add'>Add a New Group</a></h2>

<p>
<ul>
<li>To add a new group, enter the name and click Add.
<li>HTML is not allowed.
</ul>
</p>

<form action = 'admin_groups.php' method = 'post'>
<table>
<tr><td>Group Name:</td>
<td><input name = 'txtAddName' value = "<?php echo htmlentities ($sAddName) ?>"></td></tr>
<tr><td colspan = "2" class = "mid"><input type = 'submit' value = 'Add' name = 'btnSubmit'>
<input type = 'reset' value = "Reset form"></td></tr>
</table>
</form>

<h2><a name = 'freetext'>Show free text groups</a></h2>

<form action = 'admin_groups.php' method = 'post'>
<input type = 'submit' value = 'Show free text' name = 'btnSubmit'>
<input type = 'submit' value = 'Hide' name = 'btnSubmitHide'>
</form>

<?php
if (($_POST ['btnSubmit'] == 'Show free text' || $_POST ['btnSubmit'] == 'Assign To Selected') && CheckReferrer ('admin_groups.php')) {
	$sql = "select chCharacterID, chgrouptext, count(chgrouptext) as Occurs from {$db_prefix}characters where chgrouptext != '' and chgrouptext not like 'Enter name%' group by chgrouptext order by occurs desc, chgrouptext asc";
		echo "<table border = '1'>";
		echo "<tr><th>Group Name</th><th>Occurences</th></tr>";
		$result = ba_db_query ($link, $sql);
		while ($row = ba_db_fetch_assoc ($result)) {
			echo "<tr><td>". htmlentities (stripslashes ($row ['chgrouptext'])) ."</td><td>". htmlentities (stripslashes ($row ['Occurs'])) ."</td>";
			echo "<td><form action = 'admin_groups.php' method = 'post'><input type=hidden name='txtFreeValue' value='". htmlentities (stripslashes ($row ['chgrouptext'])) ."'><select name='cboAssign'>";
			foreach ($groupnames as $i => $value) {
			echo "<option>$value</option>";
			}
			echo "</select></td><td><input type = 'submit' value = 'Assign To Selected' name = 'btnSubmit'></td></form>";
			echo "</tr>";
		}
		echo "</table>";
	}
?>


<?php
include ('../inc/inc_foot.php');