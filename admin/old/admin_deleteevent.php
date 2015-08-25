<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/deleteevent.php
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
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

$sWarn = '';
if ($_POST ['btnDelete'] != '' && CheckReferrer ('admin_deleteevent.php')) {
	if ($_POST ['txtConfirm'] == 'CONFIRM') {
		//Get list of bookingIDs for this event
		$sql = "SELECT bkID from " . DB_PREFIX . "bookings where bkEventID = $eventid";
		$result = ba_db_query ($link, $sql);
		$usedidlist = "";
		while ($row = ba_db_fetch_assoc ($result))
		{
		array_push($itemselected, $row);
		$usedidlist .= $row['bkID'] .",";
		}
		$usedidlist .= "-1";
		//Remove bookingitems
		$sql = "DELETE FROM " . DB_PREFIX . "bookingitems where biBookingID in ($usedidlist)";
		ba_db_query ($link, $sql);

		//Remove payment requests
		$sql = "DELETE FROM " . DB_PREFIX . "paymentrequests where prBookingID in ($usedidlist)";
		ba_db_query ($link, $sql);

		//Remove bookings
		$sql = "DELETE FROM " . DB_PREFIX . "bookings where bkEventID = $eventid";
		ba_db_query ($link, $sql);

		//Remove items
		$sql = "DELETE FROM " . DB_PREFIX . "items where itEventID = $eventid";
		ba_db_query ($link, $sql);

		//Remove event
		$sql = "DELETE FROM " . DB_PREFIX . "events where evEventID = $eventid";
		ba_db_query ($link, $sql);



		//Remove all records from bookings table
		$sMsg = "Event ". htmlentities (stripslashes ($eventinfo['evEventName'])) ." has been deleted";
		$sURL = fnSystemURL () . 'admin.php?warn=' . urlencode ($sMsg);
		header ("Location: $sURL");
	}
	else
		$sWarn = "CONFIRM was not entered correctly in the text box. It must be all upper case.";
}
include ('../inc/inc_head_html.php');
?>

<script type="text/javascript">
<!--
function fnConfirm () {
	return confirm ("Are you sure you want to delete this event, including all bookings?")
}
// -->
</script>

<h1><?php echo TITLE?> - Remove All Bookings</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<?php
if ($sWarn != '') {
	echo "<span class = 'warn'>$sWarn</span>";
}
?>

<p>
This page can be used to delete this event. <b>This should only be done if you are sure you want to remove this event.</b> If it is done by mistake, the event will need to be re-added, and all bookings will need to be re-booked.
</p>

<form action = 'admin_deleteevent.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post' onsubmit = 'return fnConfirm ()'>
<p>
To guard against mistakes, enter <b>confirm</b> (in capital letters) in the box below, then click "Delete"<br>
<input name = 'txtConfirm'>&nbsp;
<input type = 'submit' value = 'Delete' name = 'btnDelete'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');