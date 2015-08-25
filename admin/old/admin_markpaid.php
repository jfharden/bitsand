<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_markpaid.php
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

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_markpaid.php')) {
	foreach ($_POST as $key => $value) {
		if (substr ($key, 0, 8) == "hBooking") {
			$iBookingID = $value;
			$paid = (int) $_POST ["chkPayPl{$value}"];
			$meal = (int) $_POST ["chkMealPl{$value}"];
			$gate = (int) $_POST ["chkGatePl{$value}"];
			$amountpaid = sanitiseAmount($_POST ["txtAmountPaid{$value}"]);
			$amountexpected = sanitiseAmount($_POST ["txtAmountExpected{$value}"]);

			//Mark player as paid
			if ($paid != 0) {
				//Set up UPDATE & SELECT queries
				$sql_update = "UPDATE {$db_prefix}bookings SET bkDatePaymentConfirmed = '" . date ('Y-m-d') . "', bkAmountPaid = $amountpaid, bkAmountExpected = $amountexpected WHERE bkID = " . $iBookingID;
				$sql_select = "SELECT plPlayerID, plFirstName, plSurname, plEmail, plEmailPaymentReceived ";
				$sql_select .= "FROM {$db_prefix}players INNER JOIN {$db_prefix}bookings on bkPlayerID = plPlayerID WHERE bkID = " . $iBookingID;
				//Run UPDATE query to set paid date
				ba_db_query ($link, $sql_update);
				//Run SELECT query and send e-mail
				$result = ba_db_query ($link, $sql_select);
				$row = ba_db_fetch_assoc ($result);
				$sBody = "Your payment for the upcoming event has been received and you have been marked as paid.\n";
				$sBody .= "You are now fully booked.\n\nThank you.\n\n";
				$sBody .= "Player ID: " . PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "\n";
				$sBody .= "OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'];
				if ($row['plEmailPaymentReceived'])
					mail ($row ['plEmail'], SYSTEM_NAME . ' - marked paid', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");

				//Remove any meal ticket items from this booking
				removeItem($iBookingID, 'meal');

				//Update meal ticket field
				if ($meal != 0) {
					//Set up UPDATE query
					$sql = "UPDATE {$db_prefix}bookings SET bkMealTicket = 1 WHERE bkID = " . $iBookingID;
					//Run UPDATE query to set meal ticket
					ba_db_query ($link, $sql);
   					addItem($iBookingID, 'meal');
				}
			}

			//Update pay on gate field
			if ($gate != 0)
				$sql = "UPDATE {$db_prefix}bookings SET bkDatePaymentConfirmed = '" . date ('Y-m-d') . "', bkPayOnGate = 1, bkAmountPaid = $amountpaid, bkAmountExpected = $amountexpected " .
					"WHERE bkID = " . $iBookingID;
			else
				$sql = "UPDATE {$db_prefix}bookings SET bkPayOnGate = 0 WHERE bkID = " . $iBookingID;
			//Run UPDATE query to set paid date
			ba_db_query ($link, $sql);
		}
	}
}


$playermeal = getItemCost('meal', 'player', $eventid);
$monstermeal = getItemCost('meal', 'monster', $eventid);
$staffmeal = getItemCost('meal', 'staff', $eventid);

$playerticket = getItemCost('ticket', 'player', $eventid);
$monsterticket = getItemCost('ticket', 'monster', $eventid);
$staffticket = getItemCost('ticket', 'staff', $eventid);

echo '<script type="text/javascript">';
echo "
	function changeAmounts(id, bookingtype)
	{
		var playermeal = $playermeal;
		var monstermeal = $monstermeal;
		var staffmeal = $staffmeal;
		var playerticket = $playerticket
		var monsteticket = $monsterticket;
		var staffticket = $staffticket;

		var paid = document.forms[0].elements['chkPayPl' + id].checked;
		var meal = document.forms[0].elements['chkMealPl' + id].checked;
		var gate = document.forms[0].elements['chkGatePl' + id].checked;
		var amount1 = 0;
		var amount2 = 0;

		if (bookingtype == 'Staff')
		{
			amount1 = staffticket;
			amount2 = amount1 + staffmeal;
		}
		else if (bookingtype == 'Monster')
		{
			amount1 = monsterticket;
			amount2 = amount1 + monstermeal;
		}
		else
		{
			amount1 = playerticket;
			amount2 = amount1 + playermeal;
		}

		amount1 = amount1.toFixed(2);
		amount2 = amount2.toFixed(2);

		if (meal)
		{
			document.forms[0].elements['txtAmountPaid' + id].value = amount2;
		}
		else
		{
			document.forms[0].elements['txtAmountPaid' + id].value = amount1;
		}

		if (gate || !paid)
		{
			document.forms[0].elements['txtAmountPaid' + id].value = '0.00';
		}

	}
";


echo "</script>";



//Get list of players booked but not marked as paid
$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"bkDateOOCConfirmed, " .
	"bkDateICConfirmed, " .
	"bkDatePaymentConfirmed, " .
	"bkAmountPaid, " .
	"bkAmountExpected, " .
	"bkID, " .
	"bkMealTicket " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE plPlayerID = bkPlayerID AND " .
	"bkDateOOCConfirmed <> '0000-00-00' AND bkDateICConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed = '0000-00-00'" .
	" AND bkEventID = $eventid" .
	" ORDER BY plPlayerID";

$result = ba_db_query ($link, $sql);

?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Payments Received</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
The following people have booked, but are not marked as paid. Click on a column header to sort by that column.
</p>

<form action = 'admin_markpaid.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post'>

<table border = '1' class="sortable">
<tr>
<th>Paid?</th>
<th>Meal Ticket?</th>
<th>Pay on Gate</th>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Booking As</th>
<th>Amount Paid</th>
<th>Amount Expected</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>";
	echo "<td class = 'mid'><input type = 'hidden' name = 'hBooking" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "'>";
	echo "<input type = 'checkbox' name = 'chkPayPl" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."')\"></td>";
	echo "<td class = 'mid'><input type = 'checkbox' name = 'chkMealPl" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."')\""; if ($row['bkMealTicket']) { echo "checked ";} echo "></td>";
	echo "<td class = 'mid'><input type = 'checkbox' name = 'chkGatePl" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."')\"></td>";
	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>";
	echo "<td>" . htmlentities (stripslashes ($row ['chName'])) . "</td>";
	echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs'])) . "</td>";

	echo "<td><input type = 'textbox' size = 5 name='txtAmountPaid" . $row ['bkID'] . "' value='" . htmlentities (stripslashes ($row ['bkAmountPaid'])) . "' /></td>";
	echo "<td><input type = 'textbox' size = 5 name='txtAmountExpected" . $row ['bkID'] . "' value='" . htmlentities (stripslashes ($row ['bkAmountExpected'])) . "' /></td>";
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