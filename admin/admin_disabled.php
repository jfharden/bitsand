<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_disabled.php
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

$db_prefix = DB_PREFIX;

$sql = "SELECT plPlayerID, plFirstName, plSurname, plAdminNotes FROM {$db_prefix}players " .
	"WHERE plPassword = 'ACCOUNT DISABLED'";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Disabled Accounts</h1>

<p>
Listed below are all players whose account has been disabled. Resetting the player's password will also re-enable their account.<br>
Click on a column header to sort by that column.
</p>

<table class = 'sortable' border=1>
<tr><th>Player ID</th><th>OOC First Name</th><th>OOC Surname</th><th>Admin Note</th><th>Reset Password</th><th>View Details</th></tr>
<?php
//$bNone is True if no rows were displayed
$bNone = True;
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n";
	echo "<td>" . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plFirstName"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plSurname"])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ["plAdminNotes"])) . "</td>\n";
	echo "<td><a href = 'admin_pw_reset.php?pid=" . $row ['plPlayerID'] . "'>Reset password</a></td>\n";
	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>view OOC &amp; IC details</a></td>\n";
	echo "</tr>\n";
	$bNone = False;
}
if ($bNone)
	echo "<tr><td colspan = '6'><i>No disabled accounts found</i></td></tr>\n";
echo "</table>\n";

include ('../inc/inc_foot.php');
?>
