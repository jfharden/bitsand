<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_booked.php
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
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

//Get list of players

$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"UPPER(plCarRegistration) AS uCarRegistration, " .
	"chName " .
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '0000-00-00' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Players Booked</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>


<p>
Click on a column header to sort by that column. Click on a player's ID to see that player's details.
</p>

<table border = '1' class="sortable">
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Car Registration</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n<td>";
	echo "<a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n<td>";
	echo htmlentities (stripslashes ($row ['plFirstName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['plSurname']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['chName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['uCarRegistration']));
	echo "</td>\n</tr>";
}
?>

</table>

<?php
include ('../inc/inc_foot.php');
?>
