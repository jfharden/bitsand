<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_booking_queue.php
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


$eventid = (int)htmlentities(stripslashes($_GET['EventID']));
if ($eventid > 0) { $eventinfo = getEventDetails($eventid, 0, 'admin.php'); }

$bid = (int) $_GET ['bid'];
$db_prefix = DB_PREFIX;

//remove player from queue
if ($bid > 0 && CheckReferrer ('admin_booking_queue.php')) {
	$sql = "UPDATE {$db_prefix}bookings SET bkInQueue = 0 WHERE bkID = " . $bid;
	ba_db_query ($link, $sql);
	//Send e-mail to tell them.
	$result = ba_db_query ($link, "SELECT plFirstName, plSurname, plEmail, plEmailRemovedFromQueue FROM {$db_prefix}players WHERE plPlayerID = $bid");
	$row = ba_db_fetch_assoc ($result);
	$email = $row ['plEmail'];
	//Set up e-mail body
	$sBody = "You have been removed from the booking queue at " . SYSTEM_NAME . ". " .
		"You can now finalise and pay for your booking.\n\n" .
		"Player ID: " . PID_PREFIX . sprintf ('%03s', $bid) . "\n" .
		"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . str_replace("admin/","",fnSystemURL ());
	//Send e-mail
	if ($row['plEmailRemovedFromQueue'])
		mail ($email, SYSTEM_NAME . ' - Ready to Finalise', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
}


//Get list of queued players
$sql = "SELECT bkID, plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"chName, " .
	"chFaction, ".
	"case when bkdateoocconfirmed > bkdateicconfirmed then bkdateoocconfirmed else bkdateicconfirmed end as bkDateConfirmed ".
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID AND bkInQueue = 1".
	" AND bkEventID = $eventid" .
	" ORDER BY bkDateConfirmed ASC";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Players In Booking Queue</h1>

<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<p>
This shows a list of players who have booked, and been placed into a queue. Players cannot finalise their bookings until you remove them from the queue.
</p>
<p>
Click on a column header to sort by that column. Click on a player's ID to see that player's details.
</p>

<table border = '1' class="sortable">
<tr>
<th>Remove from Queue</th>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>IC Faction</th>
<th>Date Confirmed</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n<td>";
	echo "<a href = 'admin_booking_queue.php?EventID=$eventid&bid=" . $row ['bkID'] . "'>Remove</a>";
	echo "</td>\n<td>";
	echo "<a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n<td>";
	echo htmlentities (stripslashes ($row ['plFirstName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['plSurname']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['chName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['chFaction']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['bkDateConfirmed']));
	echo "</td>\n</tr>";
}
?>

</table>

<?php
include ('../inc/inc_foot.php');
?>
