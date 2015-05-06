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
include ('../inc/inc_head_html.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

$key = CRYPT_KEY;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_bunks.php')) {
	$sql_clearbunks = "UPDATE {$db_prefix}bookings SET bkBunkAllocated = 0 where bkEventID = $eventid";
	ba_db_query ($link, $sql_clearbunks);
	foreach ($_POST as $key => $value) {
		if (substr ($key, 0, 8) == "hBooking") {
			$bunk = (int) $_POST ["chkPl{$value}"];
			if ($bunk > 0) { $bunk = 1; }
			else { $bunk = 0; }
	
	
			$iBookingID = (int) $value;
			if ($bunk) {
			 	$sql_update = "UPDATE {$db_prefix}bookings SET bkBunkAllocated = 1, bkBunkRequested = 1 WHERE bkID = " . $iBookingID;
				//echo $sql_update."<br />";
				ba_db_query ($link, $sql_update);
			}
			
			removeItem($iBookingID, 'bunk');
			if ($bunk > 0) { addItem($iBookingID, 'bunk'); }
		}
	}
}

//Get list of players that have paid and requested bunks
$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"bkBunkRequested, " .
	"bkBunkAllocated, " .
	"AES_DECRYPT(pleMedicalInfo, '$key') AS dMedicalInfo, " .
	"chName, " .
	"bkDateOOCConfirmed, " .
	"bkDateICConfirmed, " .
	"bkDatePaymentConfirmed, " .
	"bkID ".
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID AND " .
	"bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Bunks</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
The following people have paid. Click on a column header to sort by that column. To assign or remove bunks, tick or untick the relevant players boxes and click Submit.
</p>

<form action = 'admin_bunks.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post'>

<table border = '1' class="sortable">
<tr>
<th>Assign Bunk</th>
<th>Bunk Requested?</th>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Medical Info</th>
<th>Booking As</th>
<th>Date OOC Confirmed</th>
<th>Date IC Confirmed</th>
<th>Date Payment Confirmed</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>";
	echo "<input type = 'hidden' name = 'hBooking" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "'>\n";
	echo "<td class = 'mid'>";
	if ($row ['bkBunkAllocated'] == 1)
		$sChecked = ' checked';
	else
		$sChecked = '';
	echo "<input type = 'checkbox' name = 'chkPl{$row ['bkID']}' value = '{$row ['bkID']}'{$sChecked}></td>\n";
	if ($row ['bkBunkRequested'] == 0)
		echo "<td>No</td>\n";
	else
		echo "<td>Yes</td>\n";

	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['chName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['dMedicalInfo'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkDateOOCConfirmed'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkDateICConfirmed'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkDatePaymentConfirmed'])) . "</td>\n";
	echo "</tr>\n";
}
?>

</table>

<p>
<input type = 'submit' value = 'Submit' name = 'btnSubmit'>&nbsp;
<input type = 'reset' value = 'Reset'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');
?>
