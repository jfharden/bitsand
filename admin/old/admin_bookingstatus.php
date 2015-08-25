<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_bookingstatus.php
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
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

	foreach ($_POST as $key => $value) {
		if (substr ($key, 0, 10) == "btnConfirm" && $value=='D' && CheckReferrer ('admin_bookingstatus.php')) {
			$iBookingID = (int) substr($key,10, strlen($key)-10);
			if ($_POST["txtConfirm".$iBookingID] == "CONFIRM") {
				deleteBooking($iBookingID);
			}
		}
	}

$sql = "SELECT plPlayerID, bkID, " .
	"plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"bkDateOOCConfirmed, " .
	"bkDateICConfirmed, " .
	"bkDatePaymentConfirmed, " .
	"UNIX_TIMESTAMP(bkDateOOCConfirmed) + UNIX_TIMESTAMP(bkDateICConfirmed) AS dDateSort " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE plPlayerID = bkPlayerID" .
	" AND bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Bookings Status</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>


<p>
Click on a column header to sort by that column.
</p>
<p>
To delete a booking, enter 'CONFIRM' in the box, and press the button.
</p>

<form method='POST' action='admin_bookingstatus.php?EventID=<?php echo $eventinfo['evEventID'];?>'>
<table border = '1' class="sortable">
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Details Confirmed?</th>
<th>Paid?</th>
<th>Booking As</th>
<th>View</th>
<th>Delete Booking</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n<td>";
	echo "<a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
	echo "</td><td>\n";
	echo htmlentities (stripslashes ($row ['chName']));
	echo "</td>\n";
	$sConfirm = '';
	if ($row ['bkDateOOCConfirmed'] != '0000-00-00' && $row ['bkDateOOCConfirmed'] != '')
		$sConfirm = "OOC";
	if ($row ['bkDateICConfirmed'] != '0000-00-00' && $row ['bkDateICConfirmed'] != '') {
		if ($sConfirm == "OOC")
			$sConfirm = "OOC &amp; ";
		$sConfirm .= "IC";
	}
	if ($sConfirm != '')
		$sConfirm .= " details confirmed";
	if ($sConfirm == 'OOC &amp; IC details confirmed')
		$sConfirm = "<span class = 'sans-green'>$sConfirm</span>";
	echo "<td>$sConfirm</td>\n";
	if ($row ['bkDatePaymentConfirmed'] != '0000-00-00' && $row ['bkDatePaymentConfirmed'] != '')
		echo "<td><span class = 'sans-green'>Paid</span></td>\n";
	else
		echo "<td><span class = 'sans-warn'>Not paid</span></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs']));
	echo "</td>";
	echo "<td><a href='admin_booking.php?BookingID=".$row['bkID']."'>View</a></td>";
	echo "<td><input type='text' name='txtConfirm".$row['bkID']."' style='width:70px;' /><input type='submit' name='btnConfirm".$row['bkID']."' value='D' /></td>";
	echo "\n</tr>";
}
?>
</table>
</form>

<?php
include ('../inc/inc_foot.php');