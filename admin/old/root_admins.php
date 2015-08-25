<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/root_admins.php
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
include ('../inc/inc_head_html.php');

$db_prefix = DB_PREFIX;

if ($_GET ['action'] != '' && CheckReferrer ('root_admins.php')) {
	if ($_GET ['action'] == 'revoke')
		$sAccess = '';
	elseif ($_GET ['action'] == 'add')
		$sAccess = 'admin';
	//Cast the player ID to an integer, since it should be an integer anyway
	$id = (int) $_GET ['id'];
	$sql = "UPDATE {$db_prefix}players SET plAccess = '$sAccess' WHERE plPlayerID = $id";
	//LogWarn ($sql);
	if (ba_db_query ($link, $sql) == False) {
		$sWarn = "Error making user (ID $id) an administrator";
		LogError ($sWarn);
	}
}
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Administrators</h1>

<p>
<a href = 'admin.php'>Admin</a>
</p>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>\n";
?>

<h3>Current Admins</h3>

<table class = 'sortable'>
<tr><th>PlayerID</th><th>First Name</th><th>Surname</th><th colspan = '2'>&nbsp;</th></tr>
<?php
$sql = "SELECT plPlayerID, plPassword, plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plAccess = 'admin'";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n";
	if ($row ['plPassword'] == 'ACCOUNT DISABLED')
		echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . " (account disabled)</td>\n";
	else
		echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
	$sEmail = htmlentities (stripslashes ($row ['plEmail']));
	echo "<td><a href = 'mailto:" . Obfuscate ($sEmail) . "'>E-mail</a></td>\n";
	echo "<td><a href = 'root_admins.php?action=revoke&amp;id={$row ['plPlayerID']}'>Revoke</td>\n";
	echo "</tr>\n";
}
?>
</table>

<p><hr></p>
<h3>Create Administrators</h3>

<form action = 'root_admins.php' method = 'post'>
To search for a user to make an admin, enter the first name and/or surname and click Search:<br>
<table border = '0'>
<tr><td>OOC first name:</td><td><input name = 'txtFirstName'></td></tr>
<tr><td>OOC surname:</td><td><input name = 'txtSurname'></td></tr>
<tr><td class = 'mid' colspan = '2'>
<input type = 'submit' name = 'btnSubmit' value = 'Search'>
<input type = 'reset' value = "Reset form">
</td></tr>
</table>
</form>

<?php
if ($_POST ['btnSubmit'] == 'Search' && CheckReferrer ('root_admins.php')) {
	$sFirstName = ba_db_real_escape_string ($link, $_POST ['txtFirstName']);
	$sSurname = ba_db_real_escape_string ($link, $_POST ['txtSurname']);
	$sOR = '';
	$sql = "SELECT plPassword, plPlayerID, plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plAccess <> 'admin' AND (";
	if ($sFirstName != '') {
		$sql .= " plFirstName LIKE '%$sFirstName%'";
		$sOR = ' OR';
	}
	if ($sSurname != '')
		$sql .= $sOR . " plSurname LIKE '%$sSurname%'";
	$sql .= ")";
	if ($sFirstName != '' || $sSurname != '')
		$result = ba_db_query ($link, $sql);

	echo "<h3>Search Results</h3>\n";
	if ($sFirstName == '' && $sSurname == '')
		echo "<i>No search term entered</i>";
	elseif (ba_db_num_rows ($result) == 0)
		echo "<i>No non-admin users found</i>";
	else {
		echo "<table class = 'sortable'>\n";
		echo "<tr><th>PlayerID</th><th>First Name</th><th>Surname</th><th colspan = '2'>&nbsp;</th></tr>\n";
		while ($row = ba_db_fetch_assoc ($result)) {
			echo "<tr class = 'highlight'>\n";
			if ($row ['plPassword'] == 'ACCOUNT DISABLED')
				echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . " (account disabled)</td>\n";
			else
				echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</td>\n";
			echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
			echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
			$sEmail = htmlentities (stripslashes ($row ['plEmail']));
			echo "<td><a href = 'mailto:" . Obfuscate ($sEmail) . "'>E-mail</a></td>\n";
			echo "<td><a href = 'root_admins.php?action=add&amp;id={$row ['plPlayerID']}'>Add</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}

include ('../inc/inc_foot.php');