<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_search.php
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
include ('../inc/inc_head_html.php');

if ($_GET ['btnSubmit'] != '') {
	$db_prefix = DB_PREFIX;
	$iID = (int) ba_db_real_escape_string ($link, str_replace (PID_PREFIX, '', $_GET ['txtID']));
	$sFirst = ba_db_real_escape_string ($link, $_GET ['txtFirstName']);
	$sSurname = ba_db_real_escape_string ($link, $_GET ['txtSurname']);
	$sMail = SafeEmail ($_GET ['txtEmail']);
	$sCar = ba_db_real_escape_string ($link, str_replace (' ', '', $_GET ['txtCarRegistration']));
	$sCharName = ba_db_real_escape_string ($link, $_GET ['txtCharName']);

	$sql = "SELECT plPlayerID, plFirstName, plSurname, plEmail, plCarRegistration, plPassword, chName " .
		"FROM {$db_prefix}players LEFT JOIN {$db_prefix}characters ON plPlayerID = chPlayerID ";

	//$sOR is used to add OR if required
	$sOR = '';
	$sCond = '';
	if ($iID != 0) {
		$sCond .= " plPlayerID = $iID";
		$sOR = ' OR';
	}
	if ($sFirst != '') {
		$sCond .= $sOR . " plFirstName LIKE '%$sFirst%'";
		$sOR = ' OR';
	}
	if ($sSurname != '') {
		$sCond .= $sOR . " plSurname LIKE '%$sSurname%'";
		$sOR = ' OR';
	}
	if ($sMail != '') {
		$sCond .= $sOR . " plEmail LIKE '%$sMail%'";
		$sOR = ' OR';
	}
	if ($sCar != '') {
		$sCond .= $sOR . " plCarRegistration LIKE '%$sCar%'";
		$sOR = ' OR';
	}
	// Last one does not need $sOR to be set
	if ($sCharName != '')
		$sCond .= $sOR . " chName LIKE '%$sCharName%'";

	$sCond .= ")";
	if (strlen($sCond) > 1) { $sql .= "WHERE (" . $sCond; }

	$result = ba_db_query ($link, $sql);
}
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Search</h1>

<p>
Enter search terms below. If any match, the player will be included in the results. Wildcards are not required.<br>
If all fields are left blank, all players will be returned.
</p>

<form action = "admin_search.php" method = "GET">
<table><tr>
<td>Player ID:</td>
<td><?php echo PID_PREFIX?><input name = "txtID" size = "5" value = "<?php echo $_GET ['txtID']?>"></td>
</tr><tr>
<td>OOC first name:</td>
<td><input name = "txtFirstName" value = "<?php echo $_GET ['txtFirstName']?>"></td>
</tr><tr>
<td>OOC surname:</td>
<td><input name = "txtSurname" value = "<?php echo $_GET ['txtSurname']?>"></td>
</tr><tr>
<td>E-mail address:</td>
<td><input name = "txtEmail" value = "<?php echo $_GET ['txtEmail']?>"></td>
</tr><tr>
<td>Car registration:</td>
<td><input name = "txtCarRegistration" value = "<?php echo $_GET ['txtCarRegistration']?>"></td>
</tr><tr>
<td>Character name:</td>
<td><input name = "txtCharName" value = "<?php echo $_GET ['txtCharName']?>"></td>
</tr><tr>
<td colspan = "2" align = "center"><input type = "submit" name = "btnSubmit" value = "Search">
<input type = "reset" value = "Reset form"></td>
</tr></table>
</form>

<h3>Search Results</h3>

<p>
Click on a column header to sort by that column. To enable a disabled account, simply reset the user's password.
</p>

<table class = 'sortable' border = 1>
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>E-mail</th>
<th>Car Registration</th>
<th>Character Name</th>
<th colspan = '4'>Actions</th>
</tr>
<?php
//$bNone is True if no rows were displayed
$bNone = True;
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'><td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']);
	if ($row ['plPassword'] == 'ACCOUNT DISABLED')
		echo " (account disabled)";
	echo "</td>";
	echo "<td>" . htmlentities (stripslashes ($row ["plFirstName"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plSurname"])) . "</td>\n";
	$sMail = htmlentities (stripslashes ($row ["plEmail"]));
	echo "<td><a href = 'mailto:$sMail'>$sMail</a></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plCarRegistration"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["chName"])) . "</td>\n";
	echo "<td><a href = 'admin_edit_ooc.php?pid=" . $row ['plPlayerID'] . "'>edit OOC data</a></td>\n";
	echo "<td><a href = 'admin_edit_ic.php?pid=" . $row ['plPlayerID'] . "'>edit IC data</a></td>\n";
	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>view OOC &amp; IC details</a></td>\n";
	echo "<td><a href = 'admin_pw_reset.php?pid=" . $row ['plPlayerID'] . "'>reset password</a></td></tr>\n";
	$bNone = False;
}
if ($bNone)
	echo "<tr><td colspan = '10'><i>No players found</i></td></tr>\n";
echo "</table>\n";

include ('../inc/inc_foot.php');